<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsavel extends Model
{
    protected $fillable = [
        'nome',
        'telefone',
        'email'
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
}