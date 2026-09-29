<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per tracked interaction with a business (profile view, call tap, ...).
     * Feeds the business owner dashboard in the mobile app.
     */
    public function up(): void
    {
        Schema::create('business_analytics_events', function (Blueprint $table) {
            $table->bigIncrements('bae_id');
            $table->unsignedInteger('bae_cmp_id');
            $table->string('bae_event_type', 20)->comment('view, call, whatsapp, email, enquiry, share');
            $table->unsignedInteger('bae_um_id')->nullable();
            $table->string('bae_device_id', 100)->nullable()->comment('Separates guests who share one guest account');
            $table->string('bae_source', 20)->nullable()->comment('ANDROID, IOS, WEB');
            $table->timestamp('bae_created_at')->useCurrent();

            $table->index(['bae_cmp_id', 'bae_event_type', 'bae_created_at'], 'bae_cmp_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_analytics_events');
    }
};
