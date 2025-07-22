<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'guardian_id',
        'type',
        'value',
        'is_primary',
        'contact_for',
    ];

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }
} 