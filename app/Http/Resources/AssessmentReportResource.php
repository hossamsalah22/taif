<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'submission_id' => $this->id,
            'child_id' => $this->child_id,
            'assessment' => [
                'id' => $this->assessment->id,
                'title' => $this->assessment->title,
                'severity_level' => $this->assessment->severity_level,
            ],
            'status' => $this->status,
            'submitted_at' => $this->created_at,
            'report_details' => $this->answers->map(function ($answer) {
                return [
                    'question_id' => $answer->question->id,
                    'prompt' => $answer->question->prompt,
                    'exercise_type' => $answer->question->exercise_type,
                    'provided_answer' => $answer->answer_data,
                ];
            }),
        ];
    }
}
