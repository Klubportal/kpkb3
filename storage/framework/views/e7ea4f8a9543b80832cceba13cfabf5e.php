<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', $title ?? 'Klubportal - Die moderne Vereinsverwaltung'); ?></title>

    <!-- Favicon -->
    <?php if(View::hasSection('favicon')): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo $__env->yieldContent('favicon'); ?>">
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $__env->yieldContent('favicon'); ?>">
    <?php else: ?>
        <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <?php endif; ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        :root {
            --primary: #dc2626;
            --secondary: #1f2937;
            --accent: #2563eb;
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }

        /* Gradient Animations */
        .gradient-animation {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Fade In Up Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Pulse Glow */
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(220, 38, 38, 0.5); }
            50% { box-shadow: 0 0 40px rgba(220, 38, 38, 0.8); }
        }

        .animate-pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }

        /* Hover Lift */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        /* Scale on Hover */
        .hover-scale {
            transition: transform 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Parallax Background */
        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #dc2626, #991b1b);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #dc2626;
        }

        /* Shimmer Effect */
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }

        /* Reveal on Scroll */
        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.6s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="antialiased bg-gradient-to-br from-gray-50 to-gray-100">
    <?php echo e($slot); ?>


    <script>
        // Scroll Reveal Animation
        function reveal() {
            const reveals = document.querySelectorAll('.reveal');
            reveals.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', reveal);
        reveal(); // Initial check
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\components\layouts\app.blade.php ENDPATH**/ ?>