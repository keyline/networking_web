<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mentor_availabilities', function (Blueprint $table) {
            $table->renameColumn('user_id', 'mentor_user_id');
            $table->integer('day_of_week_id')->after('user_id');
            $table->boolean('is_active')->default(true)->after('to');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
