<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
            class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-black hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
        </svg>
        <span class="font-medium uppercase"><?php echo e(app()->getLocale()); ?></span>
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-48 bg-black text-white rounded-lg shadow-xl border border-gray-800 py-2 z-50">

        <?php $__currentLoopData = ['de' => 'Deutsch', 'en' => 'English', 'hr' => 'Hrvatski']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('language.switch', $locale)); ?>"
           class="flex items-center justify-between px-4 py-2 hover:bg-gray-800 transition-colors <?php echo e(app()->getLocale() === $locale ? 'bg-gray-800' : ''); ?>">
            <span class="font-medium"><?php echo e($name); ?></span>
            <?php if(app()->getLocale() === $locale): ?>
            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            <?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\language-switcher.blade.php ENDPATH**/ ?>