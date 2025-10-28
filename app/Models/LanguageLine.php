<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Spatie\TranslationLoader\LanguageLine as BaseLanguageLine;
use App\Models\Scopes\LanguageLineSearchScope;

class LanguageLine extends BaseLanguageLine
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['group', 'key', 'text'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'text' => 'array',
    ];

    /**
     * Scope for searching in text translations
     */
    public function scopeSearchText($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('group', 'like', "%{$search}%")
              ->orWhere('key', 'like', "%{$search}%")
              ->orWhereRaw("JSON_SEARCH(text, 'one', ?) IS NOT NULL", ["%{$search}%"]);
        });
    }

    /**
     * Get cache instance without tagging to avoid errors with file/database cache drivers.
     */
    public static function getCacheKey($group, $locale): string
    {
        return "spatie.translation-loader.{$group}.{$locale}";
    }

    /**
     * Override to use simple cache without tags using array store (no tagging)
     * Adapted for Translation Manager's format (all locales in JSON)
     */
    public static function getTranslationsForGroup(string $locale, string $group): array
    {
        $cacheKey = static::getCacheKey($group, $locale);

        // Use array cache store to avoid tagging issues with tenancy
        return Cache::store('array')->remember($cacheKey, now()->addDay(), function () use ($group, $locale) {
            return static::query()
                ->withoutGlobalScope('searchable')
                ->where('group', $group)
                ->get()
                ->reduce(function ($lines, self $languageLine) use ($locale) {
                    // Translation Manager stores all locales in text as JSON
                    $translations = is_array($languageLine->text) ? $languageLine->text : [];

                    // Get translation for the requested locale
                    if (isset($translations[$locale])) {
                        $lines[$languageLine->key] = $translations[$locale];
                    }

                    return $lines;
                }, []);
        });
    }

    /**
     * Scope for searching in JSON text field - used by Filament table search
     */
    public function scopeSearchGroupAndKey($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('group', 'like', "%{$search}%")
                ->orWhere('key', 'like', "%{$search}%")
                ->orWhereRaw('JSON_SEARCH(text, "one", ?) IS NOT NULL', ["%{$search}%"]);
        });
    }

    /**
     * Clear translation cache on save and delete
     */
    protected static function booted()
    {
        // Add global scope for Filament table search
        static::addGlobalScope(new LanguageLineSearchScope());

        static::saved(function () {
            Cache::flush();
        });

        static::deleted(function () {
            Cache::flush();
        });
    }
}
