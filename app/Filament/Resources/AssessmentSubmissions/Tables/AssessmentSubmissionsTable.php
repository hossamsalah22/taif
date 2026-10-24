<?php

namespace App\Filament\Resources\AssessmentSubmissions\Tables;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Enums\AutismLevelEnum;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Services\PdfRenderer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssessmentSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label(__('Submission ID'))
                    ->prefix('SUB-ASM-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('child.name')
                    ->label(__('Child Profile'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assessment.autism_level')
                    ->label(__('Assigned Severity'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => AutismLevelEnum::label($state) ?? $state),
                TextColumn::make('performance_accuracy')
                    ->label(__('Performance Accuracy'))
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('attempt_number')
                    ->label(__('Attempt Number'))
                    ->prefix('#')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Submission Time'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('assessment_id')
                    ->label(__('Assessment'))
                    ->relationship('assessment', 'title')
                    ->getOptionLabelFromRecordUsing(fn (Assessment $record): string => $record->title)
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()
                    ->label(__('Evaluate'))
                    ->icon('heroicon-o-clipboard-document-check')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data, AssessmentSubmission $record): array {
                        $data['status'] = $record->statusAfterEvaluation();

                        return $data;
                    }),
                Action::make('publish')
                    ->label(__('Publish Report'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AssessmentSubmission $record) => $record->status !== AssessmentSubmissionStatusEnum::PUBLISHED)
                    ->action(function (AssessmentSubmission $record) {
                        $record->publish();
                        Notification::make()->title(__('Report Published Successfully'))->success()->send();
                    }),
                ActionGroup::make([
                    Action::make('export_pdf')
                        ->label(__('Export PDF'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (AssessmentSubmission $record) {
                            $pdf = PdfRenderer::fromView('assessments.report_pdf', ['record' => $record]);

                            return response()->streamDownload(fn () => print ($pdf), "report-{$record->id}.pdf");
                        }),
                    Action::make('request_re_evaluation')
                        ->label(__('Request Re-evaluation'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (AssessmentSubmission $record) => ! $record->child->force_re_test)
                        ->action(function (AssessmentSubmission $record) {
                            $record->child->update(['force_re_test' => true]);
                            Notification::make()->title(__('Re-evaluation token injected.'))->success()->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
