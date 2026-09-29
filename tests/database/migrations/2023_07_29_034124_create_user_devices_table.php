<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('user_id');
            $table->enum('device_type', ['android', 'ios', 'web'])->nullable();
            $table->string('device_token', 250)->nullable();
            $table->longText('app_access_token')->nullable();
            $table->boolean('published')->default(1);
            $table->timestamps()->default('current_timestamp()');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_devices');
    }
}
