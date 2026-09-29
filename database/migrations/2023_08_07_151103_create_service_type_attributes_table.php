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
        Schema::create('service_type_attribute', function (Blueprint $table) {

            $table->foreignId('service_type_id')->constrained();

            $table->foreignId('service_attribute_id')->constrained();
            //$table->foreignId('service_id')->references('id')->on('services');

            $table->foreignId('service_id')->constrained();

            $table->boolean('is_default')->default(false);

            $table->boolean('is_active')->default(true);

            $table->primary(['service_type_id','service_attribute_id', 'service_id' ]);

            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_type_attributes');
    }
};
