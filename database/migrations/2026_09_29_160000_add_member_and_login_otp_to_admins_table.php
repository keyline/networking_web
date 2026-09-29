<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admins', 'user_master_id')) {
            Schema::table('admins', fn (Blueprint $table) => $table->unsignedBigInteger('user_master_id')->nullable()->unique()->after('id'));
        }
        if (!Schema::hasColumn('admins', 'login_otp_hash')) {
            Schema::table('admins', fn (Blueprint $table) => $table->string('login_otp_hash')->nullable()->after('remember_token'));
        }
        if (!Schema::hasColumn('admins', 'login_otp_expires_at')) {
            Schema::table('admins', fn (Blueprint $table) => $table->timestamp('login_otp_expires_at')->nullable()->after('login_otp_hash'));
        }
        if (!Schema::hasColumn('admins', 'login_otp_attempts')) {
            Schema::table('admins', fn (Blueprint $table) => $table->unsignedTinyInteger('login_otp_attempts')->default(0)->after('login_otp_expires_at'));
        }
        if (!Schema::hasColumn('admins', 'last_login_at')) {
            Schema::table('admins', fn (Blueprint $table) => $table->timestamp('last_login_at')->nullable()->after('login_otp_attempts'));
        }
    }

    public function down(): void
    {
        foreach (['last_login_at', 'login_otp_attempts', 'login_otp_expires_at', 'login_otp_hash'] as $column) {
            if (Schema::hasColumn('admins', $column)) {
                Schema::table('admins', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
        if (Schema::hasColumn('admins', 'user_master_id')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropUnique(['user_master_id']);
                $table->dropColumn('user_master_id');
            });
        }
    }
};
