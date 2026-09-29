<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 20);
            $table->string('financial_year', 9);
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['document_type', 'financial_year'], 'accounting_sequence_unique');
        });

        Schema::create('membership_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->string('invoice_number', 40)->nullable()->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->char('currency', 3)->default('INR');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });

        Schema::create('membership_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_rate', 7, 4)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign('invoice_id')->references('id')->on('membership_invoices')->restrictOnDelete();
        });

        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->string('payment_number', 40)->unique();
            $table->date('payment_date');
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('INR');
            $table->string('method', 30);
            $table->string('reference', 150)->nullable();
            $table->string('deposit_account', 100)->nullable();
            $table->string('status', 20)->default('posted');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('posted_at');
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamps();
            $table->index(['payment_date', 'method']);
            $table->index(['reference', 'method']);
        });

        Schema::create('membership_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 14, 2);
            $table->timestamps();
            $table->foreign('payment_id')->references('id')->on('membership_payments')->restrictOnDelete();
            $table->foreign('invoice_id')->references('id')->on('membership_invoices')->restrictOnDelete();
            $table->unique(['payment_id', 'invoice_id'], 'membership_payment_invoice_unique');
        });

        Schema::create('membership_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id')->unique();
            $table->string('receipt_number', 40)->unique();
            $table->date('receipt_date');
            $table->decimal('amount', 14, 2);
            $table->timestamp('issued_at');
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamps();
            $table->foreign('payment_id')->references('id')->on('membership_payments')->restrictOnDelete();
        });

        Schema::create('membership_accounting_audits', function (Blueprint $table) {
            $table->id();
            $table->string('event', 50);
            $table->string('auditable_type', 100);
            $table->unsignedBigInteger('auditable_id');
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id'], 'membership_accounting_auditable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_accounting_audits');
        Schema::dropIfExists('membership_receipts');
        Schema::dropIfExists('membership_payment_allocations');
        Schema::dropIfExists('membership_payments');
        Schema::dropIfExists('membership_invoice_items');
        Schema::dropIfExists('membership_invoices');
        Schema::dropIfExists('accounting_document_sequences');
    }
};
