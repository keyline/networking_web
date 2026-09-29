<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('companies_details', 'public_slug')) {
            Schema::table('companies_details', function (Blueprint $table) {
                $table->string('public_slug', 180)->nullable()->unique()->after('cmpd_name');
            });
        }

        DB::table('companies_details')->whereNull('public_slug')->orderBy('cmpd_id')->get()->each(function ($company) {
            $base = Str::slug($company->cmpd_name) ?: 'business';
            $slug = $base;
            $suffix = 2;
            while (DB::table('companies_details')->where('public_slug', $slug)->exists()) {
                $slug = $base . '-' . $suffix++;
            }
            DB::table('companies_details')->where('cmpd_id', $company->cmpd_id)->update(['public_slug' => $slug]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies_details', 'public_slug')) {
            Schema::table('companies_details', fn (Blueprint $table) => $table->dropColumn('public_slug'));
        }
    }
};
