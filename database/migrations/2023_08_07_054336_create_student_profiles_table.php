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
        Schema::create('student_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained();

            $table->string('first_name');

            $table->string('last_name');

            $table->string('full_name');

            $table->timestamp('created_at')->useCurrent();

            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
