<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One denormalised row per business so the public directory search is a single
 * indexed query instead of joins + LIKE '%...%' across companies, owners,
 * categories and portfolio items. Filled by App\Services\BusinessSearchIndex.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_search_index', function (Blueprint $table) {
            $table->unsignedInteger('cmp_id')->primary();
            $table->string('slug', 180)->nullable();
            $table->string('name', 255)->default('');
            $table->string('owners', 500)->default('');
            $table->string('categories', 500)->default('');
            $table->text('content')->nullable();
            $table->string('logo', 255)->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->timestamp('indexed_at')->nullable();
            $table->index(['is_public', 'is_sponsored']);
        });

        DB::statement('ALTER TABLE business_search_index ADD FULLTEXT ft_business_search (name, owners, categories, content)');
    }

    public function down(): void
    {
        Schema::dropIfExists('business_search_index');
    }
};
