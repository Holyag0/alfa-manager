<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_salary' => 'required|numeric|min:0',
            'gratification' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'inss_deduction' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:pix,poupanca,conta_corrente,dinheiro',
            'payment_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'mark_as_paid' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'base_salary.required' => 'O salário base é obrigatório.',
            'base_salary.numeric' => 'O salário base deve ser um número.',
            'base_salary.min' => 'O salário base não pode ser negativo.',
            'payment_method.in' => 'Método de pagamento inválido.',
        ];
    }
}
