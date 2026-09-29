<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->unique();
            $table->date('registration_date');
            $table->date('renewal_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('payment_amount', 12, 2)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 150)->nullable();
            $table->string('membership_status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['membership_status', 'renewal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_memberships');
    }
};
