<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Session\TenantDatabaseSessionHandler;
use Illuminate\Session\Store;

class TenantSessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // ✅ Registriere custom Session Handler für database driver
        $this->app['session']->extend('database', function ($app) {
            $connection = $app['db']->connection(config('session.connection'));
            $table = config('session.table');
            $lifetime = config('session.lifetime');

            // Nutze unseren TenantDatabaseSessionHandler!
            $handler = new TenantDatabaseSessionHandler(
                $connection,
                $table,
                $lifetime,
                $app
            );

            return new Store(
                config('session.cookie'),
                $handler
            );
        });
    }
}
