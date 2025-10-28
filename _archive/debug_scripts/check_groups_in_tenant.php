<?php
$c = new mysqli('localhost', 'root', '');

echo "🔍 Checking tenant_nkprigorjem for 'groups' table...\n\n";

$r = $c->query("SHOW TABLES FROM tenant_nkprigorjem LIKE '%group%'");
if ($r->num_rows > 0) {
    while($t = $r->fetch_array()) {
        echo "✅ {$t[0]}\n";

        // Show structure
        $cols = $c->query("DESCRIBE tenant_nkprigorjem.{$t[0]}");
        while($col = $cols->fetch_assoc()) {
            echo "   - {$col['Field']} ({$col['Type']})";
            if ($col['Key']) echo " [{$col['Key']}]";
            echo "\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No 'groups' table found in tenant_nkprigorjem\n";
    echo "\nCreating coach_group table will require:\n";
    echo "1. groups table (not exists yet)\n";
    echo "2. coach_id reference - but comet_coaches is in LANDLORD DB\n";
    echo "\n⚠️  PROBLEM: Cannot create FK from tenant DB to landlord DB!\n";
}
