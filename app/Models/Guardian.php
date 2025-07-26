<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $table = 'guardians';

    protected $fillable = [
        'name',
        'relationship', // Parentesco com o estudante
        'cpf',
        'rg',
        'marital_status',
        'status',
        'guardian_type',
        'occupation',
        'workplace',
        'birth_date',
        'gender',
        'notes',
    ];

    // Exemplo de método estático para buscar responsáveis por nome
    public static function searchByName($name)
    {
        return self::where('name', 'like', "%$name%") ->get();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withTimestamps();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class); // Matrículas que este responsável abriu
    }
} 