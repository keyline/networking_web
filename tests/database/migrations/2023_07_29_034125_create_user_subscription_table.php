<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscription', function (Blueprint $table) {
            $table->integer('user_subscription_id')->primary();
            $table->integer('user_id');
            $table->integer('subscription_id');
            $table->integer('status');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('created_ts')->default('current_timestamp()');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_subscription');
    }
}
