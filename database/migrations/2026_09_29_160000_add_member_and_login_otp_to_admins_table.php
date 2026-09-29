<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('user_master_id')->nullable()->unique()->after('id');
            $table->string('login_otp_hash')->nullable()->after('remember_token');
            $table->timestamp('login_otp_expires_at')->nullable()->after('login_otp_hash');
            $table->unsignedTinyInteger('login_otp_attempts')->default(0)->after('login_otp_expires_at');
            $table->timestamp('last_login_at')->nullable()->after('login_otp_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['user_master_id']);
            $table->dropColumn(['user_master_id', 'login_otp_hash', 'login_otp_expires_at', 'login_otp_attempts', 'last_login_at']);
        });
    }
};
