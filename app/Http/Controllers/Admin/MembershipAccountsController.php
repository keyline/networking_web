<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accounting\MembershipInvoice;
use App\Models\Accounting\MembershipPayment;
use App\Models\Accounting\MembershipReceipt;
use App\Models\MemberMembership;
use App\Models\User\UserMaster;
use App\Services\Accounting\MembershipAccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipAccountsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();
        $openStatuses = ['issued', 'partially_paid'];

        $data['filters'] = ['from' => $from->toDateString(), 'to' => $to->toDateString()];
        $data['stats'] = [
            'invoiced' => MembershipInvoice::whereBetween('invoice_date', [$from, $to])->where('status', '!=', 'draft')->sum('total_amount'),
            'collected' => MembershipPayment::whereBetween('payment_date', [$from, $to])->where('status', 'posted')->sum('amount'),
            'receivable' => MembershipInvoice::whereIn('status', $openStatuses)->selectRaw('COALESCE(SUM(total_amount - paid_amount),0) total')->value('total'),
            'overdue' => MembershipInvoice::whereIn('status', $openStatuses)->whereDate('due_date', '<', today())->selectRaw('COALESCE(SUM(total_amount - paid_amount),0) total')->value('total'),
            'advances' => max(0, MembershipPayment::where('status', 'posted')->sum('amount') - \DB::table('membership_payment_allocations')->sum('amount')),
            'due_30' => MembershipInvoice::whereIn('status', $openStatuses)->whereBetween('due_date', [today(), today()->addDays(30)])->count(),
        ];
        $data['recentPayments'] = MembershipPayment::with(['user.userDetail', 'receipt'])->latest('payment_date')->latest('id')->limit(8)->get();
        $data['overdueInvoices'] = MembershipInvoice::with(['user.userDetail'])->whereIn('status', $openStatuses)->whereDate('due_date', '<', today())->orderBy('due_date')->limit(8)->get();
        $data['methodTotals'] = MembershipPayment::whereBetween('payment_date', [$from, $to])->where('status', 'posted')->groupBy('method')->selectRaw('method, SUM(amount) total')->pluck('total', 'method');

        echo $this->admin_after_login_layout('Membership Accounts', 'membership.accounts.index', $data);
    }

    public function member(UserMaster $user)
    {
        $membership = MemberMembership::with('plan')->where('user_id', $user->um_id)->first();
        abort_unless($membership, 404, 'Set up the membership before opening its account.');
        $user->load(['userDetail', 'userType']);
        $invoices = MembershipInvoice::with('items')->where('membership_id', $membership->id)->latest('invoice_date')->latest('id')->get();
        $payments = MembershipPayment::with(['receipt', 'allocations.invoice'])->where('membership_id', $membership->id)->latest('payment_date')->latest('id')->get();

        $entries = collect();
        foreach ($invoices->where('status', '!=', 'draft') as $invoice) {
            $entries->push(['date' => $invoice->invoice_date, 'document' => $invoice->invoice_number, 'description' => 'Membership invoice', 'debit' => (float) $invoice->total_amount, 'credit' => 0, 'sort' => $invoice->id * 2]);
        }
        foreach ($payments->where('status', 'posted') as $payment) {
            $entries->push(['date' => $payment->payment_date, 'document' => $payment->receipt?->receipt_number ?: $payment->payment_number, 'description' => 'Payment · '.ucwords(str_replace('_', ' ', $payment->method)), 'debit' => 0, 'credit' => (float) $payment->amount, 'sort' => $payment->id * 2 + 1]);
        }
        $balance = 0;
        $ledger = $entries->sortBy(fn ($entry) => $entry['date']->format('Y-m-d').str_pad($entry['sort'], 12, '0', STR_PAD_LEFT))->values()->map(function ($entry) use (&$balance) { $balance += $entry['debit'] - $entry['credit']; $entry['balance'] = $balance; return $entry; });

        $summary = ['billed' => $invoices->where('status', '!=', 'draft')->sum('total_amount'), 'received' => $payments->where('status', 'posted')->sum('amount')];
        $summary['balance'] = $summary['billed'] - $summary['received'];
        $summary['advance'] = max(0, -$summary['balance']);
        $summary['outstanding'] = max(0, $summary['balance']);

        echo $this->admin_after_login_layout('Member Account', 'membership.accounts.member', compact('user', 'membership', 'invoices', 'payments', 'ledger', 'summary'));
    }

    public function invoice(Request $request, UserMaster $user, MembershipAccountingService $accounting)
    {
        $membership = MemberMembership::with('plan')->where('user_id', $user->um_id)->firstOrFail();
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'invoice_date' => ['required', 'date'], 'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
                'period_start' => ['required', 'date'], 'period_end' => ['required', 'date', 'after_or_equal:period_start'],
                'description' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'gt:0'],
                'discount_amount' => ['nullable', 'numeric', 'min:0'], 'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'], 'notes' => ['nullable', 'string', 'max:2000'],
            ]);
            try {
                $invoice = $accounting->createInvoice($membership, [[
                    'description' => $validated['description'], 'quantity' => 1, 'unit_price' => $validated['amount'],
                    'discount_amount' => $validated['discount_amount'] ?? 0, 'tax_rate' => $validated['tax_rate'] ?? 0,
                ]], array_merge($validated, ['actor_id' => auth('admin')->id()]));
                $accounting->postInvoice($invoice, auth('admin')->id());
                return redirect()->route('admin.membership-accounts.member', $user)->with('success_message', 'Invoice issued successfully.');
            } catch (\Throwable $exception) {
                return back()->withInput()->withErrors(['accounting' => $exception->getMessage()]);
            }
        }
        $user->load('userDetail');
        echo $this->admin_after_login_layout('Create Invoice', 'membership.accounts.invoice', compact('user', 'membership'));
    }

    public function payment(Request $request, UserMaster $user, MembershipAccountingService $accounting)
    {
        $membership = MemberMembership::with('plan')->where('user_id', $user->um_id)->firstOrFail();
        $openInvoices = MembershipInvoice::where('membership_id', $membership->id)->whereIn('status', ['issued', 'partially_paid'])->orderBy('due_date')->get();
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'payment_date' => ['required', 'date'], 'amount' => ['required', 'numeric', 'gt:0'],
                'method' => ['required', Rule::in(['cash', 'upi', 'bank_transfer', 'card', 'cheque', 'other'])],
                'reference' => ['nullable', 'string', 'max:150'], 'deposit_account' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:2000'], 'allocations' => ['nullable', 'array'], 'allocations.*' => ['nullable', 'numeric', 'min:0'],
            ]);
            if (in_array($validated['method'], ['upi', 'bank_transfer', 'card', 'cheque'], true) && blank($validated['reference'] ?? null)) return back()->withInput()->withErrors(['reference' => 'A transaction or cheque reference is required for this payment method.']);
            if (!blank($validated['reference'] ?? null) && MembershipPayment::where('method', $validated['method'])->where('reference', $validated['reference'])->where('status', 'posted')->exists()) return back()->withInput()->withErrors(['reference' => 'This payment reference has already been posted.']);
            $allocations = collect($validated['allocations'] ?? [])->filter(fn ($amount) => (float)$amount > 0)->all();
            try {
                $payment = $accounting->recordPayment($membership, $validated['amount'], $allocations, array_merge($validated, ['actor_id' => auth('admin')->id()]));
                return redirect()->route('admin.membership-accounts.receipt', $payment->receipt)->with('success_message', 'Payment posted and receipt generated.');
            } catch (\Throwable $exception) {
                return back()->withInput()->withErrors(['accounting' => $exception->getMessage()]);
            }
        }
        $user->load('userDetail');
        echo $this->admin_after_login_layout('Record Payment', 'membership.accounts.payment', compact('user', 'membership', 'openInvoices'));
    }

    public function receipt(MembershipReceipt $receipt)
    {
        $receipt->load(['payment.user.userDetail', 'payment.allocations.invoice']);
        return view('admin.maincontents.membership.accounts.receipt', compact('receipt'));
    }
}
