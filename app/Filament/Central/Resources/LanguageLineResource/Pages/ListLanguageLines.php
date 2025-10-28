<?php

namespace App\Filament\Central\Resources\LanguageLineResource\Pages;

use Kenepa\TranslationManager\Resources\LanguageLineResource\Pages\ListLanguageLines as BaseListLanguageLines;
use Illuminate\Database\Eloquent\Builder;

class ListLanguageLines extends BaseListLanguageLines
{
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $search = $this->getTableSearch();

        if (filled($search)) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('group', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhereRaw('JSON_SEARCH(text, "one", ?) IS NOT NULL', ["%{$search}%"]);
            });
        }

        return $query;
    }
}
