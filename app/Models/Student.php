<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'name',
        'birth_date',
        'email',
        'phone',
        'cpf',
        'photo',
        'rg',
        'birth_certificate_number',
        'notes',
    ];

    // Exemplo de método estático para buscar estudantes por nome
    public static function searchByName($name)
    {
        return self::where('name', 'like', "%$name%") ->get();
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student');
    }
} 