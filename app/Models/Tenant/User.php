<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * ✅ WICHTIG: Tenant User nutzt IMMER tenant-Datenbank!
     */
    protected $connection = 'tenant';

    /**
     * Tenant-Datenbank wird automatisch über Tenancy Middleware gewechselt
     * BelongsToTenant Trait NICHT nötig bei separaten DBs
     */
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ✅ FILAMENT: Kann User auf Panel zugreifen?
     *
     * WICHTIG: Diese Methode wird NUR für authentifizierte User aufgerufen!
     * Für Gäste (nicht eingeloggt) redirectet Filament automatisch zum Login.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // DEBUG: Temporär ALLE Users erlauben
        return true;

        // // Tenant Panel: Alle Tenant Users dürfen rein
        // if ($panel->getId() === 'club') {
        //     return true;
        // }

        // // Central Panel: Tenant Users NICHT erlauben
        // return false;
    }
}
