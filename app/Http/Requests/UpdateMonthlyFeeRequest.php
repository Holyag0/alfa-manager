<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMonthlyFeeRequest extends FormRequest
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
            'has_punctuality_discount' => 'nullable|boolean',
            'punctuality_discount_amount' => 'nullable|numeric|min:0',
            'due_day' => 'nullable|integer|min:1|max:31',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'punctuality_discount_amount.numeric' => 'O valor do desconto de pontualidade deve ser um número.',
            'punctuality_discount_amount.min' => 'O valor do desconto de pontualidade não pode ser negativo.',
            'due_day.integer' => 'O dia de vencimento deve ser um número inteiro.',
            'due_day.min' => 'O dia de vencimento deve ser no mínimo 1.',
            'due_day.max' => 'O dia de vencimento deve ser no máximo 31.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }
}

