<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('general_settings')->where('site_name', 'Net-Works')->update(['site_name' => 'Networx']);
        DB::table('general_settings')->where('from_name', 'Net-Works')->update(['from_name' => 'Networx']);

        foreach (['name', 'eyebrow', 'title', 'body', 'settings'] as $column) {
            DB::table('homepage_sections')
                ->where($column, 'like', '%Net-Works%')
                ->update([$column => DB::raw("REPLACE($column, 'Net-Works', 'Networx')")]);
        }
    }

    public function down(): void
    {
        DB::table('general_settings')->where('site_name', 'Networx')->update(['site_name' => 'Net-Works']);
        DB::table('general_settings')->where('from_name', 'Networx')->update(['from_name' => 'Net-Works']);

        foreach (['name', 'eyebrow', 'title', 'body', 'settings'] as $column) {
            DB::table('homepage_sections')
                ->where($column, 'like', '%Networx%')
                ->update([$column => DB::raw("REPLACE($column, 'Networx', 'Net-Works')")]);
        }
    }
};
