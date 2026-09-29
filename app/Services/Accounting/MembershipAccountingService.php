<?php

namespace App\Services\Accounting;

use App\Models\Accounting\MembershipAccountingAudit;
use App\Models\Accounting\MembershipInvoice;
use App\Models\Accounting\MembershipPayment;
use App\Models\Accounting\MembershipReceipt;
use App\Models\MemberMembership;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MembershipAccountingService
{
    public function __construct(private readonly DocumentNumberService $numbers) {}

    /**
     * Create a draft invoice. Money values are rounded to two decimal places and
     * totals are calculated server-side; callers cannot supply authoritative totals.
     */
    public function createInvoice(MemberMembership $membership, array $items, array $attributes = []): MembershipInvoice
    {
        if ($items === []) {
            throw new \InvalidArgumentException('An invoice requires at least one line item.');
        }

        return DB::transaction(function () use ($membership, $items, $attributes) {
            $invoice = MembershipInvoice::create([
                'membership_id' => $membership->getKey(),
                'user_id' => $membership->user_id,
                'invoice_date' => Arr::get($attributes, 'invoice_date', now()->toDateString()),
                'due_date' => Arr::get($attributes, 'due_date'),
                'period_start' => Arr::get($attributes, 'period_start'),
                'period_end' => Arr::get($attributes, 'period_end'),
                'currency' => strtoupper(Arr::get($attributes, 'currency', 'INR')),
                'notes' => Arr::get($attributes, 'notes'),
                'created_by' => Arr::get($attributes, 'actor_id'),
                'status' => 'draft',
            ]);

            $subtotal = $discount = $tax = $total = 0;
            foreach ($items as $item) {
                $quantity = $this->money(Arr::get($item, 'quantity', 1));
                $unitPrice = $this->money(Arr::get($item, 'unit_price'));
                $lineDiscount = $this->money(Arr::get($item, 'discount_amount', 0));
                $taxRate = round((float) Arr::get($item, 'tax_rate', 0), 4);
                if ($quantity <= 0 || $unitPrice < 0 || $lineDiscount < 0 || $taxRate < 0) {
                    throw new \InvalidArgumentException('Invoice quantities, prices, discounts and tax rates must be valid positive values.');
                }

                $gross = $this->money($quantity * $unitPrice);
                if ($lineDiscount > $gross) {
                    throw new \InvalidArgumentException('A line discount cannot exceed its gross amount.');
                }
                $taxable = $this->money($gross - $lineDiscount);
                $lineTax = $this->money($taxable * $taxRate / 100);
                $lineTotal = $this->money($taxable + $lineTax);
                $invoice->items()->create([
                    'description' => trim((string) Arr::get($item, 'description')),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $lineDiscount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                    'metadata' => Arr::get($item, 'metadata'),
                ]);
                $subtotal += $gross;
                $discount += $lineDiscount;
                $tax += $lineTax;
                $total += $lineTotal;
            }

            $invoice->update([
                'subtotal' => $this->money($subtotal),
                'discount_amount' => $this->money($discount),
                'tax_amount' => $this->money($tax),
                'total_amount' => $this->money($total),
            ]);
            $this->audit('invoice.created', $invoice, ['status' => 'draft'], Arr::get($attributes, 'actor_id'));

            return $invoice->fresh('items');
        });
    }

    public function postInvoice(MembershipInvoice $invoice, ?int $actorId = null): MembershipInvoice
    {
        return DB::transaction(function () use ($invoice, $actorId) {
            $invoice = MembershipInvoice::query()->lockForUpdate()->findOrFail($invoice->getKey());
            if ($invoice->status !== 'draft') {
                throw new \DomainException('Only draft invoices can be posted.');
            }
            if ((float) $invoice->total_amount <= 0 || ! $invoice->items()->exists()) {
                throw new \DomainException('A posted invoice must contain a positive amount.');
            }

            $date = Carbon::parse($invoice->invoice_date);
            $invoice->update([
                'invoice_number' => $this->numbers->next('invoice', $date),
                'status' => 'issued',
                'issued_at' => now(),
                'issued_by' => $actorId,
            ]);
            $this->audit('invoice.posted', $invoice, ['invoice_number' => $invoice->invoice_number], $actorId);

            return $invoice->fresh('items');
        });
    }

    /**
     * Record a posted payment and optionally allocate it to issued invoices.
     * Unallocated money remains an advance on the member account.
     *
     * @param  array<int|string, float|int|string>  $allocations  invoice_id => amount
     */
    public function recordPayment(MemberMembership $membership, float|int|string $amount, array $allocations, array $attributes): MembershipPayment
    {
        $amount = $this->money($amount);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }
        if (! Arr::get($attributes, 'method')) {
            throw new \InvalidArgumentException('Payment method is required.');
        }

        return DB::transaction(function () use ($membership, $amount, $allocations, $attributes) {
            $paymentDate = Carbon::parse(Arr::get($attributes, 'payment_date', now()->toDateString()));
            $allocatedTotal = 0;
            $normalised = [];
            foreach ($allocations as $invoiceId => $allocatedAmount) {
                $allocatedAmount = $this->money($allocatedAmount);
                if ($allocatedAmount <= 0) {
                    throw new \InvalidArgumentException('Allocation amounts must be greater than zero.');
                }
                $normalised[(int) $invoiceId] = $allocatedAmount;
                $allocatedTotal += $allocatedAmount;
            }
            if ($this->money($allocatedTotal) > $amount) {
                throw new \DomainException('Allocated amount cannot exceed the payment amount.');
            }

            $payment = MembershipPayment::create([
                'membership_id' => $membership->getKey(), 'user_id' => $membership->user_id,
                'payment_number' => $this->numbers->next('payment', $paymentDate),
                'payment_date' => $paymentDate, 'amount' => $amount,
                'currency' => strtoupper(Arr::get($attributes, 'currency', 'INR')),
                'method' => Arr::get($attributes, 'method'), 'reference' => Arr::get($attributes, 'reference'),
                'deposit_account' => Arr::get($attributes, 'deposit_account'),
                'notes' => Arr::get($attributes, 'notes'), 'metadata' => Arr::get($attributes, 'metadata'),
                'status' => 'posted', 'posted_at' => now(), 'posted_by' => Arr::get($attributes, 'actor_id'),
            ]);

            foreach ($normalised as $invoiceId => $allocatedAmount) {
                $invoice = MembershipInvoice::query()->lockForUpdate()->findOrFail($invoiceId);
                if ((int) $invoice->membership_id !== (int) $membership->getKey()) {
                    throw new \DomainException('A payment cannot be allocated to another member.');
                }
                if (! in_array($invoice->status, ['issued', 'partially_paid'], true)) {
                    throw new \DomainException('Payments can only be allocated to an open posted invoice.');
                }
                $balance = $this->money((float) $invoice->total_amount - (float) $invoice->paid_amount);
                if ($allocatedAmount > $balance) {
                    throw new \DomainException('An allocation cannot exceed the invoice balance.');
                }
                $newPaid = $this->money((float) $invoice->paid_amount + $allocatedAmount);
                $invoice->update(['paid_amount' => $newPaid, 'status' => $newPaid >= (float) $invoice->total_amount ? 'paid' : 'partially_paid']);
                $payment->allocations()->create(['invoice_id' => $invoice->id, 'amount' => $allocatedAmount]);
            }

            $receipt = MembershipReceipt::create([
                'payment_id' => $payment->id,
                'receipt_number' => $this->numbers->next('receipt', $paymentDate),
                'receipt_date' => $paymentDate,
                'amount' => $amount,
                'issued_at' => now(),
                'issued_by' => Arr::get($attributes, 'actor_id'),
            ]);
            $this->audit('payment.posted', $payment, ['receipt_number' => $receipt->receipt_number, 'allocated_amount' => $this->money($allocatedTotal)], Arr::get($attributes, 'actor_id'));

            return $payment->fresh(['allocations.invoice', 'receipt']);
        });
    }

    private function money(float|int|string|null $value): float
    {
        if (! is_numeric($value)) {
            throw new \InvalidArgumentException('Money values must be numeric.');
        }

        return round((float) $value, 2);
    }

    private function audit(string $event, $model, array $payload, ?int $actorId): void
    {
        MembershipAccountingAudit::create([
            'event' => $event, 'auditable_type' => $model::class, 'auditable_id' => $model->getKey(),
            'payload' => $payload, 'actor_id' => $actorId,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(), 'created_at' => now(),
        ]);
    }
}
