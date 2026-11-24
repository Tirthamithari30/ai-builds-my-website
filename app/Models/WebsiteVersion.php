<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'version_number',
        'snapshot',
        'changelog',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'snapshot' => 'array',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
