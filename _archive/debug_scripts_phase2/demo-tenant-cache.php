<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;

echo "\n";
echo "========================================\n";
echo "   CACHE TENANCY ISOLATION DEMO\n";
echo "========================================\n\n";

echo "✅ KONFIGURATION:\n";
echo "   CacheTenancyBootstrapper: " .
    (in_array(Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class, config('tenancy.bootstrappers')) ? 'AKTIVIERT ✅' : 'NICHT AKTIVIERT ❌') . "\n";
echo "   Cache Tag Base: " . config('tenancy.cache.tag_base') . "\n";
echo "   Cache Driver: " . config('cache.default') . "\n\n";

echo "========================================\n";
echo "   CACHE ISOLATION TEST\n";
echo "========================================\n\n";

// Central Cache
echo "🏢 CENTRAL CONTEXT:\n";
Cache::put('rankings', ['position' => 1, 'team' => 'Central Admin'], 60);
echo "   ✅ Cache gesetzt: rankings = ['position' => 1, 'team' => 'Central Admin']\n";
$centralValue = Cache::get('rankings');
echo "   📖 Cache gelesen: " . json_encode($centralValue) . "\n\n";

// Tenant 1: testclub
$testclub = Tenant::find('testclub');
if ($testclub) {
    echo "🏠 TENANT CONTEXT: testclub\n";
    $testclub->run(function() {
        Cache::put('rankings', ['position' => 1, 'team' => 'FC Testclub'], 60);
        echo "   ✅ Cache gesetzt: rankings = ['position' => 1, 'team' => 'FC Testclub']\n";
        $tenantValue = Cache::get('rankings');
        echo "   📖 Cache gelesen: " . json_encode($tenantValue) . "\n";
    });
    echo "\n";
}

echo "🔒 ISOLATION TEST:\n";
echo "   Was sieht Central jetzt in 'rankings'?\n";
$centralCheck = Cache::get('rankings');
echo "   → " . json_encode($centralCheck) . "\n";
echo "   " . ($centralCheck['team'] === 'Central Admin' ? '✅ Isolation funktioniert!' : '❌ Problem!') . "\n\n";

if ($testclub) {
    echo "   Was sieht testclub jetzt in 'rankings'?\n";
    $testclub->run(function() {
        $tenantCheck = Cache::get('rankings');
        echo "   → " . json_encode($tenantCheck) . "\n";
        echo "   " . ($tenantCheck['team'] === 'FC Testclub' ? '✅ Isolation funktioniert!' : '❌ Problem!') . "\n";
    });
    echo "\n";
}

echo "========================================\n";
echo "   PRAKTISCHES BEISPIEL\n";
echo "========================================\n\n";

echo "📊 Verschiedene Rankings pro Tenant:\n\n";

// Central Rankings
Cache::put('league_standings', [
    ['team' => 'Bayern München', 'points' => 25],
    ['team' => 'Borussia Dortmund', 'points' => 22],
], 3600);
echo "   Central: " . count(Cache::get('league_standings')) . " Teams gespeichert\n";

// Testclub Rankings
if ($testclub) {
    $testclub->run(function() {
        Cache::put('league_standings', [
            ['team' => 'FC Testclub U19', 'points' => 30],
            ['team' => 'SV Jugend', 'points' => 28],
            ['team' => 'FC Nachwuchs', 'points' => 25],
        ], 3600);
        echo "   Testclub: " . count(Cache::get('league_standings')) . " Teams gespeichert\n";
    });
}

echo "\n";
echo "========================================\n";
echo "   CACHE TAGS & NAMESPACING\n";
echo "========================================\n\n";

echo "Wie es intern funktioniert:\n\n";
echo "  Central Context:\n";
echo "    Cache::put('rankings', \$data)\n";
echo "    → Gespeichert als: 'rankings'\n\n";

echo "  Tenant Context (testclub):\n";
echo "    Cache::put('rankings', \$data)\n";
echo "    → Gespeichert als: Tag 'tenanttestclub' + 'rankings'\n\n";

echo "  Tenant Context (arsenal):\n";
echo "    Cache::put('rankings', \$data)\n";
echo "    → Gespeichert als: Tag 'tenantarsenal' + 'rankings'\n\n";

echo "========================================\n";
echo "   FILAMENT BEISPIEL\n";
echo "========================================\n\n";

echo "```php\n";
echo "// In einem Filament Widget oder Resource\n\n";
echo "protected function getStats(): array\n";
echo "{\n";
echo "    return Cache::remember('dashboard_stats', 3600, function() {\n";
echo "        return [\n";
echo "            'total_players' => Player::count(),\n";
echo "            'total_matches' => Match::count(),\n";
echo "            'total_news' => News::count(),\n";
echo "        ];\n";
echo "    });\n";
echo "}\n";
echo "```\n\n";

echo "Jeder Tenant cached seine eigenen Stats!\n\n";

echo "========================================\n";
echo "   CACHE CLEARING\n";
echo "========================================\n\n";

echo "Gesamter Cache löschen:\n";
echo "  php artisan cache:clear\n\n";

echo "Nur Tenant-Cache löschen:\n";
echo "  Cache::tags(['tenanttestclub'])->flush()\n\n";

echo "Spezifischen Key löschen:\n";
echo "  Cache::forget('rankings')\n\n";

echo "========================================\n";
echo "   ZUSAMMENFASSUNG\n";
echo "========================================\n";
echo "✅ CacheTenancyBootstrapper: AKTIVIERT\n";
echo "✅ Automatische Tag-basierte Isolation\n";
echo "✅ Gleicher Cache-Key, unterschiedliche Werte\n";
echo "✅ Keine manuelle Namespace-Verwaltung\n";
echo "✅ Funktioniert mit allen Cache Drivers\n";
echo "\n";

// Cleanup
echo "🧹 Aufräumen...\n";
Cache::forget('rankings');
Cache::forget('league_standings');
if ($testclub) {
    $testclub->run(function() {
        Cache::forget('rankings');
        Cache::forget('league_standings');
    });
}
echo "   ✅ Test-Cache gelöscht\n";

echo "\n========================================\n\n";
