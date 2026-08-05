<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1 space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    {{ __('Assigned Plan Summary') }}
                </x-slot>

                <div class="space-y-4">
                    <div>
                        <span class="text-gray-500">{{ __('Plan Name') }}:</span>
                        <span class="font-medium">{{ $record->learningPlan->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('Child') }}:</span>
                        <span class="font-medium">{{ $record->child->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('Severity Alignment') }}:</span>
                        <x-filament::badge :color="\App\Enums\AutismLevelEnum::color($record->child->autism_level)">
                            {{ \App\Enums\AutismLevelEnum::label($record->child->autism_level) }}
                        </x-filament::badge>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <div class="md:col-span-2">
            <x-filament::section>
                <x-slot name="heading">
                    {{ __('Execution Map') }}
                </x-slot>

                <div class="space-y-6">
                    @foreach ($record->learningPlan->goals as $goal)
                        <div
                            class="relative p-5 rounded-xl shadow-sm transition hover:shadow-md {{ $this->getStatusColorClass($this->getGoalStatus($goal)) }}">
                            <div class="flex flex-wrap gap-6 justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-white/60 dark:bg-black/30">
                                        <x-filament::icon icon="heroicon-m-flag" class="w-6 h-6 opacity-80" />
                                    </div>
                                    <h3 class="font-bold text-xl">{{ $goal->name }}</h3>
                                </div>
                                <div class="shrink-0 ms-auto">
                                    {{ ($this->reportAction)(['type' => \App\Models\LearningGoal::class, 'id' => $goal->id]) }}
                                </div>
                            </div>

                            <div class="mt-5 ps-6 ms-4 space-y-5 border-s-2 border-gray-200 dark:border-gray-700">
                                @foreach ($goal->lessons as $lesson)
                                    <div
                                        class="relative p-4 rounded-lg transition hover:bg-black/5 dark:hover:bg-white/5 {{ $this->getStatusColorClass($this->getLessonStatus($lesson)) }}">
                                        <div
                                            class="absolute -start-6 top-6 w-6 border-t-2 border-gray-200 dark:border-gray-700">
                                        </div>
                                        <div class="flex flex-wrap gap-6 justify-between items-center mb-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-white/60 dark:bg-black/30">
                                                    <x-filament::icon icon="heroicon-m-book-open"
                                                        class="w-4 h-4 opacity-80" />
                                                </div>
                                                <h4 class="font-semibold text-lg">{{ $lesson->name }}</h4>
                                            </div>
                                            <div class="shrink-0 ms-auto">
                                                {{ ($this->reportAction)(['type' => \App\Models\LearningLesson::class, 'id' => $lesson->id]) }}
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                            @foreach ($lesson->exercises as $exercise)
                                                <div
                                                    class="p-3 rounded-md shadow-sm transition hover:scale-[1.01] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 {{ $this->getStatusColorClass($this->getExerciseStatus($exercise->id)) }}">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center bg-white/60 dark:bg-black/30">
                                                            <x-filament::icon icon="heroicon-m-puzzle-piece"
                                                                class="w-3.5 h-3.5 opacity-80" />
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <span
                                                                class="font-medium text-base">{{ \App\Enums\ExerciseTypeEnum::label($exercise->type) }}</span>
                                                            <span
                                                                class="text-xs opacity-75 mt-0.5">{{ \App\Enums\DifficultyLevel::label($exercise->difficulty_level) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="self-end sm:self-auto shrink-0 ms-auto">
                                                        {{ ($this->reportAction)(['type' => \App\Models\LearningExercise::class, 'id' => $exercise->id]) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        </div>
    </div>

    <x-filament-actions::modals />
</div>
