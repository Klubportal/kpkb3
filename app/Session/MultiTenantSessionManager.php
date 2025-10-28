<?php

namespace App\Session;

use Illuminate\Session\SessionManager;

class MultiTenantSessionManager extends SessionManager
{
    /**
     * Build the session instance.
     *
     * Überschreibt die Standard-Methode, um die Connection basierend auf Tenancy zu wählen.
     */
    protected function buildSession($handler)
    {
        // Connection zur Laufzeit bestimmen
        $connectionName = $this->getConnectionName();

        // Aktualisiere die Config für den aktuellen Request
        $this->config->set('session.connection', $connectionName);

        return parent::buildSession($handler);
    }

    /**
     * Bestimme die richtige DB-Connection basierend auf Tenancy.
     */
    protected function getConnectionName(): string
    {
        // Prüfe ob Tenancy initialisiert ist
        if (tenancy()->initialized) {
            return 'tenant';
        }

        // Fallback: Prüfe Domain
        if (app()->bound('request')) {
            $host = request()->getHost();
            $centralDomains = ['localhost', '127.0.0.1', 'admin.klubportal.com'];

            if (!in_array($host, $centralDomains)) {
                // Wir sind auf Tenant-Domain, aber Tenancy nicht initialisiert
                // → Nutze 'central' als Fallback (sollte nicht passieren)
                logger()->warning("MultiTenantSessionManager: Tenant-Domain aber Tenancy nicht initialisiert für {$host}");
            }
        }

        return 'central';
    }
}
