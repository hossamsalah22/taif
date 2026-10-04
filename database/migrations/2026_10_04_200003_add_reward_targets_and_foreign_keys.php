<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            // question, lesson, goal, plan. Existing rewards were all used by lessons.
            $table->string('target_type')->default('lesson')->after('type');
        });

        // Clean up string reward ids into real foreign keys.
        $this->castToForeignKey('learning_lessons', 'reward_id');
        $this->castToForeignKey('child_rewards', 'reward_id', false);

        Schema::table('learning_goals', function (Blueprint $table) {
            $table->foreignId('reward_id')->nullable()->constrained('rewards')->nullOnDelete();
        });
        Schema::table('learning_plans', function (Blueprint $table) {
            $table->foreignId('reward_id')->nullable()->constrained('rewards')->nullOnDelete();
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('reward_id')->nullable()->constrained('rewards')->nullOnDelete();
        });

        // A child can only unlock a given reward once.
        DB::table('child_rewards')
            ->select('child_id', 'reward_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('child_id', 'reward_id')
            ->get()
            ->each(fn ($row) => DB::table('child_rewards')
                ->where('child_id', $row->child_id)
                ->where('reward_id', $row->reward_id)
                ->where('id', '!=', $row->keep_id)
                ->delete());

        Schema::table('child_rewards', function (Blueprint $table) {
            $table->unique(['child_id', 'reward_id'], 'child_reward_unique');
        });
    }

    public function down(): void
    {
        Schema::table('child_rewards', function (Blueprint $table) {
            $table->dropUnique('child_reward_unique');
        });
        foreach (['questions', 'learning_plans', 'learning_goals'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('reward_id');
            });
        }
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn('target_type');
        });
    }

    private function castToForeignKey(string $table, string $column, bool $nullable = true): void
    {
        // Drop dangling values so the foreign key can be created.
        DB::table($table)->whereNotNull($column)
            ->whereNotIn($column, DB::table('rewards')->select('id'))
            ->when($nullable,
                fn ($q) => $q->update([$column => null]),
                fn ($q) => $q->delete());

        Schema::table($table, function (Blueprint $t) use ($column, $nullable) {
            $col = $t->unsignedBigInteger($column);
            if ($nullable) {
                $col->nullable();
            }
            $col->change();
        });

        Schema::table($table, function (Blueprint $t) use ($column, $nullable) {
            $fk = $t->foreign($column)->references('id')->on('rewards');
            $nullable ? $fk->nullOnDelete() : $fk->cascadeOnDelete();
        });
    }
};
