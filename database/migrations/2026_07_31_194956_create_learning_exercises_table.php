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
        Schema::create('learning_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_lesson_id')->constrained('learning_lessons')->cascadeOnDelete();
            $table->string('difficulty_level')->default(\App\Enums\DifficultyLevel::MEDIUM->value);
            $table->boolean('is_locked')->default(true);
            $table->integer('max_attempts')->default(3);
            $table->integer('display_priority')->default(1);
            $table->string('type'); // image_selection, matching, ordering, distinguishing, audio_flashcards, instructional_video
            $table->json('configuration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_exercises');
    }
};
