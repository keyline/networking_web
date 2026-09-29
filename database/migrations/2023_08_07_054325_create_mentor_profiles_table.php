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
        Schema::create('mentor_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained();

            $table->string('first_name');

            $table->string('last_name');

            $table->string('display_name');

            $table->string('mobile', 10);

            $table->string('social_url');

            $table->string('title');

            $table->string('description');

            $table->string('timezone');

            $table->string('profile_pic');

            $table->boolean('has_synced_calender')->default(0);

            $table->string('full_name');

            $table->string('google_mail');

            $table->string('buffer');

            $table->string('use_google_meet');

            $table->string('is_email_verified');

            $table->boolean('service_added')->default(0);

            $table->boolean('slots_added')->default(0);

            $table->boolean('profile_completion')->default(0);

            $table->boolean('profile_shared')->default(0);

            $table->boolean('subscribe_to_whatsapp')->default(0);

            $table->boolean('account_details_added')->default(0);

            $table->string('personal_meeting_url')->nullable();

            $table->string('signup_source')->nullable();

            $table->string('ratings_count');

            $table->string('booking_count');

            $table->string('upcoming_calls');

            $table->string('booking_period');

            $table->timestamp('created_at')->useCurrent();

            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_profiles');
    }
};
