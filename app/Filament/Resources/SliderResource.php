<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Forms;
use Filament\Schemas\Schema;
use App\Filament\Resources\MainResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

class SliderResource extends MainResource
{
    protected static ?string $model = Slider::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('__("Active")')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('__("Sort Order")')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Section::make('Images')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('slider_en_large')
                            ->collection('slider_en_large')
                            ->label('__("English Large")')
                            ->helperText('__("Recommended size: 1920x1080")'),
                        SpatieMediaLibraryFileUpload::make('slider_en_small')
                            ->collection('slider_en_small')
                            ->label('__("English Mobile")')
                            ->helperText('__("Recommended size: 1080x1920")'),
                        SpatieMediaLibraryFileUpload::make('slider_ar_large')
                            ->collection('slider_ar_large')
                            ->label('__("Arabic Large")')
                            ->helperText('__("Recommended size: 1920x1080")'),
                        SpatieMediaLibraryFileUpload::make('slider_ar_small')
                            ->collection('slider_ar_small')
                            ->label('__("Arabic Mobile")')
                            ->helperText('__("Recommended size: 1080x1920")'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('__("Active")')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('__("Sort Order")')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}



