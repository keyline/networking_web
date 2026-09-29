<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('cycle_start_month')->default(1)->after('renewal_basis');
            $table->unsignedTinyInteger('cycle_day')->default(1)->after('cycle_start_month');
        });
    }

    public function down(): void
    {
        Schema::table('membership_settings', fn (Blueprint $table) => $table->dropColumn(['cycle_start_month', 'cycle_day']));
    }
};
