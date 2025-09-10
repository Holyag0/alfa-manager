<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'guardian_id',
        'classroom_id',
        'status',
        'process',
        'enrollment_date',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relacionamento com finanças da matrícula
     */
    public function finance()
    {
        return $this->hasOne(EnrollmentFinance::class);
    }

    /**
     * Relacionamento com faturas da matrícula
     */
    public function invoices()
    {
        return $this->hasMany(EnrollmentInvoice::class);
    }

    /**
     * Relacionamento com pagamentos da matrícula
     */
    public function payments()
    {
        return $this->hasMany(EnrollmentPayment::class);
    }
} 