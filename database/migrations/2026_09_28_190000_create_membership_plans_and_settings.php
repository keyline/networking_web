<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('code', 30)->unique();
            $table->unsignedSmallInteger('duration_months');
            $table->decimal('fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('membership_settings', function (Blueprint $table) {
            $table->id();
            $table->string('renewal_basis', 30)->default('joining_date');
            $table->timestamps();
        });

        Schema::table('member_memberships', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('user_id')->index();
        });

        DB::table('membership_plans')->insert([
            ['name' => 'Monthly', 'code' => 'monthly', 'duration_months' => 1, 'fee' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Half-yearly', 'code' => 'half_yearly', 'duration_months' => 6, 'fee' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Yearly', 'code' => 'yearly', 'duration_months' => 12, 'fee' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('membership_settings')->insert(['renewal_basis' => 'joining_date', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('member_memberships', fn (Blueprint $table) => $table->dropColumn('plan_id'));
        Schema::dropIfExists('membership_settings');
        Schema::dropIfExists('membership_plans');
    }
};
