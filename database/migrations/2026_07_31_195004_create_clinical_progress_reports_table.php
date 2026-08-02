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
        Schema::create('clinical_progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('learning_plan_id')->constrained('learning_plans')->cascadeOnDelete();
            $table->morphs('reportable'); // to attach to Goal, Lesson, or Exercise
            $table->text('title');
            $table->text('body')->nullable();
            $table->text('smart_parental_advice')->nullable();
            $table->json('strengths')->nullable(); // percentage slider config
            $table->json('improvements')->nullable(); // percentage slider config
            $table->boolean('is_visible_to_parent')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_progress_reports');
    }
};
