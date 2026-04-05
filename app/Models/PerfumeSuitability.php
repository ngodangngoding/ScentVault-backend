<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['perfume_id', 'ideal_temperature', 'ideal_time', 'ideal_environment'])]
class PerfumeSuitability extends Model
{
    public function perfume(): BelongsTo
    {
        return $this->belongsTo(Perfume::class);
    }
}
