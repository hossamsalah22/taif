<?php

namespace App\Filament\Pages;

use App\Settings\PagesSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManagePages extends SettingsPage
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = PagesSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'system_settings';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('manage_pages');
    }

    public function getTitle(): string
    {
        return __('manage_pages');
    }

    public function getHeading(): string
    {
        return __('manage_pages');
    }

    public function getSubheading(): ?string
    {
        return __('pages_settings_description');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('system_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('website_pages'))
                    ->description(__('website_pages_description'))
                    ->icon('heroicon-o-document-text')
                    ->schema([

                        Tabs::make('pages_tabs')
                            ->tabs([
                                Tab::make(__('ar'))
                                    ->icon('heroicon-o-language')
                                    ->schema([
                                        MarkdownEditor::make('about_us_ar')
                                            ->label(__('about_us_ar'))
                                            ->rule('required')
                                            ->placeholder(__('about_us_placeholder'))
                                            ->helperText(__('about_us_help'))
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'heading',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),

                                        MarkdownEditor::make('privacy_policy_ar')
                                            ->label(__('privacy_policy_ar'))
                                            ->rule('required')
                                            ->placeholder(__('privacy_policy_placeholder'))
                                            ->helperText(__('privacy_policy_help'))
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'heading',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),

                                        MarkdownEditor::make('terms_and_conditions_ar')
                                            ->label(__('terms_and_conditions_ar'))
                                            ->rule('required')
                                            ->placeholder(__('terms_conditions_placeholder'))
                                            ->helperText(__('terms_conditions_help'))
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'heading',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),
                                    ])
                                    ->columns(1),

                                Tab::make(__('en'))
                                    ->icon('heroicon-o-language')
                                    ->schema([
                                        MarkdownEditor::make('about_us_en')
                                            ->label(__('about_us_en'))
                                            ->rule('required')
                                            ->placeholder(__('about_us_placeholder'))
                                            ->helperText(__('about_us_help'))
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'heading',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),

                                        MarkdownEditor::make('privacy_policy_en')
                                            ->label(__('privacy_policy_en'))
                                            ->rule('required')
                                            ->placeholder(__('privacy_policy_placeholder'))
                                            ->helperText(__('privacy_policy_help'))
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'heading',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),

                                        MarkdownEditor::make('terms_and_conditions_en')
                                            ->label(__('terms_and_conditions_en'))
                                            ->rule('required')
                                            ->placeholder(__('terms_conditions_placeholder'))
                                            ->helperText(__('terms_conditions_help'))
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'heading',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                Section::make(__('about_app'))
                    ->description(__('about_app_description'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('about_app_header_ar')
                                ->label(__('about_app_header_ar'))
                                ->required(),
                            TextInput::make('about_app_header_en')
                                ->label(__('about_app_header_en'))
                                ->required(),
                            Textarea::make('about_app_sub_header_ar')
                                ->label(__('about_app_sub_header_ar'))
                                ->required(),
                            Textarea::make('about_app_sub_header_en')
                                ->label(__('about_app_sub_header_en'))
                                ->required(),
                        ]),
                        Repeater::make('app_features')
                            ->label(__('app_features'))
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('title_ar')
                                        ->label(__('title_ar'))
                                        ->required(),
                                    TextInput::make('title_en')
                                        ->label(__('title_en'))
                                        ->required(),
                                    Textarea::make('description_ar')
                                        ->label(__('description_ar'))
                                        ->required(),
                                    Textarea::make('description_en')
                                        ->label(__('description_en'))
                                        ->required(),
                                ]),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? null),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),

                Section::make(__('help_center'))
                    ->description(__('help_center_description'))
                    ->icon('heroicon-o-question-mark-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('help_center_slogan_ar')
                                ->label(__('help_center_slogan_ar'))
                                ->required(),
                            TextInput::make('help_center_slogan_en')
                                ->label(__('help_center_slogan_en'))
                                ->required(),
                        ]),
                        Repeater::make('privacy_pillars')
                            ->label(__('privacy_pillars'))
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('title_ar')
                                        ->label(__('title_ar'))
                                        ->required(),
                                    TextInput::make('title_en')
                                        ->label(__('title_en'))
                                        ->required(),
                                    Textarea::make('body_ar')
                                        ->label(__('body_ar'))
                                        ->required(),
                                    Textarea::make('body_en')
                                        ->label(__('body_en'))
                                        ->required(),
                                ]),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? null),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columns(1),
            ])
            ->columns(1);
    }
}
