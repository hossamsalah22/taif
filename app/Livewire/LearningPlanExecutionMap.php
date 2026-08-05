<?php

namespace App\Livewire;

use App\Models\ChildLearningPlan;
use App\Models\ClinicalProgressReport;
use App\Models\ExerciseInteractionLog;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;

class LearningPlanExecutionMap extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ChildLearningPlan $record;

    public array $exerciseLogs = [];

    public function mount(ChildLearningPlan $record): void
    {
        $this->record = $record;

        $this->exerciseLogs = ExerciseInteractionLog::where('child_id', $this->record->child_id)
            ->get()
            ->keyBy('learning_exercise_id')
            ->toArray();
    }

    public function getExerciseStatus(int $exerciseId): string
    {
        if (isset($this->exerciseLogs[$exerciseId])) {
            return $this->exerciseLogs[$exerciseId]['status'] ?? 'in_progress';
        }

        return 'locked';
    }

    public function getLessonStatus($lesson): string
    {
        $statuses = collect($lesson->exercises)->map(fn ($ex) => $this->getExerciseStatus($ex->id));
        if ($statuses->isEmpty()) {
            return 'locked';
        }
        if ($statuses->every(fn ($s) => $s === 'completed')) {
            return 'completed';
        }
        if ($statuses->contains('in_progress') || $statuses->contains('completed')) {
            return 'in_progress';
        }

        return 'locked';
    }

    public function getGoalStatus($goal): string
    {
        $statuses = collect($goal->lessons)->map(fn ($l) => $this->getLessonStatus($l));
        if ($statuses->isEmpty()) {
            return 'locked';
        }
        if ($statuses->every(fn ($s) => $s === 'completed')) {
            return 'completed';
        }
        if ($statuses->contains('in_progress') || $statuses->contains('completed')) {
            return 'in_progress';
        }

        return 'locked';
    }

    public function getStatusColorClass(string $status): string
    {
        return match ($status) {
            'completed' => 'bg-green-50 ring-1 ring-green-600/20 text-green-800 dark:bg-green-900/20 dark:ring-green-500/30 dark:text-green-300',
            'in_progress' => 'bg-amber-50 ring-1 ring-amber-600/20 text-amber-800 dark:bg-amber-900/20 dark:ring-amber-500/30 dark:text-amber-300',
            default => 'bg-white ring-1 ring-gray-950/5 text-gray-600 dark:bg-gray-900/50 dark:ring-white/10 dark:text-gray-400',
        };
    }

    public function reportAction(): Action
    {
        return Action::make('report')
            ->label(__('Report'))
            ->icon('heroicon-m-document-text')
            ->button()
            ->outlined()
            ->size('xs')
            ->color('primary')
            ->form([
                TextInput::make('title')->required()->label(__('Title'))->maxLength(255),
                Textarea::make('body')->required()->label(__('Body'))->maxLength(3000),
                Textarea::make('smart_parental_advice')->required()->label(__('Smart Parental Advice')),
                Repeater::make('strengths')->label(__('Strengths'))->schema([
                    TextInput::make('skill_name')->required()->label(__('Skill Name')),
                    TextInput::make('percentage')->numeric()->minValue(0)->maxValue(100)->required()->label(__('Percentage')),
                ]),
                Repeater::make('improvements')->label(__('Improvements'))->schema([
                    TextInput::make('target_area')->required()->label(__('Target Area')),
                    TextInput::make('percentage')->numeric()->minValue(0)->maxValue(100)->required()->label(__('Percentage')),
                ]),
                Toggle::make('is_visible_to_parent')->default(true)->label(__('Is Visible to Parent')),
            ])
            ->fillForm(function (array $arguments) {
                $report = ClinicalProgressReport::where('child_id', $this->record->child_id)
                    ->where('learning_plan_id', $this->record->learning_plan_id)
                    ->where('reportable_type', $arguments['type'])
                    ->where('reportable_id', $arguments['id'])
                    ->first();

                if ($report) {
                    return [
                        'title' => $report->title,
                        'body' => $report->body,
                        'smart_parental_advice' => $report->smart_parental_advice,
                        'strengths' => $report->strengths,
                        'improvements' => $report->improvements,
                        'is_visible_to_parent' => $report->is_visible_to_parent,
                    ];
                }

                return [];
            })
            ->action(function (array $data, array $arguments) {
                ClinicalProgressReport::updateOrCreate(
                    [
                        'child_id' => $this->record->child_id,
                        'learning_plan_id' => $this->record->learning_plan_id,
                        'reportable_type' => $arguments['type'],
                        'reportable_id' => $arguments['id'],
                    ],
                    $data
                );

                Notification::make()
                    ->title(__('Report saved successfully'))
                    ->success()
                    ->send();
            });
    }

    public function render()
    {
        $this->record->loadMissing('child', 'learningPlan.goals.lessons.exercises');

        return view('livewire.learning-plan-execution-map');
    }
}
