<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-controlled flag: sponsored businesses are featured on the app
     * home screen (replaces the old "established over 100 years" rule).
     */
    public function up(): void
    {
        Schema::table('companies_details', function (Blueprint $table) {
            $table->boolean('cmpd_is_sponsored')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('companies_details', function (Blueprint $table) {
            $table->dropIndex(['cmpd_is_sponsored']);
            $table->dropColumn('cmpd_is_sponsored');
        });
    }
};
