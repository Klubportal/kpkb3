<?php foreach ((['page']) as $__key => $__value) {
    $__consumeVariable = is_string($__key) ? $__key : $__value;
    $$__consumeVariable = is_string($__key) ? $__env->getConsumableComponentData($__key, $__value) : $__env->getConsumableComponentData($__value);
} ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-6">
    <?php if(isset($images) && is_array($images)): ?>
        <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="aspect-square overflow-hidden rounded-lg">
                <img src="<?php echo e(Storage::url($image)); ?>"
                     alt="Gallery Image"
                     class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\fabricator\page-blocks\image-gallery.blade.php ENDPATH**/ ?>