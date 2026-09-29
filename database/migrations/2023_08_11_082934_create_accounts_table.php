<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['CREDIT', 'DEBIT']);
            $table->morphs('paid_for');
            $table->float('base_price', 10, 2);
            $table->float('sgst_amount', 10, 2);
            $table->float('cgst_amount', 10, 2);
            $table->float('igst_amount', 10, 2);
            $table->float('amount_payable', 10, 2);
            $table->float('platform_charges', 10, 2);
            $table->float('payout_amount', 10, 2);
            $table->float('opening_bal', 10, 2);
            $table->float('transaction_amt', 10, 2);
            $table->float('closing_bal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
