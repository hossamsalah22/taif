<?php

namespace App\Observers;

use App\Enums\ExerciseTypeEnum;
use App\Models\Admin;
use App\Models\ExerciseInteractionLog;
use Filament\Notifications\Notification;

class ExerciseInteractionLogObserver
{
    public function created(ExerciseInteractionLog $log): void
    {
        $this->checkMilestones($log);
    }

    public function updated(ExerciseInteractionLog $log): void
    {
        $this->checkMilestones($log);
    }

    protected function checkMilestones(ExerciseInteractionLog $log): void
    {
        // Milestone check logic to notify admins when an exercise is marked 'is_successful'
        if ($log->is_successful && (! $log->getOriginal('is_successful'))) {
            $log->loadMissing(['child', 'learningExercise.lesson.goal.plan']);

            $childName = $log->child->name ?? 'A child';
            $exerciseName = ExerciseTypeEnum::label($log->learningExercise->type);
            $planName = $log->learningExercise->lesson->goal->plan->title ?? 'a learning plan';

            $admins = Admin::all();

            foreach ($admins as $admin) {
                Notification::make()
                    ->title(__('Milestone Reached!'))
                    ->body(__(':child has successfully mastered :exercise within :plan.', [
                        'child' => $childName,
                        'exercise' => $exerciseName,
                        'plan' => $planName,
                    ]))
                    ->success()
                    ->sendToDatabase($admin);
            }
        }
    }
}
