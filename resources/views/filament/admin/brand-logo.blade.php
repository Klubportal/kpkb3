@php
    // Lade Settings aus der Central DB
    try {
        $settings = \DB::connection('central')
            ->table('settings')
            ->where('group', 'general')
            ->pluck('payload', 'name')
            ->map(fn($value) => json_decode($value, true));

        $publicBase = config('filesystems.disks.public.url');
        $logo = isset($settings['logo']) && $settings['logo']
            ? rtrim($publicBase, '/').'/'.ltrim($settings['logo'], '/')
            : null;
        $siteName = $settings['site_name'] ?? 'Klubportal';
        $logoHeight = $settings['logo_height'] ?? '3.5rem';
        $primaryColor = $settings['primary_color'] ?? '#3b82f6';
    } catch (\Exception $e) {
        // Fallback wenn Settings nicht geladen werden können
        \Log::error('Tenant Brand Logo Settings Error: ' . $e->getMessage());
        $logo = null;
        $siteName = 'Klubportal';
        $logoHeight = '3.5rem';
        $primaryColor = '#3b82f6';
    }
@endphp



<style>
    /* Vergrößere die Topbar-Höhe */
    .fi-topbar {
        min-height: 120px !important;
        height: auto !important;
    }

    /* Logo-Bereich mehr Platz geben */
    .fi-sidebar-header {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
</style>

<div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; white-space: nowrap; margin-top: 10px; margin-bottom: 20px;">
    @if($logo)
        <img src="{{ $logo }}"
             alt="{{ $siteName }}"
             style="height: {{ $logoHeight }}; max-height: {{ $logoHeight }}; object-fit: contain;">
    @endif

    <span style="font-size: 1.25rem; font-weight: 700; color: {{ $primaryColor }}; line-height: 1;">
        {{ $siteName }}
    </span>
</div>
