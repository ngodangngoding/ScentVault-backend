<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Weather extends Model
{
    public function suitabilities(): HasMany
    {
        return $this->hasMany(PerfumeSuitability::class);
    }
}
