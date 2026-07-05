<?php

namespace App\Http\Requests\User\Auth;

use App\GenderEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
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
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z ]+$/'],
            'country_code' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'phone_number', 'unique:users,phone'],
            'email' => ['required', 'email', 'unique:users,email'],
            'date_of_birth' => ['required', 'date', 'before:'.today()->subYears(18)->format('Y-m-d')],
            'gender' => ['required', new Enum(GenderEnum::class)],
            'terms_and_conditions' => ['required', 'accepted'],
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
