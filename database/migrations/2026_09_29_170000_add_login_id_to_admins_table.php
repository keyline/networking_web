<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admins', 'login_id')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('login_id', 150)->nullable()->unique()->after('user_master_id');
            });
        }

        DB::table('admins')->whereNull('login_id')->orderBy('id')->each(function ($admin) {
            DB::table('admins')->where('id', $admin->id)->update(['login_id' => $admin->email]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('admins', 'login_id')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropUnique(['login_id']);
                $table->dropColumn('login_id');
            });
        }
    }
};
