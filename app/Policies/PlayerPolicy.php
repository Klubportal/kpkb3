<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Tenant\Player;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlayerPolicy
{
    use HandlesAuthorization;

    /**
     * ✅ Multi-Tenancy: Alle authentifizierten Tenant-User haben vollen Zugriff
     * TODO: Später Shield Permissions aktivieren wenn benötigt
     */

    public function viewAny(AuthUser $authUser): bool
    {
        return true; // Alle Tenant Users können Players sehen
    }

    public function view(AuthUser $authUser, Player $player): bool
    {
        return true;
    }

    public function create(AuthUser $authUser): bool
    {
        return true;
    }

    public function update(AuthUser $authUser, Player $player): bool
    {
        return true;
    }

    public function delete(AuthUser $authUser, Player $player): bool
    {
        return true;
    }

    public function restore(AuthUser $authUser, Player $player): bool
    {
        return true;
    }

    public function forceDelete(AuthUser $authUser, Player $player): bool
    {
        return true;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return true;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return true;
    }

    public function replicate(AuthUser $authUser, Player $player): bool
    {
        return true;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return true;
    }

}
