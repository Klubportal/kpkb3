<?php foreach ((['page']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<div class="my-6">
    <?php if($level === 'h1'): ?>
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white"><?php echo e($content); ?></h1>
    <?php elseif($level === 'h2'): ?>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($content); ?></h2>
    <?php elseif($level === 'h3'): ?>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($content); ?></h3>
    <?php else: ?>
        <h4 class="text-xl font-semibold text-gray-900 dark:text-white"><?php echo e($content); ?></h4>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\fabricator\page-blocks\heading.blade.php ENDPATH**/ ?>