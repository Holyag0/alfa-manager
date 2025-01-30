<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alunos extends Model
{
    // Definindo os campos que podem ser preenchidos
    protected $fillable = [
        'nome',
        'data_nascimento',
        'matricula',
        'email',
        'telefone',
        'file_foto',
        'endereco_id',
        'responsavel_id'
    ];

    /**
     * Relação com o modelo Endereco
     *
     * @return BelongsTo
     */
    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    /**
     * Relação com o modelo Responsavel
     *
     * @return BelongsTo
     */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class);
    }
}