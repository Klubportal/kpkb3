<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verein Registrieren - Klubportal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8 bg-white p-8 rounded-lg shadow-lg">
            <div>
                <h2 class="text-center text-3xl font-extrabold text-gray-900">
                    Verein Registrieren
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Erstelle deine eigene Vereinswebsite in wenigen Minuten
                </p>
            </div>

            <form method="POST" action="{{ route('tenant.register.store') }}" class="mt-8 space-y-6">
                @csrf

                {{-- Verein Informationen --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Verein Informationen</h3>

                    <div>
                        <label for="club_name" class="block text-sm font-medium text-gray-700">
                            Vereinsname *
                        </label>
                        <input type="text" name="club_name" id="club_name" required
                               value="{{ old('club_name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        @error('club_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                E-Mail *
                            </label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                Telefon
                            </label>
                            <input type="text" name="phone" id="phone"
                                   value="{{ old('phone') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-gray-700">
                            Subdomain *
                        </label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="subdomain" id="subdomain" required
                                   value="{{ old('subdomain') }}"
                                   class="flex-1 rounded-l-md border-gray-300 focus:border-primary focus:ring-primary">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                .klubportal.com
                            </span>
                        </div>
                        @error('subdomain')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Admin Zugangsdaten --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Admin Zugangsdaten</h3>

                    <div>
                        <label for="admin_name" class="block text-sm font-medium text-gray-700">
                            Admin Name *
                        </label>
                        <input type="text" name="admin_name" id="admin_name" required
                               value="{{ old('admin_name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700">
                            Admin E-Mail *
                        </label>
                        <input type="email" name="admin_email" id="admin_email" required
                               value="{{ old('admin_email') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Passwort *
                            </label>
                            <input type="password" name="password" id="password" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Passwort bestätigen *
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Plan Auswahl --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Plan Auswahl</h3>

                    <div class="grid grid-cols-3 gap-4">
                        <label class="relative flex flex-col p-4 border rounded-lg cursor-pointer hover:border-primary">
                            <input type="radio" name="plan" value="trial" checked class="sr-only peer">
                            <span class="text-lg font-semibold">Trial</span>
                            <span class="text-sm text-gray-600">14 Tage kostenlos</span>
                            <span class="mt-2 text-2xl font-bold">€0</span>
                            <span class="absolute inset-0 border-2 border-transparent peer-checked:border-primary rounded-lg"></span>
                        </label>

                        <label class="relative flex flex-col p-4 border rounded-lg cursor-pointer hover:border-primary">
                            <input type="radio" name="plan" value="basic" class="sr-only peer">
                            <span class="text-lg font-semibold">Basic</span>
                            <span class="text-sm text-gray-600">Pro Monat</span>
                            <span class="mt-2 text-2xl font-bold">€19</span>
                            <span class="absolute inset-0 border-2 border-transparent peer-checked:border-primary rounded-lg"></span>
                        </label>

                        <label class="relative flex flex-col p-4 border rounded-lg cursor-pointer hover:border-primary">
                            <input type="radio" name="plan" value="premium" class="sr-only peer">
                            <span class="text-lg font-semibold">Premium</span>
                            <span class="text-sm text-gray-600">Pro Monat</span>
                            <span class="mt-2 text-2xl font-bold">€49</span>
                            <span class="absolute inset-0 border-2 border-transparent peer-checked:border-primary rounded-lg"></span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Verein Erstellen
                </button>
            </form>
        </div>
    </div>
</body>
</html>
