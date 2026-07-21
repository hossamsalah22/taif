<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionOptionResource extends JsonResource
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
            'is_correct' => $this->is_correct,
            'audio' => $this->audio,
            'image' => $this->image,
            'left_element' => $this->left_element,
            'right_element' => $this->right_element,
            'order' => $this->order,
        ];
    }
}
