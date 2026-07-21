@php
    use App\Enums\ExerciseTypeEnum;
    $answer = is_array($getState()) ? $getState() : json_decode($getState(), true);

    $question = $getRecord()->question;
    $type = $question->exercise_type;
@endphp

@if (in_array($type, [
        ExerciseTypeEnum::IMAGE_SELECTION,
        ExerciseTypeEnum::DISTINGUISHING,
        ExerciseTypeEnum::AUDIO_FLASHCARDS,
    ]))
    @php
        $options = $question->options->keyBy('id');
    @endphp

    @foreach ((array) $answer as $optionId)
        @php
            $option = $options[$optionId] ?? null;
        @endphp

        @if ($option)
            <div class="mb-4 rounded-lg border p-3">
                @if ($option->title)
                    <div class="font-bold">
                        {{ is_array($option->title) ? $option->title[app()->getLocale()] ?? '' : $option->title }}
                    </div>
                @endif

                @if ($image = $option->getFirstMediaUrl('image'))
                    <img src="{{ $image }}"
                        style="max-height: 250px; max-width: 100%; object-fit: contain; margin-top: 1rem; border-radius: 0.5rem;">
                @endif

                @if ($audio = $option->getFirstMediaUrl('audio'))
                    <audio controls class="mt-2 w-full">
                        <source src="{{ $audio }}">
                    </audio>
                @endif
            </div>
        @endif
    @endforeach
@elseif($type === ExerciseTypeEnum::MATCHING)
    @php
        $pairs = $question->matchingPairs->keyBy('id');
    @endphp

    @foreach ($answer as $pair)
        @php
            $left = $pairs[$pair['left_option_id']] ?? null;
            $right = $pairs[$pair['right_option_id']] ?? null;
        @endphp

        <div class="flex items-center gap-16 mb-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">

            @if ($left)
                <img src="{{ $left->getFirstMediaUrl('left_element') }}"
                    style="max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 0.5rem;">
            @endif

            <span class="text-xl">→</span>

            @if ($right)
                <img src="{{ $right->getFirstMediaUrl('right_element') }}"
                    style="max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 0.5rem;">
            @endif

        </div>
    @endforeach
@elseif($type === ExerciseTypeEnum::ORDERING)
    @php
        $steps = $question->orderingSteps->keyBy('id');
    @endphp

    <div class="flex flex-wrap gap-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">

        @foreach ($answer as $stepId)
            @php
                $step = $steps[$stepId] ?? null;
            @endphp

            @if ($step)
                <div class="text-center">

                    <img src="{{ $step->getFirstMediaUrl('image') }}"
                        style="max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 0.5rem;">

                    <div
                        class="mt-4 font-bold text-lg bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 rounded-full w-8 h-8 flex items-center justify-center mx-auto">
                        {{ $loop->iteration }}
                    </div>

                </div>
            @endif
        @endforeach

    </div>
@elseif($type === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO)
    @if ($question->video_url)
        <div class="mb-4">
            <a href="{{ $question->video_url }}" target="_blank"
                class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-500 font-medium">
                <span dir="ltr">{{ $question->video_url }}</span>
            </a>
        </div>
    @endif
    <div class="inline-flex items-center gap-2 px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">
        {{ __(ucwords($record->answer_data)) }}
    </div>
@else
    <pre>{{ json_encode($answer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

@endif
