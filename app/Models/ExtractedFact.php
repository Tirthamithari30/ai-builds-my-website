<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtractedFact extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'source_type',
        'source_id',
        'source_url',
        'fact_key',
        'fact_name',
        'fact_value',
        'evidence',
        'verification_status',
        'confidence_score',
        'is_confirmed',
        'is_owner_provided',
        'user_confirmed_value',
        'extraction_time',
        'notes',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'is_owner_provided' => 'boolean',
        'confidence_score' => 'float',
        'extraction_time' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
