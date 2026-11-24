<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'fact_key',
        'confirmed_value',
        'resolution_type',
        'conflict_candidates',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'conflict_candidates' => 'array',
        'confirmed_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
