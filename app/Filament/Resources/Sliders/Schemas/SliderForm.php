<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
                Section::make(__('Images'))
                    ->icon('heroicon-o-photo')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('slider_en_large')
                            ->collection('slider_en_large')
                            ->label(__('English Large'))
                            ->helperText(__('Recommended size: 1920x1080')),
                        SpatieMediaLibraryFileUpload::make('slider_en_small')
                            ->collection('slider_en_small')
                            ->label(__('English Mobile'))
                            ->helperText(__('Recommended size: 1080x1920')),
                        SpatieMediaLibraryFileUpload::make('slider_ar_large')
                            ->collection('slider_ar_large')
                            ->label(__('Arabic Large'))
                            ->helperText(__('Recommended size: 1920x1080')),
                        SpatieMediaLibraryFileUpload::make('slider_ar_small')
                            ->collection('slider_ar_small')
                            ->label(__('Arabic Mobile'))
                            ->helperText(__('Recommended size: 1080x1920')),
                    ])->columns(2),
            ]);
    }
}
