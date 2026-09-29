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
        Schema::table('service_type_attribute', function (Blueprint $table) {
            $table->unique(['service_type_id', 'service_attribute_id', 'service_id'], 'service_type_attribute_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_type_attribute', function (Blueprint $table) {
            //
        });
    }
};
