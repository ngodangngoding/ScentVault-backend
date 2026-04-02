<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['perfume_id', 'user_id', 'occasion_id', 'environment', 'notes_review'])]
class ScentLog extends Model
{
    public const ENVIRONMENT = [
        'indoor',
        'outdoor',
        'all around'
    ];

    public function perfumes(): HasMany
    {
        return $this->hasMany(Perfume::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }
}
