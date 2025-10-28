<?php
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
?>



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
    <!--[if BLOCK]><![endif]--><?php if($logo): ?>
        <img src="<?php echo e($logo); ?>"
             alt="<?php echo e($siteName); ?>"
             style="height: <?php echo e($logoHeight); ?>; max-height: <?php echo e($logoHeight); ?>; object-fit: contain;">
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <span style="font-size: 1.25rem; font-weight: 700; color: <?php echo e($primaryColor); ?>; line-height: 1;">
        <?php echo e($siteName); ?>

    </span>
</div>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views/filament/admin/brand-logo.blade.php ENDPATH**/ ?>