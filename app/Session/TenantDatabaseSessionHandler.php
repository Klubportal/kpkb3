<?php

namespace App\Session;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Contracts\Auth\Guard;

class TenantDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * Get a fresh query builder instance for the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        // ✅ Connection zur Laufzeit bestimmen (Multi-Tenancy)
        // WICHTIG: Prüfe AUCH die Domain, falls Tenancy noch nicht initialisiert ist
        $connectionName = 'central'; // Default

        if (tenancy()->initialized) {
            $connectionName = 'tenant';
        } elseif (app()->bound('request')) {
            $host = request()->getHost();
            $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

            if (!in_array($host, $centralDomains)) {
                // Wir sind auf einer Tenant-Domain, aber Tenancy ist noch nicht initialisiert
                // Initialisiere Tenancy manuell für Session-Zugriff
                try {
                    $domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $host)->first();
                    if ($domain && $domain->tenant) {
                        tenancy()->initialize($domain->tenant);
                        $connectionName = 'tenant';
                    }
                } catch (\Exception $e) {
                    // Fallback auf central
                    logger()->warning("TenantDatabaseSessionHandler: Konnte Tenancy nicht initialisieren für {$host}: " . $e->getMessage());
                }
            }
        }

        return app('db')->connection($connectionName)->table($this->table);
    }
}
