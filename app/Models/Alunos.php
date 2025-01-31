<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alunos extends Model
{

    protected $fillable = [
        'nome',
        'data_nascimento',
        'matricula',
        'email',
        'telefone',
        'file_foto',
        'responsavel_id'
    ];


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