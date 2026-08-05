<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    public static function isSimple(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('Basic Information'))
                    ->icon('heroicon-o-user-circle')
                    ->description(__('Update your personal information and security settings.'))
                    ->columns(2)
                    ->schema([
                        Group::make([
                            SpatieMediaLibraryFileUpload::make('avatar')
                                ->label(__('Avatar'))
                                ->collection('avatar')
                                ->hiddenLabel()
                                ->avatar()
                                ->imageEditor()
                                ->circleCropper()
                                ->imagePreviewHeight('250')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/heic', 'image/svg+xml'])
                                ->extraAttributes(['class' => '[&_div]:!mx-0 [&_div]:!justify-start']),
                        ])
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label(__('Name'))
                            ->placeholder(__('Enter your full name'))
                            ->prefixIcon('heroicon-o-user')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->placeholder('user@example.com')
                            ->prefixIcon('heroicon-o-envelope')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->label(__('Password'))
                            ->placeholder('••••••••')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->password()
                            ->revealable()
                            ->confirmed()
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->live(debounce: 500),

                        TextInput::make('password_confirmation')
                            ->label(__('Password Confirmation'))
                            ->placeholder('••••••••')
                            ->prefixIcon('heroicon-o-check-badge')
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => filled($get('password')))
                            // ->visible(fn ($get) => filled($get('password')))
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
