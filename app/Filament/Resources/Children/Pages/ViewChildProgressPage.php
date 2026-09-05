<?php

namespace App\Filament\Resources\Children\Pages;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Filament\Resources\Children\ChildResource;
use App\Models\ChildLearningPlan;
use App\Models\ClinicalProgressReport;
use App\Models\ExerciseInteractionLog;
use App\Models\LearningExercise;
use App\Models\LearningGoal;
use App\Models\LearningLesson;
use App\Models\LearningPlan;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ViewChildProgressPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ChildResource::class;

    protected string $view = 'filament.resources.children.pages.view-child-progress-page';

    public ?ChildLearningPlan $progressTree = null;

    public ?LearningPlan $plan = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadProgressTree();
    }

    public function getTitle(): string|Htmlable
    {
        return __('Progress details for: ').$this->record->name;
    }

    protected function loadProgressTree()
    {
        $this->progressTree = ChildLearningPlan::where('child_id', $this->record->id)
            ->whereIn('status', [ChildLearningPlanStatusEnum::InProgress, ChildLearningPlanStatusEnum::Completed])
            ->with([
                'learningPlan.goals.lessons.exercises',
            ])
            ->first();

        if (! $this->progressTree) {
            return;
        }

        $this->plan = $this->progressTree->learningPlan;

        $completedGoalIds = $this->record->completedGoals()->pluck('learning_goals.id')->toArray();
        $completedLessonIds = $this->record->completedLessons()->pluck('learning_lessons.id')->toArray();
        $completedExerciseIds = $this->record->completedExercises()->pluck('learning_exercises.id')->toArray();

        // We also want to know which exercises have interaction logs to mark them as "In Progress" (Amber)
        $interactedExerciseIds = ExerciseInteractionLog::where('child_id', $this->record->id)
            ->pluck('learning_exercise_id')->unique()->toArray();

        // Load existing reports mapped by type and id
        $reports = ClinicalProgressReport::where('child_id', $this->record->id)->get();

        if ($this->plan && $this->plan->goals) {
            $this->plan->goals->transform(function ($goal) use ($completedGoalIds, $completedLessonIds, $completedExerciseIds, $interactedExerciseIds, $reports) {
                $goal->is_completed = in_array($goal->id, $completedGoalIds);
                $goal->has_report = $reports->where('reportable_type', LearningGoal::class)->where('reportable_id', $goal->id)->isNotEmpty();

                if ($goal->lessons) {
                    $goal->lessons->transform(function ($lesson) use ($completedLessonIds, $completedExerciseIds, $interactedExerciseIds, $reports) {
                        $lesson->is_completed = in_array($lesson->id, $completedLessonIds);
                        $lesson->has_report = $reports->where('reportable_type', LearningLesson::class)->where('reportable_id', $lesson->id)->isNotEmpty();

                        if ($lesson->exercises) {
                            $lesson->exercises->transform(function ($exercise) use ($completedExerciseIds, $interactedExerciseIds, $reports) {
                                $exercise->is_completed = in_array($exercise->id, $completedExerciseIds);
                                $exercise->is_in_progress = in_array($exercise->id, $interactedExerciseIds);
                                $exercise->has_report = $reports->where('reportable_type', LearningExercise::class)->where('reportable_id', $exercise->id)->isNotEmpty();

                                return $exercise;
                            });
                            // Lesson is in progress if any exercise is in progress or completed
                            $lesson->is_in_progress = $lesson->exercises->where('is_in_progress', true)->count() > 0 || $lesson->exercises->where('is_completed', true)->count() > 0;
                        }

                        return $lesson;
                    });
                    // Goal is in progress if any lesson is in progress or completed
                    $goal->is_in_progress = $goal->lessons->where('is_in_progress', true)->count() > 0 || $goal->lessons->where('is_completed', true)->count() > 0;
                }

                return $goal;
            });
        }
    }

    public function manageReportAction(): Action
    {
        return Action::make('manageReportAction')
            ->label(__('Manage Report'))
            ->icon('heroicon-o-document-text')
            ->button()
            ->outlined()
            ->size('sm')
            ->modalHeading(__('Clinical Progress Report'))
            ->fillForm(function () {
                $report = ClinicalProgressReport::where('child_id', $this->record->id)
                    ->where('reportable_type', LearningPlan::class)
                    ->where('reportable_id', $this->plan->id)
                    ->first();

                if ($report) {
                    $strengths = $report->strengths ?? [];
                    if (isset($strengths['ar']) || isset($strengths['en'])) {
                        $strengths = $strengths[app()->getLocale()] ?? $strengths['ar'] ?? $strengths['en'] ?? [];
                    }

                    $improvements = $report->improvements ?? [];
                    if (isset($improvements['ar']) || isset($improvements['en'])) {
                        $improvements = $improvements[app()->getLocale()] ?? $improvements['ar'] ?? $improvements['en'] ?? [];
                    }

                    return [
                        'title' => $report->getTranslations('title'),
                        'strengths' => $strengths,
                        'improvements' => $improvements,
                        'smart_parental_advice' => $report->getTranslations('smart_parental_advice'),
                        'is_visible_to_parent' => $report->is_visible_to_parent,
                    ];
                }

                return [];
            })
            ->form([
                TextInput::make('title')
                    ->required()
                    ->translatableTabs()
                    ->label(__('Report Title')),
                Repeater::make('strengths')
                    ->label(__('Strengths'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->translatableTabs(),
                        FileUpload::make('icon')
                            ->label(__('Icon'))
                            ->directory('report-icons')
                            ->disk('public')
                            ->image()
                            ->required(),
                    ])
                    ->columnSpanFull(),
                Repeater::make('improvements')
                    ->label(__('Needs Improvement'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->translatableTabs(),
                        FileUpload::make('icon')
                            ->label(__('Icon'))
                            ->directory('report-icons')
                            ->disk('public')
                            ->image()
                            ->required(),
                    ])
                    ->columnSpanFull(),
                Textarea::make('smart_parental_advice')
                    ->label(__('Smart Parental Advice'))
                    ->required()
                    ->columnSpanFull()
                    ->translatableTabs(),
                Toggle::make('is_visible_to_parent')
                    ->label(__('Visible to Parent'))
                    ->default(true)
                    ->required(),
            ])
            ->action(function (array $data) {
                ClinicalProgressReport::updateOrCreate(
                    [
                        'child_id' => $this->record->id,
                        'reportable_type' => LearningPlan::class,
                        'reportable_id' => $this->plan->id,
                    ],
                    [
                        'learning_plan_id' => $this->plan->id,
                        'title' => $data['title'],
                        'strengths' => $data['strengths'] ?? null,
                        'improvements' => $data['improvements'] ?? null,
                        'smart_parental_advice' => $data['smart_parental_advice'],
                        'is_visible_to_parent' => $data['is_visible_to_parent'],
                    ]
                );

                Notification::make()
                    ->title(__('Report published to mobile app successfully.'))
                    ->success()
                    ->send();

                $this->loadProgressTree();
            });
    }

    public function exportLogsAction(): Action
    {
        return Action::make('exportLogsAction')
            ->label(__('Export Logs'))
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->size('xs')
            ->action(function (array $arguments) {
                $exerciseId = $arguments['id'];

                $logs = ExerciseInteractionLog::where('child_id', $this->record->id)
                    ->where('learning_exercise_id', $exerciseId)
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($logs->isEmpty()) {
                    Notification::make()->title(__('No interaction logs found for this exercise.'))->warning()->send();

                    return;
                }

                $csvHeader = ['Date', 'Is Successful', 'Trials Count', 'Duration (s)'];
                $csvData = [];
                $csvData[] = implode(',', $csvHeader);

                foreach ($logs as $log) {
                    $row = [
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->is_successful ? 'Yes' : 'No',
                        $log->trials_count,
                        $log->duration_seconds,
                    ];
                    $csvData[] = implode(',', $row);
                }

                $csvContent = implode("\n", $csvData);
                $fileName = 'child_'.$this->record->id.'_exercise_'.$exerciseId.'_logs.csv';

                return response()->streamDownload(function () use ($csvContent) {
                    echo "\xEF\xBB\xBF"; // UTF-8 BOM
                    echo $csvContent;
                }, $fileName, ['Content-Type' => 'text/csv']);
            });
    }
}
