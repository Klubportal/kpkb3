<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;

class TelescopeServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (!\class_exists('Laravel\\Telescope\\Telescope')) {
            return;
        }

        // \Laravel\Telescope\Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        \call_user_func(['Laravel\\Telescope\\Telescope', 'filter'], function ($entry) use ($isLocal) {
            return $isLocal ||
                   (\method_exists($entry, 'isReportableException') && $entry->isReportableException()) ||
                   (\method_exists($entry, 'isFailedRequest') && $entry->isFailedRequest()) ||
                   (\method_exists($entry, 'isFailedJob') && $entry->isFailedJob()) ||
                   (\method_exists($entry, 'isScheduledTask') && $entry->isScheduledTask()) ||
                   (\method_exists($entry, 'hasMonitoredTag') && $entry->hasMonitoredTag());
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if (!\class_exists('Laravel\\Telescope\\Telescope') || $this->app->environment('local')) {
            return;
        }

        \call_user_func(['Laravel\\Telescope\\Telescope', 'hideRequestParameters'], ['_token']);

        \call_user_func(['Laravel\\Telescope\\Telescope', 'hideRequestHeaders'], [
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'michael@klubportal.com',
            ]) || $user->hasRole('super_admin');
        });
    }
}
