<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('question')
                    ->label(__('question'))
                    ->required()
                    ->translatableTabs(),
                Textarea::make('answer')
                    ->label(__('answer'))
                    ->required()
                    ->translatableTabs(),
                Toggle::make('is_active')
                    ->visible(fn () => auth('web')->user()->can('activate_any_faq'))
                    ->label(__('is_active'))
                    ->default(true),

            ]);
    }
}
