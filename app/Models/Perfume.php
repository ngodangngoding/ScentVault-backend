<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable(['brand_id', 'category_id', 'name', 'concentration', 'description', 'image'])]
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
            'category_id' => 'integer',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function suitability(): HasOne
    {
        return $this->hasOne(PerfumeSuitability::class, 'perfume_id', 'id');
    }

    public function scentLog(): HasMany
    {
        return $this->hasMany(ScentLog::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_perfumes', 'perfume_id', 'user_id')
            ->withPivot('star_rating')
            ->withTimestamps();
    }

    public function perfumeNote(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'perfume_notes', 'perfume_id', 'notes_id')
            ->withPivot('type')
            ->withTimestamps();
    }
}


