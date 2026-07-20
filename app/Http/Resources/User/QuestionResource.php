<?php

namespace App\Http\Resources\User;

use App\Enums\ExerciseTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            'prompt' => $this->prompt,
            'exercise_type' => ExerciseTypeEnum::label($this->exercise_type),
            'video_url' => $this->video_url,
            'audio' => $this->audio,
            'image' => $this->image,
            'distractors' => $this->distractors,
            'shared_elements' => $this->shared_elements,
            'options' => QuestionOptionResource::collection($this->options),
            'payload' => $this->payload,
            'order' => $this->order,
        ];
    }
}
