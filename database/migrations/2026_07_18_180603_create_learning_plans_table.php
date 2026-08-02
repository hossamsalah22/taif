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
        Schema::create('learning_plans', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('autism_level');
            $table->integer('weekly_sessions_count')->default(3);
            $table->string('phase_duration')->nullable();
            $table->integer('max_daily_goals')->nullable();
            $table->integer('max_daily_lessons')->nullable();
            $table->integer('max_daily_exercises')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_plans');
    }
};
