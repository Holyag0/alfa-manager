<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'classrooms';

    protected $fillable = [
        'name',
        'year',
        'shift',
        'vacancies',
    ];

    // Exemplo de método estático para buscar turmas por nome
    public static function searchByName($name)
    {
        return self::where('name', 'like', "%$name%") ->get();
    }
} 