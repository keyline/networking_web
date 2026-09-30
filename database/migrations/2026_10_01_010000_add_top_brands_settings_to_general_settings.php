<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** How the app's "Top Brands" grid is ranked, chosen in admin Settings. */
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('top_brands_metric', 30)->default('alphabetical');
            $table->unsignedSmallInteger('top_brands_days')->default(30)->comment('0 = all time');
            $table->unsignedTinyInteger('top_brands_limit')->default(12);
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['top_brands_metric', 'top_brands_days', 'top_brands_limit']);
        });
    }
};
