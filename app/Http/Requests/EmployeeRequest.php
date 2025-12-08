<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
        $employeeId = $this->route('employee') ?? $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('employees', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($employeeId)
            ],
            'phone' => 'nullable|string|max:20',
            'cpf' => [
                'nullable',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('employees', 'cpf')
                    ->whereNull('deleted_at')
                    ->ignore($employeeId)
            ],
            'rg' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
