<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Central\Tenant;

echo "=== Dropping comet_own_goal_scorers, comet_sanctions, comet_syncs from all tenants ===\n\n";

$tenants = Tenant::all();
$tables = ['comet_own_goal_scorers', 'comet_sanctions', 'comet_syncs'];

foreach ($tenants as $tenant) {
    echo "Tenant: {$tenant->id} ({$tenant->name})\n";

    try {
        tenancy()->initialize($tenant);

        foreach ($tables as $table) {
            // Drop FKs first
            $fks = DB::connection('tenant')->select("
                SELECT CONSTRAINT_NAME, TABLE_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE REFERENCED_TABLE_NAME = '{$table}'
                AND TABLE_SCHEMA = DATABASE()
            ");

            foreach ($fks as $fk) {
                try {
                    DB::connection('tenant')->statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                    echo "  ✓ Dropped FK: {$fk->CONSTRAINT_NAME} from {$fk->TABLE_NAME}\n";
                } catch (\Exception $e) {
                    echo "  - Could not drop FK {$fk->CONSTRAINT_NAME}\n";
                }
            }

            DB::connection('tenant')->statement("DROP TABLE IF EXISTS {$table}");
            echo "  ✓ Dropped {$table}\n";
        }

        tenancy()->end();

    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "✅ Done!\n";
