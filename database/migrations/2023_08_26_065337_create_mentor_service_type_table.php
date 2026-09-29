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
        Schema::create('mentor_service_type', function (Blueprint $table) {
            //$table->id();
            $table->unsignedBigInteger('mentor_profile_id');

            $table->foreign('mentor_profile_id')->references('id')->on('mentor_profiles');

            $table->unsignedBigInteger('service_type_id');

            $table->foreign('service_type_id')->references('id')->on('service_types');

            $table->primary(['mentor_profile_id', 'service_type_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_service_type');
    }
};
