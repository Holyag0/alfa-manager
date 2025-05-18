<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permitir a requisição. Pode personalizar conforme a lógica de autorização.
        return auth()->user()->hasRole('Programador');
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ];
    }
}
