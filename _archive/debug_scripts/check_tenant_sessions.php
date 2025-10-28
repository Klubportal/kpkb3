<?php<?php



require __DIR__ . '/vendor/autoload.php';require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();// Initialisiere Tenancy

$tenant = \App\Models\Central\Tenant::find('testclub');

$tenant = App\Models\Central\Tenant::find('testclub');tenancy()->initialize($tenant);

tenancy()->initialize($tenant);

echo "🔍 Prüfe Tenant Datenbank: tenant_testclub\n\n";

echo "=== RECENT SESSIONS IN TENANT DB ===" . PHP_EOL . PHP_EOL;

// Zeige alle Tabellen

$sessions = DB::connection('tenant')->table('sessions')$tables = DB::select('SHOW TABLES');

    ->orderBy('last_activity', 'desc')$sessionTableExists = false;

    ->take(10)

    ->get();echo "📋 Verfügbare Tabellen:\n";

foreach($tables as $table) {

foreach ($sessions as $session) {    $tableName = array_values((array)$table)[0];

    $lastActivity = date('Y-m-d H:i:s', $session->last_activity);    echo "   - {$tableName}";

    $userId = $session->user_id ?? 'guest';

    $sessionId = substr($session->id, 0, 15);    if (str_contains($tableName, 'session')) {

            echo " ✅ (SESSION TABLE)";

    echo "Session: {$sessionId}..." . PHP_EOL;        $sessionTableExists = true;

    echo "  User: {$userId}" . PHP_EOL;    }

    echo "  IP: {$session->ip_address}" . PHP_EOL;    echo "\n";

    echo "  Last Activity: {$lastActivity}" . PHP_EOL;}

    echo "  User Agent: " . substr($session->user_agent, 0, 50) . "..." . PHP_EOL;

    echo PHP_EOL;echo "\n";

}

if ($sessionTableExists) {

echo "Total Sessions: " . DB::connection('tenant')->table('sessions')->count() . PHP_EOL;    echo "✅ Sessions-Tabelle gefunden\n";

echo "Authenticated Sessions: " . DB::connection('tenant')->table('sessions')->whereNotNull('user_id')->count() . PHP_EOL;

    // Zähle Sessions
    $sessionCount = DB::table('sessions')->count();
    echo "📊 Anzahl Sessions: {$sessionCount}\n";
} else {
    echo "❌ KEINE Sessions-Tabelle gefunden!\n";
    echo "💡 Sessions werden in Central DB gespeichert!\n";
}

// Prüfe DB-Connection
echo "\n🔧 Aktuelle DB Connection:\n";
echo "   Name: " . DB::connection()->getDatabaseName() . "\n";
echo "   Driver: " . config('database.default') . "\n";
