<?php

namespace App\Http\Resources;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Enums\SpeechStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'gender' => $this->gender,
            'gender_label' => GenderEnum::label($this->gender),
            'autism_level' => $this->autism_level,
            'autism_level_label' => AutismLevelEnum::label($this->autism_level),
            'speech_status' => $this->speech_status,
            'speech_status_label' => SpeechStatusEnum::label($this->speech_status),
            'educational_status' => $this->educational_status,
            'number_of_assessment_submissions' => $this->assessmentSubmissions->count(),
            'submission_id' => $this->assessmentSubmissions->max('id'),
        ];
    }
}
