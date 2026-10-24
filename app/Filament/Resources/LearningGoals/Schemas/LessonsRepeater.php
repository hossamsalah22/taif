<?php

namespace App\Filament\Resources\LearningGoals\Schemas;

use App\Enums\RewardTargetEnum;
use App\Filament\Resources\LearningLessons\LearningLessonResource;
use App\Models\LearningLesson;
use App\Models\Reward;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

/**
 * Lessons editor of the learning goal form. Exercises live inside each lesson and are edited from the lesson form.
 */
class LessonsRepeater
{
    public static function make(): Repeater
    {
        return Repeater::make('lessons')
            ->hiddenLabel()
            ->relationship('lessons')
            ->orderColumn('display_priority')
            ->reorderableWithButtons()
            ->schema([
                TextInput::make('name')
                    ->label(__('Lesson Name'))
                    ->required()
                    ->translatableTabs(),
                Grid::make(2)->schema([
                    Select::make('reward_id')
                        ->label(__('Reward'))
                        ->options(fn () => Reward::query()->where('target_type', RewardTargetEnum::LESSON->value)->get()->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder(__('No reward'))
                        ->helperText(__('Given to the child once every exercise in this lesson is completed.')),
                    Toggle::make('is_locked')
                        ->label(__('Locked'))
                        ->helperText(__('Locked lessons open only after the previous lesson is completed.'))
                        ->default(true)
                        ->inline(false),
                ]),
            ])
            ->columnSpanFull()
            ->addActionLabel(__('Add lesson'))
            ->deleteAction(fn (Action $action): Action => $action->requiresConfirmation())
            ->extraItemActions([
                Action::make('manageExercises')
                    ->label(__('Manage exercises'))
                    ->tooltip(__('Manage exercises'))
                    ->icon(Heroicon::OutlinedPuzzlePiece)
                    ->url(fn (array $arguments, Repeater $component): ?string => ($lesson = self::lessonOf($arguments['item'], $component))
                        ? LearningLessonResource::getUrl('edit', ['record' => $lesson])
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (array $arguments, Repeater $component): bool => self::lessonOf($arguments['item'], $component) !== null),
            ])
            ->collapsible()
            ->itemLabel(function (array $state, Repeater $component, string $key): string {
                $name = $state['name'][app()->getLocale()] ?? null;
                $lesson = self::lessonOf($key, $component);

                if (! $lesson) {
                    return $name ?: __('New lesson');
                }

                return ($name ?: __('Lesson')).' · '.trans_choice(':count exercise|:count exercises', $lesson->exercises_count, ['count' => $lesson->exercises_count]);
            });
    }

    /**
     * The saved lesson behind a repeater item, or null for an item that has not been saved yet.
     */
    private static function lessonOf(?string $itemKey, Repeater $component): ?LearningLesson
    {
        $lesson = $itemKey ? $component->getCachedExistingRecords()->get($itemKey) : null;

        if ($lesson && ! isset($lesson->exercises_count)) {
            $lesson->loadCount('exercises');
        }

        return $lesson;
    }
}
