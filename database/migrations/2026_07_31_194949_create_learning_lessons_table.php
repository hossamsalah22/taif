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
        Schema::create('learning_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_goal_id')->constrained('learning_goals')->cascadeOnDelete();
            $table->json('name'); // translatable
            $table->string('reward_id')->nullable(); // Just a string identifier for now as per feedback question not fully answered, or we can use a string since it's "motivational reward asset".
            $table->boolean('is_locked')->default(true);
            $table->integer('display_priority')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_lessons');
    }
};
