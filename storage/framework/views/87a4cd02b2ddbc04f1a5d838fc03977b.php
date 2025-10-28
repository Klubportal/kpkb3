<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['page']));

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

foreach (array_filter((['page']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php if (isset($component)) { $__componentOriginaldef144e4ec70fc01ff9e0109d63fdef6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldef144e4ec70fc01ff9e0109d63fdef6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-fabricator::components.layouts.base','data' => ['page' => $page,'title' => $page->title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-fabricator::layouts.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['page' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page->title)]); ?>
    

    <?php if (isset($component)) { $__componentOriginal2742598f85fe3cf008baa9fa8956cdda = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2742598f85fe3cf008baa9fa8956cdda = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-fabricator::components.page-blocks','data' => ['blocks' => $page->blocks]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-fabricator::page-blocks'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['blocks' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page->blocks)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2742598f85fe3cf008baa9fa8956cdda)): ?>
<?php $attributes = $__attributesOriginal2742598f85fe3cf008baa9fa8956cdda; ?>
<?php unset($__attributesOriginal2742598f85fe3cf008baa9fa8956cdda); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2742598f85fe3cf008baa9fa8956cdda)): ?>
<?php $component = $__componentOriginal2742598f85fe3cf008baa9fa8956cdda; ?>
<?php unset($__componentOriginal2742598f85fe3cf008baa9fa8956cdda); ?>
<?php endif; ?>

     
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldef144e4ec70fc01ff9e0109d63fdef6)): ?>
<?php $attributes = $__attributesOriginaldef144e4ec70fc01ff9e0109d63fdef6; ?>
<?php unset($__attributesOriginaldef144e4ec70fc01ff9e0109d63fdef6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldef144e4ec70fc01ff9e0109d63fdef6)): ?>
<?php $component = $__componentOriginaldef144e4ec70fc01ff9e0109d63fdef6; ?>
<?php unset($__componentOriginaldef144e4ec70fc01ff9e0109d63fdef6); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\filament-fabricator\layouts\default.blade.php ENDPATH**/ ?>