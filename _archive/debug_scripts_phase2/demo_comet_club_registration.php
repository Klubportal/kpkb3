<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🏢 COMET Club Registration - Demo\n";
echo str_repeat("═", 80) . "\n\n";

echo "Dieses Script demonstriert die automatische COMET Club-Erstellung\n";
echo "bei der Tenant-Registrierung nach FIFA-Vorgaben.\n\n";

// Beispiel: NK Prigorje
$clubData = [
    'id' => 'nk-prigorje-test',
    'name' => 'NK Prigorje Markuševec',
    'email' => 'info@nk-prigorje.hr',
    'phone' => '+385 1 234 5678',
    'data' => [
        // FIFA/COMET IDs (PFLICHT für COMET Integration)
        'club_fifa_id' => 100598001,  // Fiktive FIFA ID
        'organisation_fifa_id' => 598, // HNS Organisation ID

        // Club Details
        'country_code' => 'HRV', // ISO 3166-1 alpha-3
        'city' => 'Zagreb',
        'founded_year' => 1929,
        'logo_url' => 'https://example.com/nk-prigorje-logo.png',
        'website' => 'https://nk-prigorje.hr',

        // Admin User (wird von CreateDefaultAdminUser Job verwendet)
        'admin_name' => 'Club Administrator',
        'admin_email' => 'admin@nk-prigorje.hr',
        'admin_password' => bcrypt('password123'),
    ]
];

echo "📋 Tenant-Daten:\n";
echo str_repeat("─", 80) . "\n";
echo "  ID:               {$clubData['id']}\n";
echo "  Name:             {$clubData['name']}\n";
echo "  Email:            {$clubData['email']}\n";
echo "  Club FIFA ID:     {$clubData['data']['club_fifa_id']}\n";
echo "  Organisation ID:  {$clubData['data']['organisation_fifa_id']}\n";
echo "  Country:          {$clubData['data']['country_code']}\n";
echo "  City:             {$clubData['data']['city']}\n";
echo "\n";

$choice = readline("Möchten Sie diesen Test-Tenant erstellen? (ja/nein): ");

if (strtolower(trim($choice)) !== 'ja') {
    echo "\n❌ Abgebrochen.\n\n";
    exit(0);
}

echo "\n";
echo str_repeat("═", 80) . "\n";
echo "🚀 Starte Tenant-Erstellung...\n";
echo str_repeat("═", 80) . "\n\n";

DB::beginTransaction();

try {
    // 1. Prüfe ob Tenant bereits existiert
    if (Tenant::find($clubData['id'])) {
        echo "⚠️  Tenant '{$clubData['id']}' existiert bereits. Lösche zuerst...\n";
        $oldTenant = Tenant::find($clubData['id']);
        $oldTenant->delete();
        echo "✅ Alter Tenant gelöscht\n\n";
    }

    // 2. Erstelle Tenant
    echo "1️⃣  Erstelle Tenant...\n";
    $tenant = Tenant::create($clubData);
    echo "   ✅ Tenant erstellt: {$tenant->id}\n\n";

    // 3. Erstelle Domain
    echo "2️⃣  Erstelle Domain...\n";
    $tenant->domains()->create([
        'domain' => $clubData['id'] . '.localhost'
    ]);
    echo "   ✅ Domain erstellt: {$clubData['id']}.localhost\n\n";

    // Die folgenden Schritte laufen automatisch durch TenancyServiceProvider Pipeline:
    echo "3️⃣  Automatische Pipeline läuft...\n";
    echo "   ⏳ CreateDatabase\n";
    echo "   ⏳ MigrateDatabase\n";
    echo "   ⏳ SeedDatabase\n";
    echo "   ⏳ CreateDefaultClubSettings\n";
    echo "   ⏳ CreateDefaultAdminUser\n";
    echo "   ⏳ CreateCometClubRecord (NEU!)\n\n";

    // Warte kurz für Pipeline
    sleep(2);

    DB::commit();

    echo str_repeat("═", 80) . "\n";
    echo "✅ Tenant erfolgreich erstellt!\n";
    echo str_repeat("═", 80) . "\n\n";

    // 4. Prüfe COMET Club Record
    echo "4️⃣  Prüfe COMET Club Record in kpkb3...\n";
    echo str_repeat("─", 80) . "\n";

    $club = DB::connection('central')
        ->table('comet_clubs_extended')
        ->where('comet_id', $clubData['data']['club_fifa_id'])
        ->first();

    if ($club) {
        echo "   ✅ COMET Club gefunden!\n";
        echo "      - Name:              {$club->name}\n";
        echo "      - Short Name:        {$club->short_name}\n";
        echo "      - FIFA ID:           {$club->comet_id}\n";
        echo "      - Organisation ID:   {$club->organisation_fifa_id}\n";
        echo "      - Country:           {$club->country_code}\n";
        echo "      - City:              {$club->city}\n";
        echo "      - Tenant ID:         {$club->tenant_id}\n";
        echo "      - Status:            {$club->status}\n";
    } else {
        echo "   ⚠️  COMET Club nicht gefunden (Job läuft möglicherweise noch)\n";
    }

    echo "\n";
    echo str_repeat("═", 80) . "\n";
    echo "📊 Zusammenfassung\n";
    echo str_repeat("═", 80) . "\n\n";

    // Tenant Info
    $tenant = Tenant::find($clubData['id']);
    echo "Tenant:\n";
    echo "  - ID:        {$tenant->id}\n";
    echo "  - Name:      {$tenant->name}\n";
    echo "  - Email:     {$tenant->email}\n";
    echo "  - Database:  tenant_{$tenant->id}\n";
    echo "\n";

    // Domain Info
    $domain = $tenant->domains()->first();
    echo "Domain:\n";
    echo "  - URL:       http://{$domain->domain}\n";
    echo "\n";

    // COMET Info
    echo "COMET:\n";
    echo "  - Club FIFA ID:      {$clubData['data']['club_fifa_id']}\n";
    echo "  - Organisation ID:   {$clubData['data']['organisation_fifa_id']}\n";
    echo "  - Zentrale DB:       kpkb3\n";
    echo "  - Tabelle:           comet_clubs_extended\n";
    echo "\n";

    echo str_repeat("═", 80) . "\n";
    echo "🎉 Demo abgeschlossen!\n";
    echo str_repeat("═", 80) . "\n\n";

    echo "💡 Nächste Schritte:\n";
    echo "   1. COMET API Sync implementieren (Spieler, Matches, etc.)\n";
    echo "   2. Daten von kpkb3 → tenant_* DB synchronisieren\n";
    echo "   3. Frontend für Club-Admin erstellen\n";
    echo "\n";

    echo "🌐 Zugriff:\n";
    echo "   URL:      http://{$clubData['id']}.localhost\n";
    echo "   Email:    {$clubData['data']['admin_email']}\n";
    echo "   Password: password123\n";
    echo "\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n";
    echo str_repeat("═", 80) . "\n";
    echo "❌ FEHLER\n";
    echo str_repeat("═", 80) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File:    " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n";
}
