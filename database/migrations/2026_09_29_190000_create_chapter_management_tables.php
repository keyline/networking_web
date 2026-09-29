<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->string('city')->nullable();
            $table->string('venue')->nullable();
            $table->string('meeting_day', 15)->nullable();
            $table->time('meeting_time')->nullable();
            $table->date('established_on')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['forming', 'active', 'paused', 'closed'])->default('forming')->index();
            $table->timestamps();
        });

        Schema::create('chapter_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            // Match the signed INT legacy primary keys exactly.
            $table->integer('user_id');
            $table->integer('company_id')->nullable();
            $table->enum('role', ['member', 'president', 'vice_president', 'secretary_treasurer', 'membership_committee', 'visitor_host'])->default('member');
            $table->enum('status', ['invited', 'active', 'inactive', 'left'])->default('active')->index();
            $table->date('joined_on');
            $table->date('left_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['chapter_id', 'user_id']);
            $table->foreign('user_id')->references('um_id')->on('user_master')->cascadeOnDelete();
            $table->foreign('company_id')->references('cmp_id')->on('companies_master')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_members');
        Schema::dropIfExists('chapters');
    }
};
