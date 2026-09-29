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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings');
            //$table->foreignIdFor(Booking::class)->constrained();
            $table->float('amount');
            $table->string('transaction_id')->unique();
            $table->text('gateway');
            $table->text('body');
            $table->string('destination');
            $table->text('hash');
            $table->text('response')->nullable();
            $table->enum('status', ['pending', 'failed', 'successful', 'invalid'])->default('pending')->index();
            $table->timestamp('verified_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
