<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'description',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'operating_hours',
        'products',
        'services',
        'social_links',
        'status',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'products' => 'array',
        'services' => 'array',
        'social_links' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(BusinessSource::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(UploadedFile::class);
    }

    public function extractedFacts(): HasMany
    {
        return $this->hasMany(ExtractedFact::class);
    }

    public function factConfirmations(): HasMany
    {
        return $this->hasMany(FactConfirmation::class);
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function latestWebsite(): HasOne
    {
        return $this->hasOne(Website::class)->latestOfMany();
    }
}
