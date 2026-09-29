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
        Schema::create('service_extra_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_attribute_id');
            $table->foreign('service_attribute_id')->references('id')->on('service_attributes');
            //$table->foreignIdFor(ServiceAttribute::class)->constrained();
            $table->string('type');
            $table->text('questiontxt');
            $table->boolean('isRequired')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_extra_questions');
    }
};
