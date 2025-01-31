<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsavel extends Model
{
    protected $fillable = [
        'nome',
        'cpf',
        'rg',
        'parentesco',
        'data_nascimento',
        'telefone',
        'email',
        'tipo',
        'endereco_id',
        'status'
    ];

    /**
     * Relação com o modelo Aluno
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alunos()
    {
        return $this->hasMany(Alunos::class);
    }
    public function endereco()
    {
        return $this->hasMany(Endereco::class);
    }
}