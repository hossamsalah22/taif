<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'phone_number:'.$this->input('country_code')],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (request()->input('phone')) {
            $this->merge([
                'phone' => prepare_phone(request()->input('phone'), request()->input('country_code') ?? 'SA'),
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.phone_number' => __('validation.phone_number', ['country_code' => __('validation.country_codes.'.($this->input('country_code') ?? 'SA'))]),
        ];
    }
}
