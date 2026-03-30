<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['brand_id', 'name', 'concentration', 'description', 'image', 'is_active', 'star_rating'])]
class Perfume extends Model
{
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function suitability(): HasOne
    {
        return $this->hasOne(PerfumeSuitability::class);
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(PerfumeNote::class);
    }

}


