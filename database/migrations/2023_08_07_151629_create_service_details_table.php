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
        Schema::create('service_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_attribute_id');
            $table->foreign('service_attribute_id')->references('id')->on('service_attributes');

            $table->unsignedBigInteger('mentor_user_id');
            $table->foreign('mentor_user_id')->references('id')->on('users');

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('duration');
            $table->float('sgst_amount', 10, 2);
            $table->float('cgst_amount', 10, 2);
            $table->float('igst_amount', 10, 2);
            $table->float('total_amount_payable', 10, 2);
            $table->float('platform_charges', 10, 2);
            $table->float('mentor_payout_amount', 10, 2);
            $table->string('promised_response_time');
            $table->string('sort_order');
            $table->string('countryid');
            $table->string('service_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_details');
    }
};
