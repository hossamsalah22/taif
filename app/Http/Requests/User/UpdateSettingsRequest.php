<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'locale' => 'sometimes|string|in:ar,en,ar-SA,en-US',
            'sensory' => 'sometimes|array',
            'sensory.child_id' => 'required_with:sensory|exists:children,id',
            'sensory.audio_volume' => 'required_with:sensory|integer|min:0|max:100',
            'sensory.screen_brightness' => 'required_with:sensory|integer|min:10|max:100',

            'notifications' => 'sometimes|array',
            'notifications.daily_session' => 'boolean',
            'notifications.progress_reports' => 'boolean',
            'notifications.child_achievements' => 'boolean',
            'notifications.plan_updates' => 'boolean',
        ];
    }
}
