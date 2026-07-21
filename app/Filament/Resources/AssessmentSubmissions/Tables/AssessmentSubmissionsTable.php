<?php

namespace App\Filament\Resources\AssessmentSubmissions\Tables;

use App\Models\AssessmentSubmission;
use App\Notifications\ReportPublishedNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;

class AssessmentSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
