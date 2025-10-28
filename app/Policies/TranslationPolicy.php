<?php

namespace App\Policies;

use App\Models\User;

class TranslationPolicy
{
    /**
     * Determine if the user can view any translations.
     */
    public function viewAny(User $user): bool
    {
        return true; // Alle authentifizierten Benutzer können Übersetzungen sehen
    }

    /**
     * Determine if the user can view the translation.
     */
    public function view(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can create translations.
     */
    public function create(User $user): bool
    {
        return true; // Alle können neue Übersetzungen erstellen
    }

    /**
     * Determine if the user can update translations.
     */
    public function update(User $user): bool
    {
        return true; // Alle können Übersetzungen bearbeiten
    }

    /**
     * Determine if the user can delete translations.
     */
    public function delete(User $user): bool
    {
        return true; // Alle können Übersetzungen löschen
    }

    /**
     * Determine if the user can restore translations.
     */
    public function restore(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can permanently delete translations.
     */
    public function forceDelete(User $user): bool
    {
        return true;
    }
}
