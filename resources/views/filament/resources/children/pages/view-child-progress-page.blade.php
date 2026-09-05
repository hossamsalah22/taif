<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <span>{{ __('Learning Plan Progress') }}: {{ $plan ? $plan->title : __('No Active Plan') }}</span>
                @if ($plan)
                    {{ ($this->manageReportAction)(['type' => \App\Models\LearningPlan::class, 'id' => $plan->id]) }}
                @endif
            </div>
        </x-slot>

        @if (!$plan)
            <div class="text-gray-500">{{ __('No active learning plan found for this child.') }}</div>
        @else
            <div class="flex flex-col gap-6">
                @foreach ($plan->goals as $goal)
                    @php
                        $goalBadgeColor = $goal->is_completed ? 'success' : ($goal->is_in_progress ? 'warning' : 'gray');
                    @endphp
                    
                    <x-filament::section compact style="margin-bottom: 1rem; border: 1px solid rgba(128,128,128,0.2);">
                        <x-slot name="heading">
                            <div class="flex items-center gap-3">
                                <span>{{ $goal->title ?? __('Goal') . ' ' . $goal->id }}</span>
                                <x-filament::badge :color="$goalBadgeColor">{{ $goal->is_completed ? __('Mastered') : ($goal->is_in_progress ? __('In Progress') : __('Locked')) }}</x-filament::badge>
                            </div>
                        </x-slot>

                        @if ($goal->lessons && $goal->lessons->count() > 0)
                            <div class="flex flex-col gap-4">
                                @foreach ($goal->lessons as $lesson)
                                    @php
                                        $lessonBadgeColor = $lesson->is_completed ? 'success' : ($lesson->is_in_progress ? 'warning' : 'gray');
                                    @endphp
                                    <x-filament::section compact style="background: rgba(128,128,128,0.02); border: 1px solid rgba(128,128,128,0.1);">
                                        <x-slot name="heading">
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm font-medium">{{ $lesson->title ?? __('Lesson') . ' ' . $lesson->id }}</span>
                                                <x-filament::badge :color="$lessonBadgeColor" size="sm">{{ $lesson->is_completed ? __('Mastered') : ($lesson->is_in_progress ? __('In Progress') : __('Locked')) }}</x-filament::badge>
                                            </div>
                                        </x-slot>

                                        @if ($lesson->exercises && $lesson->exercises->count() > 0)
                                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                                @foreach ($lesson->exercises as $exercise)
                                                    @php
                                                        $exerciseBadgeColor = $exercise->is_completed ? 'success' : ($exercise->is_in_progress ? 'warning' : 'gray');
                                                        $exerciseTypeLabel = \App\Enums\ExerciseTypeEnum::label($exercise->type);
                                                        $exerciseTypeColor = \App\Enums\ExerciseTypeEnum::color($exercise->type);
                                                    @endphp
                                                    <div style="border: 1px solid rgba(128,128,128,0.2); border-radius: 0.5rem; padding: 1rem; display: flex; flex-direction: column; gap: 1rem; background-color: rgba(128,128,128,0.05);">
                                                        <div class="flex justify-between items-start">
                                                            <div class="flex flex-col gap-2">
                                                                <strong class="text-sm">{{ $exercise->title ?? __('Exercise') . ' ' . $exercise->id }}</strong>
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <x-filament::badge :color="$exerciseTypeColor" size="xs">{{ $exerciseTypeLabel }}</x-filament::badge>
                                                                    <x-filament::badge :color="$exerciseBadgeColor" size="xs">{{ $exercise->is_completed ? __('Completed') : ($exercise->is_in_progress ? __('Started') : __('Pending')) }}</x-filament::badge>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-wrap items-center gap-2 mt-auto">
                                                            {{ ($this->exportLogsAction)(['id' => $exercise->id]) }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </x-filament::section>
                                @endforeach
                            </div>
                        @endif
                    </x-filament::section>
                @endforeach
            </div>
        @endif
    </x-filament::section>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
