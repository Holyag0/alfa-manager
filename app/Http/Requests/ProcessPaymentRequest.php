<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
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
            'installment_id' => 'required|exists:monthly_fee_installments,id',
            'paid_by_guardian_id' => 'required|exists:guardians,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:pix,credit_card,debit_card,cash,bank_transfer,check',
            'payment_date' => 'nullable|date',
            'reference' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'auto_confirm' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'installment_id.required' => 'A parcela é obrigatória.',
            'installment_id.exists' => 'A parcela selecionada não existe.',
            'paid_by_guardian_id.required' => 'O responsável pagador é obrigatório.',
            'paid_by_guardian_id.exists' => 'O responsável selecionado não existe.',
            'amount.required' => 'O valor do pagamento é obrigatório.',
            'amount.numeric' => 'O valor do pagamento deve ser um número.',
            'amount.min' => 'O valor do pagamento deve ser maior ou igual a zero.',
            'method.required' => 'O método de pagamento é obrigatório.',
            'method.in' => 'O método de pagamento deve ser: PIX, cartão de crédito, cartão de débito, dinheiro, transferência bancária ou cheque.',
            'payment_date.date' => 'A data de pagamento deve ser uma data válida.',
            'reference.max' => 'A referência não pode ter mais de 255 caracteres.',
            'transaction_id.max' => 'O ID da transação não pode ter mais de 255 caracteres.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }
}

