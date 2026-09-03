<?php

namespace App\Filament\Resources\ClinicalProgressReports\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ClinicalProgressReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('child_id')
                    ->relationship('child', 'name')
                    ->searchable()
                    ->required(),
                Select::make('learning_plan_id')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('reportable_type', 'App\\Models\\LearningPlan');
                        $set('reportable_id', $state);
                    })
                    ->live(),
                Hidden::make('reportable_type')
                    ->default('App\\Models\\LearningPlan'),
                Hidden::make('reportable_id'),
                TextInput::make('title')
                    ->required()
                    ->columnSpanFull()
                    ->label(__('Report Title (e.g. Week 1 Report)')),
                TagsInput::make('strengths')
                    ->label(__('Strengths'))
                    ->placeholder(__('Press Enter to add a strength')),
                TagsInput::make('improvements')
                    ->label(__('Needs Improvement'))
                    ->placeholder(__('Press Enter to add an improvement')),
                Textarea::make('smart_parental_advice')
                    ->label(__('Smart Parental Advice'))
                    ->columnSpanFull(),
                Toggle::make('is_visible_to_parent')
                    ->label(__('Visible to Parent'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
