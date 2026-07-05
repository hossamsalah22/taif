<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('name'))
                    ->required()
                    ->rules(['min:5', 'max:50']),
                TextInput::make('email')
                    ->label(__('email'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->email(),
                TextInput::make('password')
                    ->label(__('password'))
                    ->required()
                    ->password()
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label(__('password_confirmation'))
                    ->required()
                    ->password(),
            ]);
    }
}
