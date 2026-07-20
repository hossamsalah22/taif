<?php

namespace App\Http\Requests\User\Auth;

use App\Enums\DeviceTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ConfirmOtpRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required'],
            'otp' => ['required', 'integer'],
            'verification_id' => ['required', 'exists:user_verifications,id'],
            'device_token' => ['nullable'],
            'device_type' => ['nullable', new Enum(DeviceTypeEnum::class)],
        ];
    }
}
