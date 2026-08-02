<?php

namespace App\Filament\Resources\ChildLearningPlans\Pages;

use App\Filament\Resources\ChildLearningPlans\ChildLearningPlanResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ViewChildLearningPlanProgress extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ChildLearningPlanResource::class;

    protected string $view = 'filament.resources.child-learning-plans.pages.view-child-learning-plan-progress';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function reportAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('report')
            ->label(__('Report'))
            ->icon('heroicon-m-document-text')
            ->button()
            ->size('sm')
            ->color('gray')
            ->form([
                \Filament\Forms\Components\TextInput::make('title')->required()->maxLength(255),
                \Filament\Forms\Components\Textarea::make('body')->required()->maxLength(3000),
                \Filament\Forms\Components\Textarea::make('smart_parental_advice')->required(),
                \Filament\Forms\Components\Repeater::make('strengths')->schema([
                    \Filament\Forms\Components\TextInput::make('skill_name')->required(),
                    \Filament\Forms\Components\TextInput::make('percentage')->numeric()->minValue(0)->maxValue(100)->required(),
                ]),
                \Filament\Forms\Components\Repeater::make('improvements')->schema([
                    \Filament\Forms\Components\TextInput::make('target_area')->required(),
                    \Filament\Forms\Components\TextInput::make('percentage')->numeric()->minValue(0)->maxValue(100)->required(),
                ]),
                \Filament\Forms\Components\Toggle::make('is_visible_to_parent')->default(true),
            ])
            ->fillForm(function (array $arguments) {
                $report = \App\Models\ClinicalProgressReport::where('child_id', $this->record->child_id)
                    ->where('learning_plan_id', $this->record->learning_plan_id)
                    ->where('reportable_type', \App\Models\LearningExercise::class)
                    ->where('reportable_id', $arguments['id'] ?? null)
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
                \App\Models\ClinicalProgressReport::updateOrCreate(
                    [
                        'child_id' => $this->record->child_id,
                        'learning_plan_id' => $this->record->learning_plan_id,
                        'reportable_type' => \App\Models\LearningExercise::class,
                        'reportable_id' => $arguments['id'],
                    ],
                    $data
                );
            });
    }
}
