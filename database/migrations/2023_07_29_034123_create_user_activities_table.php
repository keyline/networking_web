<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->integer('activity_id')->primary();
            $table->string('user_email', 250)->nullable();
            $table->string('user_name', 250)->nullable();
            $table->enum('user_type', ['admin', 'business', 'customer', 'newspaper admin'])->nullable();
            $table->string('ip_address', 250)->nullable();
            $table->boolean('activity_type')->default(0);
            $table->longText('activity_details')->nullable();
            $table->enum('platform_type', ['web', 'mobile', 'android', 'ios'])->default('WEB');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_activities');
    }
}
