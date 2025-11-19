<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstallmentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'other_discounts' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'other_discounts.numeric' => 'O valor de outros descontos deve ser um número.',
            'other_discounts.min' => 'O valor de outros descontos não pode ser negativo.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }
}

