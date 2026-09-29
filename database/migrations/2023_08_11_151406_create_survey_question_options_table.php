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
        Schema::create('survey_question_options', function (Blueprint $table) {
            $table->id('option_id');
            $table->unsignedBigInteger('survey_id');
            $table->foreign('survey_id')->references('id')->on('surveys');
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')->references('question_id')->on('survey_questions');
            $table->longText('option_name');
            $table->unsignedInteger('option_weight');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_question_options');
    }
};
