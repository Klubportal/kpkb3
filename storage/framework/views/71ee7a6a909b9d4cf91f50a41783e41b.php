<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name')); ?> - Club Management Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">

    
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-indigo-600">
                        ⚽ <?php echo e(config('app.name')); ?>

                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/admin/login"
                       class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Admin Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <h2 class="text-5xl font-bold text-gray-900 mb-6">
                Die All-in-One Plattform für Sportvereine
            </h2>
            <p class="text-xl text-gray-600 mb-12 max-w-3xl mx-auto">
                Verwalte deinen Verein professionell mit unserem modernen Club Management System.
                News, Events, Teams, Spieler und vieles mehr - alles an einem Ort.
            </p>

            <div class="grid md:grid-cols-3 gap-8 mt-16">
                
                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                    <div class="text-4xl mb-4">📰</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">News & Content</h3>
                    <p class="text-gray-600">
                        Veröffentliche Neuigkeiten und Artikel in mehreren Sprachen mit unserem CMS
                    </p>
                </div>

                
                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Teams & Spieler</h3>
                    <p class="text-gray-600">
                        Verwalte Teams, Spieler, Trainer und halte alle Daten aktuell
                    </p>
                </div>

                
                <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Events & Spiele</h3>
                    <p class="text-gray-600">
                        Plane Events, tracke Spiele und behalte den Überblick über alle Termine
                    </p>
                </div>
            </div>

            <div class="mt-16 bg-white rounded-xl shadow-lg p-8 max-w-2xl mx-auto">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">🚀 Multi-Tenancy Architektur</h3>
                <p class="text-gray-600 mb-6">
                    Jeder Verein erhält seine eigene isolierte Umgebung mit eigener Datenbank,
                    eigenem Domain und vollständiger Anpassbarkeit.
                </p>
                <div class="grid md:grid-cols-2 gap-4 text-left">
                    <div class="flex items-start space-x-3">
                        <span class="text-green-500 text-xl">✓</span>
                        <span class="text-gray-700">Eigene Subdomain</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-green-500 text-xl">✓</span>
                        <span class="text-gray-700">Isolierte Datenbank</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-green-500 text-xl">✓</span>
                        <span class="text-gray-700">Mehrsprachig (10+ Sprachen)</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-green-500 text-xl">✓</span>
                        <span class="text-gray-700">Custom Domain Support</span>
                    </div>
                </div>
            </div>

            
            <div class="mt-12 p-6 bg-blue-50 rounded-xl border-2 border-blue-200">
                <p class="text-lg font-semibold text-blue-900 mb-2">Demo Tenant verfügbar:</p>
                <a href="http://testclub.localhost:8000"
                   class="text-blue-600 hover:text-blue-800 font-mono text-lg underline"
                   target="_blank">
                    http://testclub.localhost:8000
                </a>
                <p class="text-sm text-gray-600 mt-2">
                    Login: admin@testclub.com / password
                </p>
            </div>
        </div>
    </div>

    
    <footer class="bg-white border-t border-gray-200 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <p class="text-center text-gray-500">
                © <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?> - Powered by Laravel <?php echo e(app()->version()); ?> & Filament
            </p>
        </div>
    </footer>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\central\home.blade.php ENDPATH**/ ?>