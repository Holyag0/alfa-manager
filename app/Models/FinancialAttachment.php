<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FinancialAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected $appends = [
        'formatted_size',
        'download_url',
    ];

    /**
     * Relacionamento com transação
     */
    public function transaction()
    {
        return $this->belongsTo(FinancialTransaction::class, 'transaction_id');
    }

    /**
     * Accessor para tamanho formatado
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        $i = 0;
        $maxIndex = count($units) - 1;
        
        while ($bytes >= 1024 && $i < $maxIndex) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Accessor para URL de download
     */
    public function getDownloadUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}

