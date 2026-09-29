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
        //Schema::rename('service_type_attribute', 'service_attribute');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Schema::rename('service_attribute', 'service_type_attribute');


    }
};
