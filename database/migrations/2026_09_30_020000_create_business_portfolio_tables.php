<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_portfolios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->string('tagline', 180)->nullable();
            $table->text('about')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('website')->nullable();
            $table->string('notification_email')->nullable();
            $table->string('notification_mobile', 30)->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->string('whatsapp_message', 255)->nullable();
            $table->boolean('whatsapp_enabled')->default(true);
            $table->boolean('contact_form_enabled')->default(true);
            $table->boolean('is_published')->default(false);
            $table->json('published_snapshot')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'company_id']);
        });

        Schema::create('business_portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->enum('type', ['product', 'service']);
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('price_label', 100)->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['company_id', 'is_active', 'sort_order'], 'portfolio_items_company_active_sort');
        });

        Schema::create('business_portfolio_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->enum('type', ['image', 'youtube']);
            $table->string('path')->nullable();
            $table->string('youtube_id', 20)->nullable();
            $table->string('title', 150)->nullable();
            $table->string('caption', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'type', 'is_active', 'sort_order'], 'portfolio_media_company_type_active_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_portfolio_media');
        Schema::dropIfExists('business_portfolio_items');
        Schema::dropIfExists('business_portfolios');
    }
};
