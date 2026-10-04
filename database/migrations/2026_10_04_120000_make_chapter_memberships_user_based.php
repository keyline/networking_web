<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Chapter membership now belongs to the user; their linked businesses are derived live.
        DB::table('chapter_members')->update(['company_id' => null]);
    }

    public function down(): void
    {
        // The previous single represented business cannot be reconstructed reliably.
    }
};
