<?php

namespace App\Http\Requests\User;

use App\Enums\SupportTicketStatusEnum;
use App\Models\SupportTicket;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSupportTicketRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:5', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }

    public function validated($key = null, $default = null)
    {

        $referenceNumber = 'TYF-TK-'.strtoupper(Str::random(5));

        // Ensure uniqueness just in case
        while (SupportTicket::where('reference_number', $referenceNumber)->exists()) {
            $referenceNumber = 'TYF-TK-'.strtoupper(Str::random(5));
        }

        return array_merge(parent::validated(), [
            'reference_number' => $referenceNumber,
            'user_id' => $this->user()->id,
            'status' => SupportTicketStatusEnum::OPEN,
        ]);

    }
}
