<?php

namespace App\Services;

use App\Models\Central\Template;
use App\Models\Tenant;
use Illuminate\Support\Facades\View;

class TemplateLoaderService
{
    protected ?Template $currentTemplate = null;

    /**
     * Get the current template for the active tenant
     */
    public function getCurrentTemplate(): ?Template
    {
        if ($this->currentTemplate) {
            return $this->currentTemplate;
        }

        // Check if we're in tenant context
        if (!tenant()) {
            return Template::getDefault();
        }

        $tenant = Tenant::current();

        if ($tenant && $tenant->template_id) {
            $this->currentTemplate = Template::find($tenant->template_id);
        }

        // Fallback to default template
        if (!$this->currentTemplate) {
            $this->currentTemplate = Template::getDefault();
        }

        return $this->currentTemplate;
    }

    /**
     * Get the layout path for the current template
     */
    public function getLayoutPath(): string
    {
        $template = $this->getCurrentTemplate();
        return $template?->getLayoutPath() ?? 'layouts.frontend';
    }

    /**
     * Get a color value from the current template
     */
    public function getColor(string $key, string $default = '#000000'): string
    {
        $template = $this->getCurrentTemplate();
        return $template?->getColor($key, $default) ?? $default;
    }

    /**
     * Check if current template has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        $template = $this->getCurrentTemplate();
        return $template?->hasFeature($feature) ?? false;
    }

    /**
     * Get all colors from current template
     */
    public function getColors(): array
    {
        $template = $this->getCurrentTemplate();
        return $template?->colors ?? [];
    }

    /**
     * Get all features from current template
     */
    public function getFeatures(): array
    {
        $template = $this->getCurrentTemplate();
        return $template?->features ?? [];
    }

    /**
     * Share template data with all views
     */
    public function shareWithViews(): void
    {
        $template = $this->getCurrentTemplate();

        View::share('currentTemplate', $template);
        View::share('templateColors', $this->getColors());
        View::share('templateFeatures', $this->getFeatures());
    }

    /**
     * Get CSS variables for the current template colors
     */
    public function getCssVariables(): string
    {
        $colors = $this->getColors();
        $css = ':root {';

        foreach ($colors as $key => $value) {
            $css .= "--color-{$key}: {$value};";
        }

        $css .= '}';

        return $css;
    }

    /**
     * Check if a custom view exists for the current template
     */
    public function viewExists(string $view): bool
    {
        $template = $this->getCurrentTemplate();

        if (!$template) {
            return view()->exists($view);
        }

        $customView = "templates.{$template->slug}.{$view}";

        return view()->exists($customView);
    }

    /**
     * Get view name (custom template view if exists, otherwise default)
     */
    public function getView(string $view): string
    {
        $template = $this->getCurrentTemplate();

        if (!$template) {
            return $view;
        }

        $customView = "templates.{$template->slug}.{$view}";

        return view()->exists($customView) ? $customView : $view;
    }
}
