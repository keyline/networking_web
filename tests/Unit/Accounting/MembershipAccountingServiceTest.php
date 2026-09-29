<?php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\MembershipInvoice;
use App\Models\Accounting\MembershipPayment;
use App\Models\MemberMembership;
use App\Services\Accounting\DocumentNumberService;
use App\Services\Accounting\MembershipAccountingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MembershipAccountingServiceTest extends TestCase
{
    private MembershipAccountingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('member_memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->unique();
            $table->date('registration_date');
            $table->timestamps();
        });
        $migration = require database_path('migrations/2026_09_29_100000_create_membership_accounting_tables.php');
        $migration->up();
        $this->service = app(MembershipAccountingService::class);
    }

    public function test_it_posts_an_invoice_and_allocates_a_payment_with_receipt(): void
    {
        $membership = MemberMembership::create(['user_id' => 42, 'registration_date' => '2026-04-01']);
        $invoice = $this->service->createInvoice($membership, [
            ['description' => 'Annual membership', 'quantity' => 1, 'unit_price' => 1000, 'discount_amount' => 100, 'tax_rate' => 18],
        ], ['invoice_date' => '2026-09-29', 'due_date' => '2026-10-15', 'actor_id' => 7]);

        $invoice = $this->service->postInvoice($invoice, 7);
        $this->assertSame('INV/2026-27/000001', $invoice->invoice_number);
        $this->assertSame('1062.00', $invoice->total_amount);

        $payment = $this->service->recordPayment($membership, 600, [$invoice->id => 600], [
            'payment_date' => '2026-09-29', 'method' => 'upi', 'reference' => 'UTR-100', 'actor_id' => 7,
        ]);

        $this->assertSame('PAY/2026-27/000001', $payment->payment_number);
        $this->assertSame('RCP/2026-27/000001', $payment->receipt->receipt_number);
        $this->assertSame('600.00', $payment->allocations->first()->amount);
        $this->assertSame('partially_paid', $invoice->fresh()->status);
        $this->assertSame('462.00', $invoice->fresh()->balance_due);
        $this->assertDatabaseCount('membership_accounting_audits', 3);

        $this->expectException(\LogicException::class);
        $payment->update(['amount' => 1]);
    }

    public function test_invalid_allocation_rolls_back_the_entire_payment(): void
    {
        $membership = MemberMembership::create(['user_id' => 43, 'registration_date' => '2026-04-01']);
        $invoice = $this->service->postInvoice($this->service->createInvoice($membership, [
            ['description' => 'Monthly membership', 'unit_price' => 100],
        ], ['invoice_date' => '2026-09-29']));

        try {
            $this->service->recordPayment($membership, 150, [$invoice->id => 150], ['method' => 'cash']);
            $this->fail('Expected over-allocation to be rejected.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString('invoice balance', $exception->getMessage());
        }

        $this->assertSame(0, MembershipPayment::count());
        $this->assertSame('0.00', MembershipInvoice::find($invoice->id)->paid_amount);
    }

    public function test_document_numbers_reset_by_indian_financial_year(): void
    {
        $numbers = app(DocumentNumberService::class);
        $this->assertSame('2025-26', $numbers->financialYear(now()->setDate(2026, 3, 31)));
        $this->assertSame('2026-27', $numbers->financialYear(now()->setDate(2026, 4, 1)));
    }
}
