<?php

/**
 * 🌱 SEEDING TRENNUNG - Demo Script
 *
 * Demonstriert wie Seeders für Central vs Tenant getrennt sind
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\File;

echo "\n";
echo "========================================\n";
echo "   SEEDING STRUKTUR DEMONSTRATION\n";
echo "========================================\n\n";

echo "📁 SEEDER ORDNERSTRUKTUR:\n\n";

$seederPath = database_path('seeders');
$files = File::files($seederPath);

echo "database/seeders/\n";
echo "├── Central Seeders (laufen auf Central DB):\n";

$centralSeeders = [
    'DatabaseSeeder.php' => 'Master Seeder - ruft alle Central Seeders auf',
    'PlansSeeder.php' => 'Subscription Plans (Free, Basic, Pro, Enterprise)',
    'MichaelSuperAdminSeeder.php' => 'Super Admin User für Central Panel',
    'TenantSeeder.php' => 'Erstellt Tenants (Vereine)',
    'CmsSeeder.php' => 'Central CMS Daten (News, Pages)',
    'RolesAndPermissionsSeeder.php' => 'Rollen & Permissions (Shield/Spatie)',
];

foreach ($centralSeeders as $file => $description) {
    $exists = File::exists($seederPath . '/' . $file) ? '✅' : '❌';
    echo "│   {$exists} {$file}\n";
    echo "│      → {$description}\n";
}

echo "│\n";
echo "└── Tenant Seeders (laufen auf Tenant DB):\n";
echo "    ⚠️  Aktuell noch im Hauptordner\n";
echo "    💡 Empfehlung: Eigener Ordner database/seeders/tenant/\n\n";

echo "========================================\n";
echo "   SEEDING FLOW\n";
echo "========================================\n\n";

echo "1️⃣  CENTRAL SEEDING (einmalig bei Installation):\n\n";

echo "   php artisan db:seed\n";
echo "   ↓\n";
echo "   DatabaseSeeder.php\n";
echo "   ├── PlansSeeder → Pläne erstellen\n";
echo "   ├── MichaelSuperAdminSeeder → Super Admin\n";
echo "   ├── TenantSeeder → Tenants erstellen\n";
echo "   └── CmsSeeder → Central CMS Daten\n\n";

echo "   DB: kpkb3 (Central)\n";
echo "   Tabellen: users, tenants, domains, plans, news, pages\n\n";

echo "2️⃣  TENANT SEEDING (für jeden Tenant):\n\n";

echo "   php artisan tenants:seed\n";
echo "   ↓\n";
echo "   TenantDatabaseSeeder.php (erstellen!)\n";
echo "   ├── PlayerSeeder → Spieler\n";
echo "   ├── TeamSeeder → Mannschaften\n";
echo "   ├── MatchSeeder → Spiele\n";
echo "   ├── NewsSeeder → Tenant News\n";
echo "   └── EventSeeder → Events\n\n";

echo "   DB: tenant_testclub, tenant_liverpool, etc.\n";
echo "   Tabellen: players, teams, matches, news, events\n\n";

echo "========================================\n";
echo "   CENTRAL VS TENANT SEEDERS\n";
echo "========================================\n\n";

echo "📊 CENTRAL SEEDERS:\n\n";

echo "1. DatabaseSeeder.php\n";
echo "   → Master Seeder\n";
echo "   → Ruft alle Central Seeders auf\n";
echo "   → Läuft auf: Central DB\n\n";

echo "<?php\n";
echo "class DatabaseSeeder extends Seeder\n";
echo "{\n";
echo "    public function run(): void\n";
echo "    {\n";
echo "        \$this->call([\n";
echo "            PlansSeeder::class,\n";
echo "            MichaelSuperAdminSeeder::class,\n";
echo "            TenantSeeder::class,\n";
echo "            CmsSeeder::class,\n";
echo "        ]);\n";
echo "    }\n";
echo "}\n\n";

echo "2. PlansSeeder.php\n";
echo "   → Subscription Plans\n";
echo "   → Models: App\\Models\\Central\\Plan\n";
echo "   → Connection: central\n\n";

echo "3. TenantSeeder.php\n";
echo "   → Erstellt Tenants & Domains\n";
echo "   → Models: App\\Models\\Central\\Tenant\n";
echo "   → Connection: central\n\n";

echo "🏢 TENANT SEEDERS:\n\n";

echo "1. TenantDatabaseSeeder.php (zu erstellen)\n";
echo "   → Master Seeder für Tenants\n";
echo "   → Läuft auf: Tenant DBs\n\n";

echo "<?php\n";
echo "class TenantDatabaseSeeder extends Seeder\n";
echo "{\n";
echo "    public function run(): void\n";
echo "    {\n";
echo "        \$this->call([\n";
echo "            PlayerSeeder::class,\n";
echo "            TeamSeeder::class,\n";
echo "            MatchSeeder::class,\n";
echo "            TenantNewsSeeder::class,\n";
echo "        ]);\n";
echo "    }\n";
echo "}\n\n";

echo "2. PlayerSeeder.php\n";
echo "   → Spieler Daten\n";
echo "   → Models: App\\Models\\Tenant\\Player\n";
echo "   → Connection: tenant (dynamisch)\n\n";

echo "========================================\n";
echo "   EMPFOHLENE STRUKTUR\n";
echo "========================================\n\n";

echo "database/\n";
echo "├── seeders/\n";
echo "│   ├── DatabaseSeeder.php          ← Central Master\n";
echo "│   ├── PlansSeeder.php             ← Central\n";
echo "│   ├── MichaelSuperAdminSeeder.php ← Central\n";
echo "│   ├── TenantSeeder.php            ← Central (erstellt Tenants)\n";
echo "│   ├── CmsSeeder.php               ← Central\n";
echo "│   │\n";
echo "│   └── tenant/                     ← NEUER ORDNER\n";
echo "│       ├── TenantDatabaseSeeder.php\n";
echo "│       ├── PlayerSeeder.php\n";
echo "│       ├── TeamSeeder.php\n";
echo "│       ├── MatchSeeder.php\n";
echo "│       └── TenantNewsSeeder.php\n";
echo "│\n";
echo "├── migrations/\n";
echo "│   ├── (Central Migrations)\n";
echo "│   └── tenant/ ← Tenant Migrations\n";
echo "│\n";
echo "└── factories/\n";
echo "    └── UserFactory.php\n\n";

echo "========================================\n";
echo "   BEFEHLE\n";
echo "========================================\n\n";

echo "# Central DB seeden (einmalig)\n";
echo "php artisan db:seed\n\n";

echo "# Spezifischen Central Seeder\n";
echo "php artisan db:seed --class=PlansSeeder\n\n";

echo "# Alle Tenants seeden\n";
echo "php artisan tenants:seed\n\n";

echo "# Spezifischen Tenant seeden\n";
echo "php artisan tenants:seed --tenants=testclub\n\n";

echo "# Tenant mit spezifischem Seeder\n";
echo "php artisan tenants:seed --tenants=testclub --class=PlayerSeeder\n\n";

echo "# Fresh + Seed (⚠️ LÖSCHT ALLES!)\n";
echo "php artisan migrate:fresh --seed  # Central\n";
echo "php artisan tenants:migrate-fresh --seed  # Alle Tenants\n\n";

echo "========================================\n";
echo "   DEMO SEEDERS ERSTELLEN\n";
echo "========================================\n\n";

echo "# Central Seeder\n";
echo "php artisan make:seeder CentralNewsSeeder\n\n";

echo "# Tenant Seeder (Ordner manuell erstellen)\n";
echo "php artisan make:seeder PlayerSeeder\n";
echo "# Dann verschieben nach: database/seeders/tenant/\n\n";

echo "========================================\n";
echo "   CODE BEISPIELE\n";
echo "========================================\n\n";

echo "📝 CENTRAL SEEDER BEISPIEL:\n\n";

echo "<?php\n";
echo "// database/seeders/PlansSeeder.php\n\n";

echo "use App\\Models\\Central\\Plan;\n";
echo "use Illuminate\\Database\\Seeder;\n\n";

echo "class PlansSeeder extends Seeder\n";
echo "{\n";
echo "    public function run(): void\n";
echo "    {\n";
echo "        // WICHTIG: Explizit Central Connection\n";
echo "        Plan::on('central')->create([\n";
echo "            'name' => 'Free',\n";
echo "            'price' => 0,\n";
echo "            'features' => ['basic_features'],\n";
echo "        ]);\n\n";

echo "        Plan::on('central')->create([\n";
echo "            'name' => 'Pro',\n";
echo "            'price' => 49,\n";
echo "            'features' => ['all_features'],\n";
echo "        ]);\n";
echo "    }\n";
echo "}\n\n";

echo "📝 TENANT SEEDER BEISPIEL:\n\n";

echo "<?php\n";
echo "// database/seeders/tenant/PlayerSeeder.php\n\n";

echo "namespace Database\\Seeders\\Tenant;\n\n";

echo "use App\\Models\\Tenant\\Player;\n";
echo "use App\\Models\\Tenant\\Team;\n";
echo "use Illuminate\\Database\\Seeder;\n\n";

echo "class PlayerSeeder extends Seeder\n";
echo "{\n";
echo "    public function run(): void\n";
echo "    {\n";
echo "        // Läuft automatisch im Tenant Context!\n";
echo "        // KEINE explizite Connection nötig\n\n";

echo "        \$team = Team::first();\n\n";

echo "        Player::create([\n";
echo "            'team_id' => \$team->id,\n";
echo "            'name' => 'Max Mustermann',\n";
echo "            'position' => 'Stürmer',\n";
echo "            'number' => 10,\n";
echo "        ]);\n\n";

echo "        // Oder mit Factory\n";
echo "        Player::factory()->count(20)->create();\n";
echo "    }\n";
echo "}\n\n";

echo "========================================\n";
echo "   NAMESPACE FÜR TENANT SEEDERS\n";
echo "========================================\n\n";

echo "⚠️  WICHTIG: Namespace anpassen!\n\n";

echo "// Tenant Seeder im Ordner database/seeders/tenant/\n";
echo "namespace Database\\Seeders\\Tenant;  // ← Beachte Namespace!\n\n";

echo "use Illuminate\\Database\\Seeder;\n\n";

echo "class PlayerSeeder extends Seeder { ... }\n\n";

echo "// Aufruf im TenantDatabaseSeeder:\n";
echo "\$this->call([\n";
echo "    \\Database\\Seeders\\Tenant\\PlayerSeeder::class,  // Voller Namespace\n";
echo "]);\n\n";

echo "========================================\n";
echo "   BEST PRACTICES\n";
echo "========================================\n\n";

echo "✅ DO - EMPFOHLEN:\n\n";

echo "1. Separate Ordner:\n";
echo "   database/seeders/ → Central\n";
echo "   database/seeders/tenant/ → Tenant\n\n";

echo "2. Model::on('central') für Central Seeders:\n";
echo "   Plan::on('central')->create([...])\n\n";

echo "3. Keine explizite Connection für Tenant Seeders:\n";
echo "   // Läuft automatisch im Tenant Context\n";
echo "   Player::create([...])  // ✅ Richtig\n\n";

echo "4. Factories verwenden:\n";
echo "   Player::factory()->count(50)->create()\n\n";

echo "5. Idempotent Seeders:\n";
echo "   Plan::firstOrCreate(['name' => 'Free'], [...])\n\n";

echo "❌ DON'T - VERMEIDEN:\n\n";

echo "1. NICHT: Central Models ohne Connection:\n";
echo "   Plan::create([...])  // ❌ Könnte falsche DB sein\n\n";

echo "2. NICHT: Tenant Models mit Central Connection:\n";
echo "   Player::on('central')->create([...])  // ❌ Falsch!\n\n";

echo "3. NICHT: Gemischte Seeders:\n";
echo "   // Ein Seeder für Central UND Tenant ❌\n\n";

echo "4. NICHT: Hardcoded Tenant IDs:\n";
echo "   // In Tenant Seeders\n";
echo "   Player::where('tenant_id', 'testclub')  // ❌ Unnötig\n\n";

echo "========================================\n";
echo "   TROUBLESHOOTING\n";
echo "========================================\n\n";

echo "❌ PROBLEM: Seeder läuft auf falscher DB\n";
echo "✅ LÖSUNG:\n";
echo "   Central: Model::on('central')->create()\n";
echo "   Tenant: Kein on() - läuft automatisch im Tenant Context\n\n";

echo "❌ PROBLEM: Class not found\n";
echo "✅ LÖSUNG:\n";
echo "   1. Namespace prüfen (Database\\Seeders\\Tenant)\n";
echo "   2. composer dump-autoload\n";
echo "   3. Voller Namespace im call(): \\Database\\Seeders\\Tenant\\PlayerSeeder::class\n\n";

echo "❌ PROBLEM: tenants:seed findet TenantDatabaseSeeder nicht\n";
echo "✅ LÖSUNG:\n";
echo "   php artisan tenants:seed --class=Database\\Seeders\\Tenant\\TenantDatabaseSeeder\n\n";

echo "========================================\n";
echo "   AKTUELLER STATUS\n";
echo "========================================\n\n";

echo "✅ VORHANDEN:\n";
foreach ($centralSeeders as $file => $description) {
    $exists = File::exists($seederPath . '/' . $file);
    if ($exists) {
        echo "   ✅ {$file}\n";
    }
}

echo "\n⚠️  FEHLT (empfohlen zu erstellen):\n";
echo "   📁 database/seeders/tenant/ (Ordner)\n";
echo "   📄 TenantDatabaseSeeder.php\n";
echo "   📄 PlayerSeeder.php\n";
echo "   📄 TeamSeeder.php\n";
echo "   📄 MatchSeeder.php\n";
echo "   📄 TenantNewsSeeder.php\n\n";

echo "========================================\n";
echo "   NÄCHSTE SCHRITTE\n";
echo "========================================\n\n";

echo "1. Ordner erstellen:\n";
echo "   mkdir database\\seeders\\tenant\n\n";

echo "2. Tenant Master Seeder erstellen:\n";
echo "   php artisan make:seeder TenantDatabaseSeeder\n";
echo "   # Verschieben nach database/seeders/tenant/\n\n";

echo "3. Tenant Seeders erstellen:\n";
echo "   php artisan make:seeder PlayerSeeder\n";
echo "   php artisan make:seeder TeamSeeder\n";
echo "   # Verschieben nach database/seeders/tenant/\n\n";

echo "4. Namespace in Tenant Seeders anpassen:\n";
echo "   namespace Database\\Seeders\\Tenant;\n\n";

echo "5. TenantDatabaseSeeder füllen:\n";
echo "   \$this->call([\n";
echo "       \\Database\\Seeders\\Tenant\\PlayerSeeder::class,\n";
echo "   ]);\n\n";

echo "6. Tenants seeden:\n";
echo "   php artisan tenants:seed --class=Database\\Seeders\\Tenant\\TenantDatabaseSeeder\n\n";

echo "========================================\n\n";

echo "💡 Seeding Struktur dokumentiert!\n";
echo "📁 Siehe: database/seeders/\n\n";
