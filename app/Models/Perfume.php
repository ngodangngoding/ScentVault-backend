<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable(['brand_id', 'name', 'concentration', 'description', 'image', 'is_active', 'star_rating'])]
class Perfume extends Model
{

    public const CONCENTRATION = [
        'extrait de parfum',
        'eau de parfum',
        'eau de toilette',
        'eau de cologne'
    ];

    protected function casts(): array
    {
        return [
            'brand_id' => 'integer',
            'is_active' => 'boolean',
            'star_rating' => 'integer'
        ];
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }
        return null;
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function suitability(): HasOne
    {
        return $this->hasOne(PerfumeSuitability::class);
    }

    public function perfumeNote(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'perfume_notes', 'perfume_id', 'notes_id')
            ->withPivot('type')
            ->withTimestamps();
    }

}


