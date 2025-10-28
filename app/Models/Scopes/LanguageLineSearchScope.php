<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;

class LanguageLineSearchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Check if this is a Livewire table search request
        $request = request();

        $search = null;

        // Check if it's a Livewire request with components
        $components = $request->input('components', []);

        if (!empty($components) && isset($components[0]['snapshot'])) {
            $snapshot = json_decode($components[0]['snapshot'], true);

            if (isset($snapshot['data']['tableSearch']) && filled($snapshot['data']['tableSearch'])) {
                $search = $snapshot['data']['tableSearch'];
            }
        }

        // Also check direct search parameter (for GET requests)
        if (!$search && $request->has('search')) {
            $search = $request->get('search');
        }

        // Apply search if found - searches in group, key, and all translations (JSON text field)
        if ($search) {
            $builder->where(function (Builder $query) use ($search) {
                $query->where('group', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhereRaw('JSON_SEARCH(text, "one", ?) IS NOT NULL', ["%{$search}%"]);
            });
        }
    }
}
