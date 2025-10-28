<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'image' => null,
    'date' => null,
    'category' => null,
    'url' => '#',
    'excerpt' => null,
    'featured' => false,
    'animate' => true
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
    'title' => null,
    'image' => null,
    'date' => null,
    'category' => null,
    'url' => '#',
    'excerpt' => null,
    'featured' => false,
    'animate' => true
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="card bg-base-100 shadow-xl <?php echo e($animate ? 'hover-lift' : ''); ?> <?php echo e($featured ? 'lg:col-span-2' : ''); ?>">
    <?php if($image): ?>
    <figure class="<?php echo e($featured ? 'h-96' : 'h-48'); ?> overflow-hidden image-zoom">
        <img src="<?php echo e($image); ?>"
             alt="<?php echo e($title); ?>"
             class="w-full h-full object-cover">
    </figure>
    <?php else: ?>
    <figure class="<?php echo e($featured ? 'h-96' : 'h-48'); ?> bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20 text-base-content/20">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
        </svg>
    </figure>
    <?php endif; ?>

    <div class="card-body <?php echo e($featured ? 'p-8' : ''); ?>">
        
        <div class="flex items-center gap-3 text-sm text-base-content/60 mb-2 flex-wrap">
            <?php if($date): ?>
            <div class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <?php echo e($date); ?>

            </div>
            <?php endif; ?>

            <?php if($category): ?>
            <div class="badge badge-primary badge-sm"><?php echo e($category); ?></div>
            <?php endif; ?>
        </div>

        
        <h3 class="card-title <?php echo e($featured ? 'text-3xl' : 'text-xl'); ?> line-clamp-2">
            <?php echo e($title); ?>

        </h3>

        
        <?php if($excerpt): ?>
        <p class="text-base-content/70 <?php echo e($featured ? 'text-lg line-clamp-4' : 'line-clamp-3'); ?>">
            <?php echo e($excerpt); ?>

        </p>
        <?php endif; ?>

        
        <?php if($slot->isNotEmpty()): ?>
        <div class="mt-4">
            <?php echo e($slot); ?>

        </div>
        <?php endif; ?>

        
        <div class="card-actions justify-end mt-4">
            <a href="<?php echo e($url); ?>" class="btn btn-primary btn-sm">
                Weiterlesen →
            </a>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\card.blade.php ENDPATH**/ ?>