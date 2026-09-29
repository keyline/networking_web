<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('site_name', 250)->nullable();
            $table->string('site_phone')->nullable();
            $table->string('site_mail')->nullable();
            $table->string('system_email', 250)->nullable();
            $table->string('site_url')->nullable();
            $table->longText('description')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('site_front_logo', 250)->nullable();
            $table->string('site_favicon', 250)->nullable();
            $table->longText('become_partner_text')->nullable();
            $table->longText('copyright_statement')->nullable();
            $table->string('google_map_api_code', 250)->nullable();
            $table->longText('google_analytics_code')->nullable();
            $table->longText('google_pixel_code')->nullable();
            $table->longText('facebook_tracking_code')->nullable();
            $table->boolean('newspaper_section')->default(0);
            $table->string('theme_color', 250)->nullable();
            $table->string('font_color', 250)->nullable();
            $table->string('twitter_profile', 250)->nullable();
            $table->string('facebook_profile', 250)->nullable();
            $table->string('instagram_profile', 250)->nullable();
            $table->string('linkedin_profile', 250)->nullable();
            $table->string('youtube_profile', 250)->nullable();
            $table->string('sms_authentication_key', 250)->nullable();
            $table->string('sms_sender_id', 250)->nullable();
            $table->string('sms_base_url', 250)->nullable();
            $table->string('from_email', 250)->nullable();
            $table->string('from_name', 250)->nullable();
            $table->string('smtp_host', 250)->nullable();
            $table->string('smtp_username', 250)->nullable();
            $table->string('smtp_password', 250)->nullable();
            $table->string('smtp_port', 250)->nullable();
            $table->longText('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->longText('footer_text')->nullable();
            $table->longText('footer_link_name')->nullable();
            $table->longText('footer_link')->nullable();
            $table->enum('stripe_payment_type', ['1', '2']);
            $table->longText('stripe_sandbox_sk')->nullable();
            $table->longText('stripe_sandbox_pk')->nullable();
            $table->longText('stripe_live_sk')->nullable();
            $table->longText('stripe_live_pk')->nullable();
            $table->float('partner_commission', 10, 2)->default(0.00);
            $table->timestamps()->default('current_timestamp()');
            $table->boolean('published')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_settings');
    }
}
