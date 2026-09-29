<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_orders', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('customer_phone');
            $table->string('gst_number', 30)->nullable()->after('company_name');
        });
        Schema::table('event_attendees', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('phone');
            $table->string('designation')->nullable()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('event_attendees', fn (Blueprint $table) => $table->dropColumn(['company_name', 'designation']));
        Schema::table('event_orders', fn (Blueprint $table) => $table->dropColumn(['company_name', 'gst_number']));
    }
};
