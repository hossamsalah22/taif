<?php

namespace App\Filament\Resources\AssessmentSubmissions\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Specialist Report'))
                    ->schema([
                        Textarea::make('strengths')
                            ->label(__('Strengths'))
                            ->rows(3),
                        Textarea::make('improvements')
                            ->label(__('Areas for Improvement'))
                            ->rows(3),
                        Textarea::make('recommendations')
                            ->label(__('Specialist Recommendations'))
                            ->rows(3),
                        SpatieMediaLibraryFileUpload::make('report')
                            ->label(__('Clinical Report Document'))
                            ->collection('reports')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
