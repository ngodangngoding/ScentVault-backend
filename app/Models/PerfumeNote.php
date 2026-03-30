<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['perfume_id', 'notes_id', 'type'])]
class PerfumeNote extends Model
{
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function perfumes(): HasMany
    {
        return $this->hasMany(Perfume::class);
    }
}
