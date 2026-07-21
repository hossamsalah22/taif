<?php

namespace App\Http\Requests;

use App\Models\Assessment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assessment_id' => ['required', 'exists:assessments,id'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.answer_data' => ['required'], // can be string, array, etc depending on exercise type
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $assessmentId = $this->input('assessment_id');
            $answers = $this->input('answers', []);

            if ($assessmentId && is_array($answers)) {
                $assessment = Assessment::find($assessmentId);

                if ($assessment) {
                    $assessmentQuestionIds = $assessment->questions()->pluck('id');
                    $submittedQuestionIds = collect($answers)->pluck('question_id');

                    if ($assessmentQuestionIds->diff($submittedQuestionIds)->isNotEmpty()) {
                        $validator->errors()->add('answers', __('All questions must be answered before submission.'));
                    }
                }
            }
        });
    }
}
