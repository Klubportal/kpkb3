<!DOCTYPE html>
<html>
<head>
    <title>Translation Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .debug { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .debug h2 { color: #dc2626; margin-top: 0; }
        .ok { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .lang-switcher { margin: 20px 0; }
        .lang-switcher a { display: inline-block; margin: 5px; padding: 10px 20px; background: #dc2626; color: white; text-decoration: none; border-radius: 4px; }
        .lang-switcher a.active { background: #991b1b; }
    </style>
</head>
<body>
    <h1>🔍 Translation Debug Page</h1>

    <div class="lang-switcher">
        <strong>Switch Language:</strong><br>
        <a href="/language/de" class="{{ app()->getLocale() === 'de' ? 'active' : '' }}">🇩🇪 Deutsch</a>
        <a href="/language/en" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">🇬🇧 English</a>
        <a href="/language/hr" class="{{ app()->getLocale() === 'hr' ? 'active' : '' }}">🇭🇷 Hrvatski</a>
    </div>

    <div class="debug">
        <h2>📍 Current Locale</h2>
        <p><strong>App Locale:</strong> <span class="ok">{{ app()->getLocale() }}</span></p>
        <p><strong>Session Locale:</strong> <span class="{{ session('locale') ? 'ok' : 'error' }}">{{ session('locale') ?? 'NOT SET' }}</span></p>
        <p><strong>Config Locale:</strong> {{ config('app.locale') }}</p>
        <p><strong>Fallback Locale:</strong> {{ config('app.fallback_locale') }}</p>
    </div>

    <div class="debug">
        <h2>🔤 Translation Tests</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f5f5f5; font-weight: bold;">
                <td style="padding: 8px; border: 1px solid #ddd;">Key</td>
                <td style="padding: 8px; border: 1px solid #ddd;">Translation</td>
            </tr>
            @php
                $testKeys = [
                    'site.description',
                    'nav.features',
                    'nav.news',
                    'nav.pricing',
                    'nav.contact',
                    'nav.login',
                    'nav.register_club',
                    'hero.start_free',
                    'hero.learn_more',
                ];
            @endphp
            @foreach($testKeys as $key)
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><code>{{ $key }}</code></td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ __($key) }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="debug">
        <h2>🗄️ Database Check</h2>
        @php
            $translations = DB::connection('central')->table('language_lines')->get();
        @endphp
        <p><strong>Total Translations in DB:</strong> {{ $translations->count() }}</p>
        <p><strong>Groups:</strong> {{ $translations->pluck('group')->unique()->implode(', ') }}</p>
    </div>

    <div class="debug">
        <h2>🎯 Expected Results</h2>
        <p>When locale is <strong>hr</strong>, you should see:</p>
        <ul>
            <li>site.description: <strong>Tvoja platforma za upravljanje nogometnim klubom</strong></li>
            <li>nav.features: <strong>Značajke</strong></li>
            <li>nav.news: <strong>Vijesti</strong></li>
            <li>hero.start_free: <strong>Počni besplatno sada</strong></li>
        </ul>
    </div>

    <div class="debug">
        <h2>🔗 Quick Links</h2>
        <p><a href="/landing" style="color: #dc2626;">Go to Landing Page →</a></p>
        <p><a href="/super-admin/login" style="color: #dc2626;">Go to Admin Login →</a></p>
    </div>
</body>
</html>
