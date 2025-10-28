<?php foreach ((['page']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<div class="prose prose-lg max-w-none my-6">
    <?php echo $content; ?>

</div>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\fabricator\page-blocks\text-content.blade.php ENDPATH**/ ?>