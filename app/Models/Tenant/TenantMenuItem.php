<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantMenuItem extends Model
{
    protected $fillable = [
        'label',
        'icon',
        'url',
        'route',
        'route_parameters',
        'sort_order',
        'is_active',
        'group',
        'badge',
        'badge_color',
        'permissions',
        'roles',
        'parent_id',
    ];

    protected $casts = [
        'route_parameters' => 'array',
        'permissions' => 'array',
        'roles' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Parent menu item (for submenus)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TenantMenuItem::class, 'parent_id');
    }

    /**
     * Child menu items (submenus)
     */
    public function children(): HasMany
    {
        return $this->hasMany(TenantMenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Get active menu items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get root menu items (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if user has permission to see this menu item
     */
    public function canView(?object $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        // Check permissions
        if (!empty($this->permissions)) {
            foreach ($this->permissions as $permission) {
                if (!$user->can($permission)) {
                    return false;
                }
            }
        }

        // Check roles
        if (!empty($this->roles)) {
            if (!$user->hasAnyRole($this->roles)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the full URL for this menu item
     */
    public function getUrl(): ?string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->route) {
            $params = $this->route_parameters ?? [];
            return route($this->route, $params);
        }

        return null;
    }
}
