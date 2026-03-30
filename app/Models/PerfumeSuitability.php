<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['perfume_id', 'weather_id', 'ideal_time', 'ideal_environment'])]
class PerfumeSuitability extends Model
{
    public function perfume(): BelongsTo
    {
        return $this->belongsTo(Perfume::class);
    }

    public function weather(): BelongsTo
    {
        return $this->belongsTo(Weather::class);
    }
}
