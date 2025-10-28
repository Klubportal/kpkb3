<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'subtitle' => '',
    'image' => null,
    'height' => 'h-[600px]',
    'gradient' => 'from-primary to-secondary',
    'cta' => null,
    'ctaUrl' => '#',
    'ctaStyle' => 'btn-primary'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => '',
    'subtitle' => '',
    'image' => null,
    'height' => 'h-[600px]',
    'gradient' => 'from-primary to-secondary',
    'cta' => null,
    'ctaUrl' => '#',
    'ctaStyle' => 'btn-primary'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="relative <?php echo e($height); ?> bg-gradient-to-r <?php echo e($gradient); ?> overflow-hidden">
    
    <?php if($image): ?>
    <div class="absolute inset-0">
        <img src="<?php echo e($image); ?>"
             alt="<?php echo e($title); ?>"
             class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
    </div>
    <?php endif; ?>

    
    <div class="relative max-w-7xl mx-auto px-4 h-full flex items-center">
        <div class="max-w-3xl text-white animate-fadeInUp">
            <?php if($subtitle): ?>
            <div class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold mb-4 animate-fadeIn">
                <?php echo e($subtitle); ?>

            </div>
            <?php endif; ?>

            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
                <?php echo e($title); ?>

            </h1>

            <?php if($slot->isNotEmpty()): ?>
            <div class="text-xl md:text-2xl mb-8 text-white/90">
                <?php echo e($slot); ?>

            </div>
            <?php endif; ?>

            <?php if($cta): ?>
            <a href="<?php echo e($ctaUrl); ?>"
               class="btn <?php echo e($ctaStyle); ?> btn-lg gap-2 animate-scaleIn">
                <?php echo e($cta); ?>

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-float">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-white">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\hero.blade.php ENDPATH**/ ?>