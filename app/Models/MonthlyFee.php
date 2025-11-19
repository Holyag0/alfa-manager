<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyFee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enrollment_id',
        'classroom_service_id',
        'academic_year',
        'contract_number',
        'base_amount',
        'total_installments',
        'has_sibling_discount',
        'sibling_discount_amount',
        'has_punctuality_discount',
        'punctuality_discount_amount',
        'start_date',
        'end_date',
        'due_day',
        'status',
        'notes',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'sibling_discount_amount' => 'decimal:2',
        'punctuality_discount_amount' => 'decimal:2',
        'has_sibling_discount' => 'boolean',
        'has_punctuality_discount' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_installments' => 'integer',
        'due_day' => 'integer',
    ];

    // Relationships
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function classroomService(): BelongsTo
    {
        return $this->belongsTo(ClassroomService::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(MonthlyFeeInstallment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeByEnrollment($query, $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    // Accessors
    public function getFormattedBaseAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->base_amount, 2, ',', '.');
    }

    public function getFormattedSiblingDiscountAttribute(): string
    {
        return 'R$ ' . number_format($this->sibling_discount_amount, 2, ',', '.');
    }

    public function getFormattedPunctualityDiscountAttribute(): string
    {
        return 'R$ ' . number_format($this->punctuality_discount_amount, 2, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Ativo',
            'cancelled' => 'Cancelado',
            'completed' => 'Concluído',
            'suspended' => 'Suspenso',
            default => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'cancelled' => 'red',
            'completed' => 'blue',
            'suspended' => 'yellow',
            default => 'gray',
        };
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}

