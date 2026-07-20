<?php

namespace App\Http\Requests\User;

use App\Enums\AutismLevelEnum;
use App\Enums\GenderEnum;
use App\Enums\SpeechStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ChildRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:30'],
            'age' => ['required', 'integer', 'min:2', 'max:18'],
            'gender' => ['required', 'string', new Enum(GenderEnum::class)],
            'autism_level' => ['required', 'string', new Enum(AutismLevelEnum::class)],
            'speech_status' => ['required', 'string', new Enum(SpeechStatusEnum::class)],
            'educational_status' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return array_merge(parent::validated(), [
            'parent_id' => auth('user')->id(),
        ]);
    }
}
