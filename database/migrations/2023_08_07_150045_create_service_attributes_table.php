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
        Schema::create('service_attributes', function (Blueprint $table) {

            $table->id();

            $table->string('title');

            $table->text('description');

            $table->string('slug');

            $table->string('duration');

            $table->float('actual_amount', 10, 2);

            $table->float('slashed_amount', 10, 2);

            $table->boolean('status')->default(true);

            $table->timestamp('created_at')->useCurrent();

            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_attributes');
    }
};
