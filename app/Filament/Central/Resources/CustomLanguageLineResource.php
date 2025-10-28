<?php

namespace App\Filament\Central\Resources;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Kenepa\TranslationManager\Resources\LanguageLineResource;

class CustomLanguageLineResource extends LanguageLineResource
{
    public static function getColumns(): array
    {
        $columns = parent::getColumns();

        // Find and replace the group_and_key column with proper searchable
        foreach ($columns as $key => $column) {
            if ($column instanceof TextColumn && $column->getName() === 'group_and_key') {
                $columns[$key] = TextColumn::make('group_and_key')
                    ->label(__('translation-manager::translations.group') . ' & ' . __('translation-manager::translations.key'))
                    ->searchable(['group', 'key', 'text'], query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search) {
                            $query->where('group', 'like', "%{$search}%")
                                ->orWhere('key', 'like', "%{$search}%")
                                ->orWhereRaw('JSON_SEARCH(text, "one", ?) IS NOT NULL', ["%{$search}%"]);
                        });
                    })
                    ->getStateUsing(function ($record) {
                        return $record->group . '.' . $record->key;
                    });
                break;
            }
        }

        return $columns;
    }
}
