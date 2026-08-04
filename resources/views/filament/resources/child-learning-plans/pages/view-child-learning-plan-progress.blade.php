<x-filament-panels::page>
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

                <div class="space-y-4">
                    @foreach ($record->learningPlan->goals as $goal)
                        <div
                            class="p-4 border rounded-lg {{ $this->getStatusColorClass($this->getGoalStatus($goal)) }}">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-semibold text-lg">{{ $goal->name }}</h3>
                            </div>

                            <div class="pl-4 space-y-4 border-l-2 border-gray-200 dark:border-gray-700">
                                @foreach ($goal->lessons as $lesson)
                                    <div
                                        class="p-3 border rounded {{ $this->getStatusColorClass($this->getLessonStatus($lesson)) }}">
                                        <div class="flex justify-between items-center mb-2">
                                            <h4 class="font-medium">{{ $lesson->name }}</h4>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                                            @foreach ($lesson->exercises as $exercise)
                                                <div
                                                    class="p-2 border rounded text-sm flex justify-between items-center {{ $this->getStatusColorClass($this->getExerciseStatus($exercise->id)) }}">
                                                    <span>{{ $exercise->type->value }}
                                                        ({{ $exercise->difficulty_level->value }})</span>
                                                    {{ ($this->reportAction)(['type' => 'exercise', 'id' => $exercise->id]) }}
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
</x-filament-panels::page>
