<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'original_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'extracted_text',
        'processing_status',
        'error_message',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
