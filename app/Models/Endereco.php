<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    // Definindo os campos que podem ser preenchidos
    protected $fillable = [
        'rua',
        'numero',
        'cidade',
        'estado',
        'cep'
    ];

    /**
     * Relação com o modelo Aluno
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsavel()
    {
        return $this->belongsTo(Responsavel::class);
    }
    
}