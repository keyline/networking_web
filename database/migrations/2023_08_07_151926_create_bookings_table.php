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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mentor_profiles_id');
            $table->foreign('mentor_profiles_id')->references('id')->on('mentor_profiles');

            $table->unsignedBigInteger('meeting_id');
            $table->foreign('meeting_id')->references('id')->on('meetings');

            //$table->foreignIdFor(User::class)->constrained();
            //$table->foreignIdFor(Meeting::class)->constrained();
            $table->unsignedBigInteger('service_attribute_id');
            $table->foreign('service_attribute_id')->references('id')->on('service_attributes');

            //$table->foreignIdFor(ServiceTypeAttribute::class)->constrained();
            $table->timestamp('start');
            $table->timestamp('end');
            $table->boolean('finished')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
