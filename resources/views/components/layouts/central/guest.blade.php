<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Klubportal - Die moderne Vereinsverwaltung' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #dc2626;
            --secondary: #1f2937;
            --accent: #2563eb;
        }
    </style>
</head>
<body class="antialiased bg-base-100" x-data="{ mobileMenu: false }">

    {{-- Navigation --}}
    <nav class="navbar bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto">
            <div class="flex-1">
                <a href="/" class="btn btn-ghost text-xl">
                    <img src="{{ asset('images/logo.svg') }}" alt="Klubportal" class="h-10">
                    <span class="ml-2 font-bold text-primary">Klubportal</span>
                </a>
            </div>

            {{-- Desktop Menu --}}
            <div class="hidden lg:flex gap-2">
                <a href="#features" class="btn btn-ghost">Features</a>
                <a href="#news" class="btn btn-ghost">News</a>
                <a href="#pricing" class="btn btn-ghost">Preise</a>
                <a href="#contact" class="btn btn-ghost">Kontakt</a>
                <a href="/super-admin/login" class="btn btn-ghost">Login</a>
                <a href="#register" class="btn btn-primary">Verein registrieren</a>
            </div>

            {{-- Mobile Menu Button --}}
            <div class="lg:hidden">
                <button @click="mobileMenu = !mobileMenu" class="btn btn-ghost btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" x-cloak class="lg:hidden">
            <div class="bg-base-200 p-4 space-y-2">
                <a href="#features" class="btn btn-ghost btn-block justify-start">Features</a>
                <a href="#news" class="btn btn-ghost btn-block justify-start">News</a>
                <a href="#pricing" class="btn btn-ghost btn-block justify-start">Preise</a>
                <a href="#contact" class="btn btn-ghost btn-block justify-start">Kontakt</a>
                <a href="/super-admin/login" class="btn btn-ghost btn-block justify-start">Login</a>
                <a href="#register" class="btn btn-primary btn-block">Verein registrieren</a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-secondary text-white mt-20">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- About --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Über Klubportal</h3>
                    <p class="text-sm opacity-80">
                        Die moderne All-in-One Lösung für Vereinsverwaltung, Spielerverwaltung und Öffentlichkeitsarbeit.
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm opacity-80">
                        <li><a href="#features" class="hover:text-primary">Features</a></li>
                        <li><a href="#pricing" class="hover:text-primary">Preise</a></li>
                        <li><a href="#news" class="hover:text-primary">News</a></li>
                        <li><a href="#contact" class="hover:text-primary">Kontakt</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Rechtliches</h3>
                    <ul class="space-y-2 text-sm opacity-80">
                        <li><a href="#" class="hover:text-primary">Impressum</a></li>
                        <li><a href="#" class="hover:text-primary">Datenschutz</a></li>
                        <li><a href="#" class="hover:text-primary">AGB</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="text-lg font-bold mb-4">Kontakt</h3>
                    <ul class="space-y-2 text-sm opacity-80">
                        <li>E-Mail: info@klubportal.com</li>
                        <li>Tel: +49 123 456789</li>
                        <li class="pt-2">
                            <div class="flex gap-3">
                                <a href="#" class="hover:text-primary"><i class="fab fa-facebook"></i></a>
                                <a href="#" class="hover:text-primary"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="hover:text-primary"><i class="fab fa-instagram"></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="border-t border-white/20 mt-8 pt-8 text-center text-sm opacity-60">
                <p>&copy; {{ date('Y') }} Klubportal. Alle Rechte vorbehalten.</p>
            </div>
        </div>
    </footer>

    {{-- Back to Top Button --}}
    <button
        onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
        class="btn btn-circle btn-primary fixed bottom-8 right-8 shadow-lg hidden"
        x-data="{ show: false }"
        @scroll.window="show = window.pageYOffset > 300"
        x-show="show"
        x-cloak
    >
        ↑
    </button>

</body>
</html>
