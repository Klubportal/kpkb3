<?php foreach ((['page']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>
<?php
    $settings = app(\App\Settings\GeneralSettings::class);
?>

<?php if (isset($component)) { $__componentOriginalb0c4e659bbf958351fd27259f52d6c85 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb0c4e659bbf958351fd27259f52d6c85 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.central','data' => ['title' => $page->title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.central'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($page->title)]); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4"><?php echo e($page->title); ?></h1>

            <?php if($page->excerpt): ?>
                <p class="text-xl text-gray-600 dark:text-gray-400"><?php echo e($page->excerpt); ?></p>
            <?php endif; ?>
        </div>

        
        <div class="space-y-8">
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
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb0c4e659bbf958351fd27259f52d6c85)): ?>
<?php $attributes = $__attributesOriginalb0c4e659bbf958351fd27259f52d6c85; ?>
<?php unset($__attributesOriginalb0c4e659bbf958351fd27259f52d6c85); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb0c4e659bbf958351fd27259f52d6c85)): ?>
<?php $component = $__componentOriginalb0c4e659bbf958351fd27259f52d6c85; ?>
<?php unset($__componentOriginalb0c4e659bbf958351fd27259f52d6c85); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\fabricator\layouts\default.blade.php ENDPATH**/ ?>