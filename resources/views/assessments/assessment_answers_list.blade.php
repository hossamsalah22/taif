<div>
    @if($getRecord() && $getRecord()->answers)
        @foreach($getRecord()->answers as $answer)
            <div class="mb-6 p-4 border rounded-lg">
                <h3 class="font-bold text-lg mb-2">
                    {{ __('Question') }} {{ $loop->iteration }}: {{ $answer->question->prompt ?? '' }}
                </h3>
                <div class="mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ \App\Enums\ExerciseTypeEnum::label($answer->question->exercise_type) ?? '' }}
                    </span>
                </div>
                
                <div class="mt-4">
                    @include('assessments.assessment_answer', [
                        'getRecord' => function() use ($answer) { return $answer; },
                        'getState' => function() use ($answer) { return $answer->answer_data; }
                    ])
                </div>
            </div>
        @endforeach
    @endif
</div>
