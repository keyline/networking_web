<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * user_details is looked up by ud_um_id all over the app (dashboard, activity, members)
 * but had no index on it, so every lookup scanned the whole table.
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM user_details WHERE Column_name = 'ud_um_id'"))->isNotEmpty();
        if (! $exists) {
            Schema::table('user_details', fn ($table) => $table->index('ud_um_id', 'user_details_ud_um_id_index'));
        }
    }

    public function down(): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM user_details WHERE Key_name = 'user_details_ud_um_id_index'"))->isNotEmpty();
        if ($exists) {
            Schema::table('user_details', fn ($table) => $table->dropIndex('user_details_ud_um_id_index'));
        }
    }
};
