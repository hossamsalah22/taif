<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_goal_learning_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_plan_id')->constrained('learning_plans')->cascadeOnDelete();
            $table->foreignId('learning_goal_id')->constrained('learning_goals')->cascadeOnDelete();
            $table->integer('display_priority')->default(1);
            $table->timestamps();

            $table->unique(['learning_plan_id', 'learning_goal_id'], 'goal_plan_unique');
        });

        // Copy existing one-to-many links into the pivot table.
        DB::table('learning_goals')
            ->whereNotNull('learning_plan_id')
            ->orderBy('id')
            ->get(['id', 'learning_plan_id', 'display_priority'])
            ->each(function ($goal) {
                DB::table('learning_goal_learning_plan')->insert([
                    'learning_plan_id' => $goal->learning_plan_id,
                    'learning_goal_id' => $goal->id,
                    'display_priority' => $goal->display_priority ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        // Goals can now exist without a plan, and survive plan deletion.
        Schema::table('learning_goals', function (Blueprint $table) {
            $table->dropForeign(['learning_plan_id']);
        });

        Schema::table('learning_goals', function (Blueprint $table) {
            $table->unsignedBigInteger('learning_plan_id')->nullable()->change();
            $table->foreign('learning_plan_id')->references('id')->on('learning_plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_goal_learning_plan');
    }
};
