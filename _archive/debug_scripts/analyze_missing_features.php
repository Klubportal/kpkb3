<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔍 Analyzing current database structure for management improvements...\n";
echo "============================================================\n\n";

// 1. Check what we have in LANDLORD
echo "📊 LANDLORD DATABASE (kpkb3):\n";
echo "------------------------------------------------------------\n";

$landlord_tables = $conn->query("SHOW TABLES FROM kpkb3 LIKE 'comet_%'");
echo "Comet Tables:\n";
while ($table = $landlord_tables->fetch_array()) {
    $tableName = $table[0];
    $count = $conn->query("SELECT COUNT(*) as cnt FROM kpkb3.$tableName")->fetch_assoc();
    echo sprintf("  ✅ %-35s | %6d records\n", $tableName, $count['cnt']);
}

echo "\nOther important tables:\n";
$other_tables = ['tenants', 'domains', 'users', 'central_users'];
foreach ($other_tables as $tableName) {
    $exists = $conn->query("SHOW TABLES FROM kpkb3 LIKE '$tableName'");
    if ($exists->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) as cnt FROM kpkb3.$tableName")->fetch_assoc();
        echo sprintf("  ✅ %-35s | %6d records\n", $tableName, $count['cnt']);
    } else {
        echo "  ❌ $tableName - NOT EXISTS\n";
    }
}

// 2. Check what we have in TENANT
echo "\n\n📊 TENANT DATABASE (tenant_nkprigorjem):\n";
echo "------------------------------------------------------------\n";

$tenant_tables = $conn->query("SHOW TABLES FROM tenant_nkprigorjem");
echo "All tables:\n";
while ($table = $tenant_tables->fetch_array()) {
    $tableName = $table[0];
    $count = $conn->query("SELECT COUNT(*) as cnt FROM tenant_nkprigorjem.$tableName")->fetch_assoc();
    echo sprintf("  %-35s | %6d records\n", $tableName, $count['cnt']);
}

echo "\n\n============================================================\n";
echo "💡 RECOMMENDATIONS FOR CENTRAL BACKEND:\n";
echo "============================================================\n\n";

echo "🎯 MISSING FEATURES TO MANAGE:\n\n";

echo "1. SPIELERVERWALTUNG:\n";
echo "   - players (tenant) - Spielerprofile pro Verein\n";
echo "   - player_group (tenant) - Spieler zu Mannschaften zuordnen\n";
echo "   - player_statistics (tenant) - Spielerstatistiken pro Saison\n\n";

echo "2. TRAININGSVERWALTUNG:\n";
echo "   - training_sessions (tenant) - Trainingseinheiten planen\n";
echo "   - training_attendance (tenant) - Anwesenheit tracken\n";
echo "   - training_locations (tenant) - Trainingsplätze\n\n";

echo "3. CONTENT MANAGEMENT:\n";
echo "   - news/articles (tenant) - Neuigkeiten\n";
echo "   - galleries (tenant) - Bildergalerien\n";
echo "   - documents (tenant) - Dokumente/Downloads\n\n";

echo "4. VEREINSVERWALTUNG:\n";
echo "   - club_settings (tenant) - Vereinseinstellungen\n";
echo "   - sponsors (tenant) - Sponsoren\n";
echo "   - partners (tenant) - Partner\n\n";

echo "5. SYNCHRONISATION:\n";
echo "   - sync_logs (landlord) - Protokoll aller Comet-Syncs\n";
echo "   - sync_errors (landlord) - Fehler beim Sync\n";
echo "   - sync_schedules (landlord) - Automatische Sync-Zeitpläne\n\n";

echo "6. RECHTEVERWALTUNG:\n";
echo "   - roles (tenant) - Rollen (Admin, Trainer, Spieler)\n";
echo "   - permissions (tenant) - Berechtigungen\n";
echo "   - role_user (tenant) - User-Rollen Zuordnung\n\n";

echo "7. KOMMUNIKATION:\n";
echo "   - notifications (tenant) - Benachrichtigungen\n";
echo "   - messages (tenant) - Interne Nachrichten\n";
echo "   - email_logs (tenant) - Email-Versand Protokoll\n\n";

echo "8. ANALYTICS:\n";
echo "   - page_views (tenant) - Website Statistiken\n";
echo "   - event_tracking (tenant) - Event Tracking\n\n";

$conn->close();
