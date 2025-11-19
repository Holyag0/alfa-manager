<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMonthlyFeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajuste conforme sua lógica de autorização
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'enrollment_id' => 'required|exists:enrollments,id',
            'classroom_service_id' => 'nullable|exists:classroom_services,id',
            'academic_year' => [
                'required',
                'string',
                'size:4',
                'regex:/^\d{4}$/',
                function ($attribute, $value, $fail) {
                    $year = (int) $value;
                    $currentYear = (int) date('Y');
                    if ($year < 2000 || $year > ($currentYear + 5)) {
                        $fail('O ano letivo deve estar entre 2000 e ' . ($currentYear + 5) . '.');
                    }
                },
            ],
            'total_installments' => 'nullable|integer|min:1|max:12',
            'start_month' => 'nullable|integer|min:1|max:12',
            'end_month' => 'nullable|integer|min:1|max:12|gte:start_month',
            'has_sibling_discount' => 'nullable|boolean',
            'sibling_discount_amount' => 'nullable|numeric|min:0',
            'has_punctuality_discount' => 'nullable|boolean',
            'punctuality_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
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
            'enrollment_id.required' => 'A matrícula é obrigatória.',
            'enrollment_id.exists' => 'A matrícula selecionada não existe.',
            'classroom_service_id.exists' => 'O serviço de turma selecionado não existe.',
            'academic_year.required' => 'O ano letivo é obrigatório.',
            'academic_year.size' => 'O ano letivo deve ter 4 dígitos.',
            'academic_year.regex' => 'O ano letivo deve ser um ano válido.',
            'total_installments.integer' => 'O número de parcelas deve ser um número inteiro.',
            'total_installments.min' => 'Deve haver pelo menos 1 parcela.',
            'total_installments.max' => 'O número máximo de parcelas é 12.',
            'start_month.integer' => 'O mês de início deve ser um número inteiro.',
            'start_month.min' => 'O mês de início deve ser no mínimo 1 (Janeiro).',
            'start_month.max' => 'O mês de início deve ser no máximo 12 (Dezembro).',
            'end_month.integer' => 'O mês de término deve ser um número inteiro.',
            'end_month.min' => 'O mês de término deve ser no mínimo 1 (Janeiro).',
            'end_month.max' => 'O mês de término deve ser no máximo 12 (Dezembro).',
            'end_month.gte' => 'O mês de término deve ser posterior ou igual ao mês de início.',
            'sibling_discount_amount.numeric' => 'O valor do desconto de irmão deve ser um número.',
            'sibling_discount_amount.min' => 'O valor do desconto de irmão não pode ser negativo.',
            'punctuality_discount_amount.numeric' => 'O valor do desconto de pontualidade deve ser um número.',
            'punctuality_discount_amount.min' => 'O valor do desconto de pontualidade não pode ser negativo.',
            'start_date.date' => 'A data de início deve ser uma data válida.',
            'end_date.date' => 'A data de fim deve ser uma data válida.',
            'end_date.after' => 'A data de fim deve ser posterior à data de início.',
            'due_day.integer' => 'O dia de vencimento deve ser um número inteiro.',
            'due_day.min' => 'O dia de vencimento deve ser no mínimo 1.',
            'due_day.max' => 'O dia de vencimento deve ser no máximo 31.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }
}

