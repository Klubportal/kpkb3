<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Klubportal - Vereinsverwaltung für Fußballvereine')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #1e40af;
            --secondary: #dc2626;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100">
    @include('central-frontend.partials.navbar')

    @yield('hero')

    <main class="py-12">
        <div class="container mx-auto px-4">
            @yield('content')
        </div>
    </main>

    @include('central-frontend.partials.footer')
</body>
</html>
