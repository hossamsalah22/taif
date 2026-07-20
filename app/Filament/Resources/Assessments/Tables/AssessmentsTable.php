<?php

namespace App\Filament\Resources\Assessments\Tables;

use App\Enums\AssessmentStatusEnum;
use App\Enums\AutismLevelEnum;
use App\Models\Assessment;
use App\Models\Question;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssessmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->latest())
            ->columns([
                TextColumn::make('id')
                    ->label(__('Template ID'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(),
                TextColumn::make('autism_level')
                    ->label(__('Target Severity Level'))
                    ->badge()
                    ->color(fn ($state) => AutismLevelEnum::color($state))
                    ->formatStateUsing(fn ($state) => AutismLevelEnum::label($state))
                    ->sortable(),
                TextColumn::make('version')
                    ->label(__('Version'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => AssessmentStatusEnum::color($state))
                    ->formatStateUsing(fn ($state) => AssessmentStatusEnum::label($state))
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label(__('Total Questions'))
                    ->counts('questions')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Creation Date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                Action::make('create_new_version')
                    ->label(__('Edit Template'))
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->visible(fn (Assessment $record) => $record->status === AssessmentStatusEnum::ACTIVE)
                    ->requiresConfirmation()
                    ->modalHeading(__('Create New Version'))
                    ->modalDescription(__('This will create a new draft version of this assessment. The current active version will remain active until you publish the new one.'))
                    ->action(function (Assessment $record) {
                        $newAssessment = $record->replicate(['status', 'version', 'questions_count']);
                        $newAssessment->status = AssessmentStatusEnum::DRAFT->value;
                        $newAssessment->version = $record->version + 1;
                        $newAssessment->save();

                        // Clone questions
                        $record->questions->each(function (Question $question) use ($newAssessment) {
                            $newQuestion = $question->replicate(['assessment_id']);
                            $newQuestion->assessment_id = $newAssessment->id;
                            $newQuestion->save();

                            // Duplicate media (assuming we want to keep references or actually duplicate? Spatie Media Library allows copying)
                            $question->getMedia('question_audio')->each(function ($media) use ($newQuestion) {
                                $media->copy($newQuestion, 'question_audio');
                            });
                            $question->getMedia('question_image')->each(function ($media) use ($newQuestion) {
                                $media->copy($newQuestion, 'question_image');
                            });
                        });

                        return redirect(request()->header('referer').'/'.$newAssessment->id.'/edit');
                    }),
                Action::make('edit_draft')
                    ->label(__('Continue Editing'))
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Assessment $record): string => request()->header('referer').'/'.$record->id.'/edit')
                    ->visible(fn (Assessment $record) => $record->status === AssessmentStatusEnum::DRAFT),
            ])
            ->toolbarActions([
                // No bulk actions to enforce read-only on master records
            ]);
    }
}
