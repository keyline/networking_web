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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments');

            //$table->foreignIdFor(Payment::class)->constrained();
            $table->unsignedBigInteger('service_detail_id');
            $table->foreign('service_detail_id')->references('id')->on('service_details');

            //$table->foreignIdFor(ServiceDetail::class)->constrained();
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings');
            //$table->foreignIdFor(Booking::class)->constrained();
            $table->float('service_price', 10, 2);
            $table->float('sgst_amount', 10, 2);
            $table->float('cgst_amount', 10, 2);
            $table->float('igst_amount', 10, 2);
            $table->float('total_amount', 10, 2);
            $table->float('platform_charges', 10, 2);
            $table->float('mentor_charges', 10, 2);
            $table->morphs('invoicable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
