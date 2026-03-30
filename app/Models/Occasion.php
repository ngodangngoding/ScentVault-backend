<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Occasion extends Model
{
    public function scentLogs(): HasMany
    {
        return $this->hasMany(ScentLog::class);
    }
}
