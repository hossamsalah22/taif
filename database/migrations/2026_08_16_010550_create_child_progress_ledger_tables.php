<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('child_completed_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_goal_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['child_id', 'learning_goal_id']);
        });

        Schema::create('child_completed_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['child_id', 'learning_lesson_id']);
        });

        Schema::create('child_completed_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_exercise_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['child_id', 'learning_exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_completed_exercises');
        Schema::dropIfExists('child_completed_lessons');
        Schema::dropIfExists('child_completed_goals');
    }
};
