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
        // Milestone check logic to notify admins when an exercise is marked 'completed'
        if ($log->status === 'completed' && (! $log->getOriginal('status') || $log->getOriginal('status') !== 'completed')) {
            $log->loadMissing(['child', 'exercise.lesson.goal.plan']);

            $childName = $log->child->name ?? 'A child';
            $exerciseName = ExerciseTypeEnum::label($log->exercise->type);
            $planName = $log->exercise->lesson->goal->plan->name ?? 'a learning plan';

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
