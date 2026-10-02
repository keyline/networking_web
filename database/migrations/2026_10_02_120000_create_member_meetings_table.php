<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reported_by_um_id');
            $table->unsignedBigInteger('counterpart_um_id');
            $table->unsignedBigInteger('invited_by_um_id');
            $table->dateTime('meeting_at');
            $table->enum('mode', ['in_person', 'online', 'phone']);
            $table->string('location', 250)->nullable();
            $table->text('details');
            $table->text('outcome')->nullable();
            $table->date('follow_up_on')->nullable();
            $table->timestamps();

            $table->index(['reported_by_um_id', 'meeting_at']);
            $table->index(['counterpart_um_id', 'meeting_at']);
            $table->index('invited_by_um_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_meetings');
    }
};
