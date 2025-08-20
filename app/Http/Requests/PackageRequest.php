<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
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
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string|max:1000',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
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
            'name.required' => 'O nome do pacote é obrigatório.',
            'name.max' => 'O nome do pacote não pode ter mais de 255 caracteres.',
            'price.required' => 'O preço do pacote é obrigatório.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'price.max' => 'O preço não pode ser maior que R$ 999.999,99.',
            'category.required' => 'A categoria do pacote é obrigatória.',
            'category.max' => 'A categoria não pode ter mais de 255 caracteres.',
            'status.required' => 'O status do pacote é obrigatório.',
            'status.in' => 'O status deve ser ativo ou inativo.',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'services.array' => 'Os serviços devem ser uma lista.',
            'services.*.exists' => 'Um ou mais serviços selecionados não existem.',
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
            'name' => 'nome do pacote',
            'price' => 'preço',
            'category' => 'categoria',
            'status' => 'status',
            'description' => 'descrição',
            'services' => 'serviços',
        ];
    }
}
