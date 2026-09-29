<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->integer('package_id')->primary();
            $table->enum('module_for', ['partner subscription', 'voice assistant'])->nullable();
            $table->enum('type', ['0', '1'])->default('0')->comment("0=monthly,1=yearly");
            $table->string('title', 250)->nullable();
            $table->longText('description')->nullable();
            $table->float('subscription_price', 10, 2)->default(0.00);
            $table->enum('status', ['1', '0', '3'])->default('1')->comment("1=acitve,0=inactive,2=deleted");
            $table->integer('created_by');
            $table->timestamp('created_on')->default('current_timestamp()');
            $table->timestamp('updated_on')->default('current_timestamp()');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_packages');
    }
}
