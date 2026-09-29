<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique();
            $table->text('summary')->nullable(); $table->longText('description')->nullable();
            $table->string('venue_name')->nullable(); $table->text('venue_address')->nullable();
            $table->string('timezone', 50)->default('Asia/Kolkata');
            $table->dateTime('starts_at'); $table->dateTime('ends_at');
            $table->dateTime('registration_opens_at')->nullable(); $table->dateTime('registration_closes_at')->nullable();
            $table->unsignedInteger('capacity')->nullable(); $table->unsignedSmallInteger('max_seats_per_order')->default(10);
            $table->char('currency', 3)->default('INR'); $table->string('status', 20)->default('draft');
            $table->boolean('allow_guest_registration')->default(true); $table->boolean('collect_attendee_details')->default(true);
            $table->string('cover_image')->nullable(); $table->unsignedBigInteger('created_by')->nullable(); $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps(); $table->index(['status', 'starts_at']);
        });
        Schema::create('event_ticket_types', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100); $table->text('description')->nullable(); $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('capacity')->nullable(); $table->unsignedSmallInteger('minimum_per_order')->default(1); $table->unsignedSmallInteger('maximum_per_order')->default(10);
            $table->dateTime('sales_start_at')->nullable(); $table->dateTime('sales_end_at')->nullable(); $table->boolean('is_active')->default(true); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('event_addons', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120); $table->string('category', 30)->default('food'); $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0); $table->string('pricing_type', 20)->default('per_seat');
            $table->unsignedInteger('capacity')->nullable(); $table->boolean('is_active')->default(true); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('event_form_fields', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('field_key', 80); $table->string('label', 120); $table->string('field_type', 30)->default('text');
            $table->json('options')->nullable(); $table->boolean('is_required')->default(false); $table->string('applies_to', 20)->default('order');
            $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps(); $table->unique(['event_id', 'field_key']);
        });
        Schema::create('event_orders', function (Blueprint $table) {
            $table->id(); $table->string('order_number', 40)->unique(); $table->string('confirmation_token', 64)->unique(); $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('user_id')->nullable()->index(); $table->string('customer_name'); $table->string('customer_email'); $table->string('customer_phone', 30)->nullable();
            $table->unsignedInteger('seat_count'); $table->decimal('subtotal', 12, 2); $table->decimal('discount_amount', 12, 2)->default(0); $table->decimal('tax_amount', 12, 2)->default(0); $table->decimal('total_amount', 12, 2);
            $table->char('currency', 3)->default('INR'); $table->string('status', 20)->default('pending'); $table->string('payment_provider', 30)->nullable();
            $table->string('payment_reference')->nullable()->index(); $table->string('checkout_session_id')->nullable()->unique(); $table->json('responses')->nullable();
            $table->timestamp('expires_at')->nullable(); $table->timestamp('paid_at')->nullable(); $table->timestamps(); $table->index(['event_id', 'status']);
        });
        Schema::create('event_order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_order_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20); $table->unsignedBigInteger('item_id')->nullable(); $table->string('name');
            $table->unsignedInteger('quantity'); $table->decimal('unit_price', 12, 2); $table->decimal('line_total', 12, 2); $table->json('metadata')->nullable(); $table->timestamps();
        });
        Schema::create('event_attendees', function (Blueprint $table) {
            $table->id(); $table->foreignId('event_order_id')->constrained()->cascadeOnDelete(); $table->foreignId('ticket_type_id')->nullable()->constrained('event_ticket_types')->nullOnDelete();
            $table->string('first_name'); $table->string('last_name')->nullable(); $table->string('email')->nullable(); $table->string('phone', 30)->nullable();
            $table->json('responses')->nullable(); $table->string('status', 20)->default('registered'); $table->timestamp('checked_in_at')->nullable(); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('event_attendees'); Schema::dropIfExists('event_order_items'); Schema::dropIfExists('event_orders');
        Schema::dropIfExists('event_form_fields'); Schema::dropIfExists('event_addons'); Schema::dropIfExists('event_ticket_types'); Schema::dropIfExists('events');
    }
};
