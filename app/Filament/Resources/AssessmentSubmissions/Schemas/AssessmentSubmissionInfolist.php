<?php

namespace App\Filament\Resources\AssessmentSubmissions\Schemas;

use App\Enums\ExerciseTypeEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Header Telemetry'))
                    ->schema([
                        TextEntry::make('child.user.name')
                            ->label(__('Child Profile'))
                            ->weight('bold'),
                        TextEntry::make('attempt_number')
                            ->label(__('Attempt Badge'))
                            ->prefix('#'),
                        TextEntry::make('created_at')
                            ->label(__('Submission Timestamp'))
                            ->dateTime(),
                    ])->columns(3),

                Section::make(__('Specialist Report'))
                    ->schema([
                        TextEntry::make('diagnosed_severity_level')
                            ->label(__('Diagnosed Severity Level'))
                            ->badge(),
                        TextEntry::make('strengths')
                            ->label(__('Strengths')),
                        TextEntry::make('improvements')
                            ->label(__('Areas for Improvement')),
                        TextEntry::make('recommendations')
                            ->label(__('Specialist Recommendations')),
                    ])->columns(2),

                Section::make(__('Assessment Answers'))
                    ->schema([
                        RepeatableEntry::make('answers')
                            ->label(__('Answers'))
                            ->schema([
                                TextEntry::make('question.prompt')
                                    ->label(__('Question'))
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('question.exercise_type')
                                    ->label(__('Exercise Type'))
                                    ->formatStateUsing(fn ($state) => ExerciseTypeEnum::label($state))
                                    ->badge(),

                                ViewEntry::make('answer_data')
                                    ->label(__('Answer'))
                                    ->view('assessments.assessment_answer'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
