<?php

namespace App\Filament\Resources\ClinicalProgressReports\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ClinicalProgressReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('child_id')
                    ->relationship('child', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('Child'))
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('learning_plan_id', null))
                    ->required(),
                Select::make('learning_plan_id')
                    ->relationship('plan', 'name', function (Builder $query, Get $get) {
                        $childId = $get('child_id');
                        if ($childId) {
                            $query->whereHas('childLearningPlans', function ($q) use ($childId) {
                                $q->where('child_id', $childId);
                            });
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get) => ! $get('child_id'))
                    ->label(__('Plan'))
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
                    ->label(__('Report Title (e.g. Week 1 Report)'))
                    ->translatableTabs()
                    ->columnSpanFull(),
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
                            ->image()
                            ->required(),
                    ])
                    ->columns(2)
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
                            ->image()
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Textarea::make('smart_parental_advice')
                    ->label(__('Smart Parental Advice'))
                    ->translatableTabs()
                    ->columnSpanFull(),
                Toggle::make('is_visible_to_parent')
                    ->label(__('Visible to Parent'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
