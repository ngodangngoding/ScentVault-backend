<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Note extends Model
{
    public function perfumeNotes(): HasMany
    {
        return $this->hasMany(PerfumeNote::class);
    }
}
