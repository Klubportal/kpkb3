<div>
    @if($submitted || session('registration_success'))
        <!-- Success Message -->
        <div class="rounded-lg bg-green-50 p-6 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-green-800">
                        Registrierung erfolgreich!
                    </h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Vielen Dank für deine Registrierung, <strong>{{ $club_name ?? 'dein Verein' }}</strong>!</p>
                        <p class="mt-2">Wir haben deine Anfrage erhalten und werden sie schnellstmöglich prüfen. Du erhältst eine E-Mail an <strong>{{ $email ?? 'deine E-Mail-Adresse' }}</strong>, sobald dein Vereinsportal freigeschaltet wurde.</p>
                        <p class="mt-2">Deine Subdomain wird sein: <strong class="font-mono">{{ $subdomain ?? 'subdomain' }}.klubportal.com</strong></p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('home') }}" class="text-green-700 hover:text-green-600 font-medium">
                            ← Zurück zur Startseite
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Registration Form -->
        <form wire:submit="submit" class="space-y-6">
            <!-- Club Name -->
            <div>
                <label for="club_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Vereinsname <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="club_name"
                    wire:model.live="club_name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('club_name') border-red-500 @enderror"
                    placeholder="z.B. FC Beispiel"
                    required
                >
                @error('club_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Subdomain -->
            <div>
                <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-2">
                    Subdomain <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center">
                    <input
                        type="text"
                        id="subdomain"
                        wire:model.blur="subdomain"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('subdomain') border-red-500 @enderror"
                        placeholder="fc-beispiel"
                        pattern="[a-z0-9-]+"
                        required
                    >
                    <span class="px-4 py-3 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-600">
                        .klubportal.com
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Nur Kleinbuchstaben, Zahlen und Bindestriche erlaubt</p>
                @error('subdomain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact Person -->
            <div>
                <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">
                    Ansprechpartner <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="contact_person"
                    wire:model="contact_person"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('contact_person') border-red-500 @enderror"
                    placeholder="Max Mustermann"
                    required
                >
                @error('contact_person')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    E-Mail-Adresse <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    wire:model="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                    placeholder="kontakt@fc-beispiel.de"
                    required
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Telefon (optional)
                </label>
                <input
                    type="tel"
                    id="phone"
                    wire:model="phone"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                    placeholder="+49 123 456789"
                >
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Template Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Wähle dein Template <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- KB Template -->
                    <label class="relative cursor-pointer">
                        <input
                            type="radio"
                            wire:model="template"
                            value="kb"
                            class="sr-only peer"
                        >
                        <div class="border-2 border-gray-300 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-200 hover:border-gray-400 transition">
                            <div class="h-32 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg mb-3"></div>
                            <h4 class="font-semibold text-gray-900">KB Template</h4>
                            <p class="text-sm text-gray-600">Modern & Clean</p>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block">
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>

                    <!-- BM Template -->
                    <label class="relative cursor-pointer">
                        <input
                            type="radio"
                            wire:model="template"
                            value="bm"
                            class="sr-only peer"
                        >
                        <div class="border-2 border-gray-300 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-200 hover:border-gray-400 transition">
                            <div class="h-32 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg mb-3"></div>
                            <h4 class="font-semibold text-gray-900">BM Template</h4>
                            <p class="text-sm text-gray-600">Sportlich & Dynamisch</p>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block">
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                </div>
                @error('template')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Terms -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input
                        type="checkbox"
                        id="terms"
                        wire:model="terms"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 @error('terms') border-red-500 @enderror"
                        required
                    >
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms" class="text-gray-700">
                        Ich akzeptiere die <a href="#" class="text-blue-600 hover:text-blue-500">Nutzungsbedingungen</a> und <a href="#" class="text-blue-600 hover:text-blue-500">Datenschutzerklärung</a> <span class="text-red-500">*</span>
                    </label>
                    @error('terms')
                        <p class="mt-1 text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Error -->
            @error('form')
                <div class="rounded-lg bg-red-50 p-4 border border-red-200">
                    <p class="text-sm text-red-600">{{ $message }}</p>
                </div>
            @enderror

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4">
                <p class="text-sm text-gray-500">
                    <span class="text-red-500">*</span> Pflichtfelder
                </p>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center"
                >
                    <span wire:loading.remove>Registrierung absenden</span>
                    <span wire:loading class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Wird gesendet...
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>
