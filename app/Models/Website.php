<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'title',
        'slug',
        'design_preset',
        'primary_color',
        'secondary_color',
        'font_family',
        'meta_description',
        'is_published',
        'published_at',
        'current_version',
        'settings',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'current_version' => 'integer',
        'settings' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(WebsiteSection::class)->orderBy('order', 'asc');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(WebsiteVersion::class)->orderBy('version_number', 'desc');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class)->orderBy('published_at', 'desc');
    }

    public function activePublication(): HasOne
    {
        return $this->hasOne(Publication::class)->where('is_active', true)->latestOfMany();
    }
}
