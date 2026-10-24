<?php

namespace App\Filament\Resources\Children\Schemas;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Enums\SpeechStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Enum;

/**
 * Same limits as the parent app's ChildRequest, so a child edited here stays valid there.
 */
class ChildForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Basic Child Profile'))
                    ->schema([
                        Select::make('parent_id')
                            ->label(__('Linked Parent'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            // Moving a child to another family is not an edit; it would hand their history to someone else.
                            ->disabledOn('edit'),
                        TextInput::make('name')
                            ->label(__('Child Name'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(30),
                        TextInput::make('age')
                            ->label(__('Age'))
                            ->integer()
                            ->required()
                            ->minValue(2)
                            ->maxValue(18),
                        Select::make('gender')
                            ->label(__('Gender'))
                            ->options(GenderEnum::options())
                            ->rules(['required', new Enum(GenderEnum::class)])
                            ->required(),
                        Select::make('speech_status')
                            ->label(__('Speech Status'))
                            ->options(SpeechStatusEnum::options())
                            ->rules(['required', new Enum(SpeechStatusEnum::class)])
                            ->required(),
                        TextInput::make('educational_status')
                            ->label(__('Educational Status'))
                            ->maxLength(100),
                    ])
                    ->columns(2),
                Section::make(__('Screening Baseline (Assessment)'))
                    ->schema([
                        Select::make('autism_level')
                            ->label(__('Autism Level'))
                            ->options(AutismLevelEnum::options())
                            ->rules(['required', new Enum(AutismLevelEnum::class)])
                            ->required(),
                        Toggle::make('override_assessment_lock')
                            ->label(__('Allow one more assessment attempt'))
                            ->helperText(__('Lets the parent take the assessment again after using all attempts. Turns itself off once used.')),
                        Toggle::make('force_re_test')
                            ->label(__('Force Re-test')),
                    ])
                    ->columns(2),
            ]);
    }
}
