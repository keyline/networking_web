<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $used = DB::table('companies_details')
            ->whereNotNull('public_slug')
            ->where('public_slug', 'not regexp', '^business-[0-9]+$')
            ->pluck('public_slug')
            ->flip()
            ->all();

        DB::table('companies_details')
            ->where(function ($query) {
                $query->whereNull('public_slug')
                    ->orWhere('public_slug', '')
                    ->orWhereRaw("public_slug regexp '^business-[0-9]+$'");
            })
            ->orderBy('cmpd_id')
            ->get(['cmpd_id', 'cmpd_name'])
            ->each(function ($business) use (&$used) {
                $base = Str::slug($business->cmpd_name ?: '') ?: 'business';
                $slug = $base;
                $suffix = 2;

                while (isset($used[$slug])) {
                    $slug = $base.'-'.$suffix++;
                }

                DB::table('companies_details')
                    ->where('cmpd_id', $business->cmpd_id)
                    ->update(['public_slug' => $slug]);

                $used[$slug] = true;
            });
    }

    public function down(): void
    {
        // Name-based public URLs are intentionally retained on rollback.
    }
};
