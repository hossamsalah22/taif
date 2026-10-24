<?php

namespace App\Exports;

use App\Models\ChildLearningPlan;
use App\Models\ExerciseInteractionLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Every exercise attempt the child logged in the app for the exercises of one assigned plan, newest first.
 */
class ChildPlanInteractionLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(public ChildLearningPlan $childLearningPlan) {}

    public function query(): Builder
    {
        return ExerciseInteractionLog::query()
            ->with('learningExercise.lesson.goal')
            ->where('child_id', $this->childLearningPlan->child_id)
            ->whereIn('learning_exercise_id', fn (QueryBuilder $planExercises) => $planExercises
                ->select('learning_exercises.id')
                ->from('learning_exercises')
                ->join('learning_lessons', 'learning_lessons.id', '=', 'learning_exercises.learning_lesson_id')
                ->join('learning_goal_learning_plan', 'learning_goal_learning_plan.learning_goal_id', '=', 'learning_lessons.learning_goal_id')
                ->where('learning_goal_learning_plan.learning_plan_id', $this->childLearningPlan->learning_plan_id))
            ->latest()
            ->latest('id');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            __('Date'),
            __('Goal'),
            __('Lesson'),
            __('Exercise'),
            __('Interaction Type'),
            __('Result'),
            __('Attempts'),
            __('Duration (seconds)'),
        ];
    }

    /**
     * @param  ExerciseInteractionLog  $log
     * @return array<int, mixed>
     */
    public function map($log): array
    {
        $exercise = $log->learningExercise;

        return [
            $log->created_at?->format('Y-m-d H:i'),
            $exercise?->lesson?->goal?->name,
            $exercise?->lesson?->name,
            $exercise?->prompt,
            $log->interaction_type,
            $log->is_successful ? __('Successful') : __('Not successful'),
            $log->trials_count,
            $log->duration_seconds,
        ];
    }

    /**
     * Right-to-left sheet with a bold header row, so it reads naturally in Arabic.
     *
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft(app()->getLocale() === 'ar');

        return [1 => ['font' => ['bold' => true]]];
    }
}
