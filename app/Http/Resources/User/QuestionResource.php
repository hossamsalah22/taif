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
        $type = $this->exercise_type;

        return [
            'id' => $this->id,
            'prompt' => $this->prompt,
            'exercise_type' => ExerciseTypeEnum::label($type),
            'order' => $this->order,
            'payload' => $this->payload,

            $this->mergeWhen($type === ExerciseTypeEnum::DISTINGUISHING, [
                'image' => $this->image,
            ]),

            $this->mergeWhen($type === ExerciseTypeEnum::INSTRUCTIONAL_VIDEO, [
                'video_url' => $this->video_url,
            ]),

            $this->mergeWhen(in_array($type, [ExerciseTypeEnum::MATCHING, ExerciseTypeEnum::ORDERING, ExerciseTypeEnum::AUDIO_FLASHCARDS, ExerciseTypeEnum::IMAGE_SELECTION, ExerciseTypeEnum::DISTINGUISHING]), [
                'options' => QuestionOptionResource::collection($this->options),
            ]),
        ];
    }
}
