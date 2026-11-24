<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'section_type',
        'order',
        'is_visible',
        'content',
        'design_overrides',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_visible' => 'boolean',
        'content' => 'array',
        'design_overrides' => 'array',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
