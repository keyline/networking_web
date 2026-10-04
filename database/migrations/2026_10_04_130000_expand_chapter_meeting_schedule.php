<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->string('meeting_frequency', 30)->default('not_scheduled')->after('venue');
            $table->unsignedTinyInteger('meeting_day_of_month')->nullable()->after('meeting_day');
            $table->unsignedTinyInteger('meeting_second_day_of_month')->nullable()->after('meeting_day_of_month');
            $table->string('meeting_mode', 20)->nullable()->after('meeting_time');
            $table->text('meeting_link')->nullable()->after('meeting_mode');
            $table->text('meeting_address')->nullable()->after('meeting_link');
        });

        DB::table('chapters')->whereNotNull('meeting_day')->update([
            'meeting_frequency' => 'weekly',
            'meeting_mode' => 'offline',
            'meeting_address' => DB::raw('venue'),
        ]);
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['meeting_frequency', 'meeting_day_of_month', 'meeting_second_day_of_month', 'meeting_mode', 'meeting_link', 'meeting_address']);
        });
    }
};
