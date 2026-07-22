<?php

namespace App\Filament\Resources\Children\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChildInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Basic Child Profile'))
                    ->schema([
                        TextEntry::make('id')->label(__('Child ID')),
                        TextEntry::make('name')->label(__('Child Name')),
                        TextEntry::make('age')->label(__('Age')),
                        TextEntry::make('gender')->label(__('Gender'))
                            ->formatStateUsing(fn ($state) => GenderEnum::label($state))
                            ->color(fn ($state) => GenderEnum::color($state))
                            ->badge(),
                        TextEntry::make('parent.name')->label(__('Linked Parent')),
                        TextEntry::make('created_at')->label(__('Profile Created'))->dateTime(),
                    ])->columns(3),

                Section::make(__('Screening Baseline (Assessment)'))
                    ->schema([
                        TextEntry::make('autism_level')
                            ->label(__('Autism Level'))
                            ->formatStateUsing(fn ($state) => AutismLevelEnum::label($state))
                            ->color(fn ($state) => AutismLevelEnum::color($state))
                            ->badge(),
                        TextEntry::make('latest_assessment_date')
                            ->label(__('Assessment Date'))
                            ->default('-'),
                        TextEntry::make('latest_assessment_score')
                            ->label(__('Score'))
                            ->default('-'),
                        TextEntry::make('latest_assessment_report')
                            ->label(__('Report Link'))
                            ->default('-'),
                    ])->columns(4),

                Section::make(__('Active Learning Plan Summary'))
                    ->schema([
                        TextEntry::make('active_plan_package')
                            ->label(__('Package Name'))
                            ->default(__('-')),
                        TextEntry::make('active_plan_status')
                            ->label(__('Status'))
                            ->default('-'),
                        TextEntry::make('active_plan_sessions')
                            ->label(__('Sessions Overview'))
                            ->default('-'),
                        TextEntry::make('active_plan_duration')
                            ->label(__('Duration'))
                            ->default('-'),
                    ])
                    ->headerActions([
                        Action::make('assign_global_plan')
                            ->label(__('Assign Global Plan'))
                            ->color('primary')
                            ->icon('heroicon-o-document-plus')
                            ->action(function ($record) {
                                // Logic to assign global plan
                            }),
                        Action::make('archive_plan')
                            ->label(__('Archive Plan'))
                            ->color('danger')
                            ->icon('heroicon-o-archive-box')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                // Logic to archive plan
                            }),
                    ])
                    ->columns(4),
            ]);
    }
}
