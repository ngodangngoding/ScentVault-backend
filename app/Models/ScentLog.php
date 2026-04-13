<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['perfume_id', 'user_id', 'occasion_id', 'weather', 'notes_review'])]
class ScentLog extends Model
{
    public const WEATHER = [
        'Cerah', 'Berawan', 'Mendung', 'Hujan', 'Sejuk', 'Dingin'
    ];

    public function perfume(): BelongsTo
    {
        return $this->belongsTo(Perfume::class);
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
