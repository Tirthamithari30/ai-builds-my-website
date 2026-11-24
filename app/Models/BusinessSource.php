<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'source_type',
        'url',
        'title',
        'raw_content',
        'metadata',
        'status',
        'error_message',
        'crawled_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'crawled_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
