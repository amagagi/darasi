<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'logo_path',
        'cover_image_path',
        'url',
        'category',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saving(function (Platform $platform): void {
            if (blank($platform->slug)) {
                $platform->slug = static::slugUnique($platform->name, $platform->id);
            }
        });

        static::saved(fn () => Cache::forget('platforms.active'));
        static::deleted(fn () => Cache::forget('platforms.active'));
    }

    /** Génère un slug unique à partir de $name, en s'excluant soi-même. */
    private static function slugUnique(string $name, ?int $ignoreId): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffixe = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffixe}";
            $suffixe++;
        }

        return $slug;
    }
}
