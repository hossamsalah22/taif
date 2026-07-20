<?php

namespace App\Http\Resources\User;

use App\Enums\AssessmentStatusEnum;
use App\Enums\AutismLevelEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
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
            'title' => $this->title,
            'autism_level' => AutismLevelEnum::label($this->autism_level),
            'status' => AssessmentStatusEnum::label($this->status),
            'version' => $this->version,
            'questions' => QuestionResource::collection($this->questions),
        ];
    }
}
