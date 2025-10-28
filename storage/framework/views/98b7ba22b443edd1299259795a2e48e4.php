<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Klubportal - Vereinsverwaltung für Fußballvereine'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        :root {
            --primary: #1e40af;
            --secondary: #dc2626;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100">
    <?php echo $__env->make('central-frontend.partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldContent('hero'); ?>

    <main class="py-12">
        <div class="container mx-auto px-4">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <?php echo $__env->make('central-frontend.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\central-frontend\layout.blade.php ENDPATH**/ ?>