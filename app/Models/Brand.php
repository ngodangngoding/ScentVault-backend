<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'normalized_name'])]
class Brand extends Model
{

    public function perfumes(): HasMany
    {
        return $this->hasMany(Perfume::class);
    }
}
