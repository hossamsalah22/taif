<?php

namespace App\Filament\Resources\AssessmentSubmissions\Tables;

use App\Enums\AutismLevelEnum;
use App\Models\AssessmentSubmission;
use App\Notifications\ReportPublishedNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Browsershot\Browsershot;

class AssessmentSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                TextColumn::make('diagnosed_severity_level')
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
                TextColumn::make('created_at')
                    ->label(__('Submission Time'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()
                    ->label(__('Evaluate'))
                    ->icon('heroicon-o-clipboard-document-check')
                    ->slideOver(),
                Action::make('export_pdf')
                    ->label(__('Export PDF'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (AssessmentSubmission $record) {
                        $pdf = Browsershot::html("<h1>Report for {$record->id}</h1>")->pdf();

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
                Action::make('publish')
                    ->label(__('Publish Report'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AssessmentSubmission $record) => $record->status !== 'published')
                    ->action(function (AssessmentSubmission $record) {
                        $record->update([
                            'status' => 'published',
                            'report_published_at' => now(),
                        ]);
                        $record->child->parent->notify(new ReportPublishedNotification($record));
                        Notification::make()->title(__('Report Published Successfully'))->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
