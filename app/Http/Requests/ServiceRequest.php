<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'category' => 'required|string|max:255',
            'type' => 'nullable|in:monthly,enrollment,reservation,service,other', // Opcional, será mapeado automaticamente
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string|max:1000',
            'is_classroom_linked' => 'boolean',
            'selected_classrooms' => 'required_if:is_classroom_linked,true|array',
            'selected_classrooms.*' => 'exists:classrooms,id',
            'classroom_linking_notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do serviço é obrigatório.',
            'name.max' => 'O nome do serviço não pode ter mais de 255 caracteres.',
            'price.required' => 'O preço do serviço é obrigatório.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'price.max' => 'O preço não pode ser maior que R$ 999.999,99.',
            'category.required' => 'A categoria do serviço é obrigatória.',
            'category.max' => 'A categoria não pode ter mais de 255 caracteres.',
            'status.required' => 'O status do serviço é obrigatório.',
            'status.in' => 'O status deve ser ativo ou inativo.',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'selected_classrooms.required_if' => 'É necessário selecionar pelo menos uma turma quando o serviço é vinculado.',
            'selected_classrooms.*.exists' => 'Uma das turmas selecionadas não existe.',
            'classroom_linking_notes.max' => 'As notas de vinculação não podem ter mais de 500 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do serviço',
            'price' => 'preço',
            'category' => 'categoria',
            'status' => 'status',
            'description' => 'descrição',
            'is_classroom_linked' => 'vinculação com turmas',
            'selected_classrooms' => 'turmas selecionadas',
            'classroom_linking_notes' => 'notas de vinculação',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('is_classroom_linked') && empty($this->input('selected_classrooms'))) {
                $validator->errors()->add('selected_classrooms', 'É necessário selecionar pelo menos uma turma quando o serviço é vinculado.');
            }
        });
    }
}
