<?php

namespace App\Providers;

use App\Services\TemplateLoaderService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TemplateLoaderService::class, function ($app) {
            return new TemplateLoaderService();
        });

        $this->app->alias(TemplateLoaderService::class, 'template.loader');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share template data with all views after tenant is initialized
        if (tenant()) {
            $templateLoader = $this->app->make(TemplateLoaderService::class);
            $templateLoader->shareWithViews();

            // Register Blade directive for template colors
            Blade::directive('templateColor', function ($expression) {
                return "<?php echo app('template.loader')->getColor($expression); ?>";
            });

            // Register Blade directive to check template features
            Blade::directive('hasFeature', function ($expression) {
                return "<?php if(app('template.loader')->hasFeature($expression)): ?>";
            });

            Blade::directive('endHasFeature', function () {
                return "<?php endif; ?>";
            });
        }
    }
}
