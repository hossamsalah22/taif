<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_goal_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_goal_id')->constrained('learning_goals')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['learning_goal_id', 'question_id'], 'goal_question_unique');
        });

        // Questions can exist outside an assessment (question bank).
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['assessment_id']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_id')->nullable()->change();
            $table->foreign('assessment_id')->references('id')->on('assessments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_goal_question');
    }
};
