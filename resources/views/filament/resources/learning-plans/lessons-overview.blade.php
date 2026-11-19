{{-- Read-only list of the lessons inside the goals picked in the plan's Goals tab. --}}
<div class="flex flex-col gap-3">
    @forelse ($goals as $goal)
        <div class="rounded-xl p-4 ring-1 ring-gray-200 dark:ring-white/10">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $goal->name }}</p>
                <x-filament::badge color="gray">
                    {{ trans_choice(':count lesson|:count lessons', $goal->lessons->count(), ['count' => $goal->lessons->count()]) }}
                </x-filament::badge>
            </div>

            @if ($goal->lessons->isNotEmpty())
                <ol class="mt-3 flex flex-wrap gap-2">
                    @foreach ($goal->lessons as $lesson)
                        <li class="rounded-lg bg-gray-50 px-3 py-1.5 text-sm text-gray-700 dark:bg-white/5 dark:text-gray-200">
                            {{ $loop->iteration }}. {{ $lesson->name }}
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('This goal has no lessons yet.') }}</p>
            @endif
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
            {{ __('Pick goals in the Goals tab to see their lessons here.') }}
        </div>
    @endforelse
</div>
