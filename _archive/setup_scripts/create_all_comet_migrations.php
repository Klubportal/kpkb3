<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔄 Erstelle Migrationen für ALLE COMET-Tabellen aus Central DB...\n\n";

// Alle COMET-Tabellen aus Central DB holen
$tables = DB::connection('central')
    ->select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kpkb3' AND TABLE_NAME LIKE 'comet_%' ORDER BY TABLE_NAME");

$migrationDir = database_path('migrations/comet');

// Ordner erstellen falls nicht vorhanden
if (!File::exists($migrationDir)) {
    File::makeDirectory($migrationDir, 0755, true);
    echo "✓ Ordner erstellt: {$migrationDir}\n\n";
}

$timestamp = '2025_01_01_000000';
$counter = 1;

foreach ($tables as $table) {
    $tableName = $table->TABLE_NAME;

    echo "📋 {$tableName}...\n";

    // Spalten holen
    $columns = DB::connection('central')
        ->select("SHOW FULL COLUMNS FROM `{$tableName}`");

    // Indizes holen
    $indexes = DB::connection('central')
        ->select("SHOW INDEX FROM `{$tableName}`");

    // Migration generieren
    $className = 'Create' . str_replace('_', '', ucwords($tableName, '_')) . 'Table';
    $migrationFile = sprintf('%s_%03d_create_%s_table.php', $timestamp, $counter, $tableName);

    $migration = "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\n";
    $migration .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
    $migration .= "use Illuminate\\Support\\Facades\\Schema;\n\n";
    $migration .= "return new class extends Migration\n{\n";
    $migration .= "    public function up(): void\n    {\n";
    $migration .= "        Schema::create('{$tableName}', function (Blueprint \$table) {\n";

    // Spalten-Definitionen
    foreach ($columns as $col) {
        $line = generateColumnDefinition($col);
        if ($line) {
            $migration .= "            {$line}\n";
        }
    }

    // Indizes (außer PRIMARY)
    $processedIndexes = [];
    $indexCounter = 1;
    foreach ($indexes as $index) {
        $indexName = $index->Key_name;

        if ($indexName === 'PRIMARY' || in_array($indexName, $processedIndexes)) {
            continue;
        }

        $processedIndexes[] = $indexName;

        // Alle Spalten für diesen Index sammeln
        $indexColumns = array_filter($indexes, fn($i) => $i->Key_name === $indexName);
        $columnNames = array_map(fn($i) => $i->Column_name, $indexColumns);

        // Kurzen Index-Namen generieren
        $shortName = substr($tableName, 6, 4) . '_idx' . $indexCounter++;

        if ($index->Non_unique == 0) {
            if (count($columnNames) > 1) {
                $migration .= "            \$table->unique(['" . implode("', '", $columnNames) . "'], '{$shortName}');\n";
            }
        } else {
            if (count($columnNames) > 1) {
                $migration .= "            \$table->index(['" . implode("', '", $columnNames) . "'], '{$shortName}');\n";
            }
        }
    }    $migration .= "        });\n";
    $migration .= "    }\n\n";
    $migration .= "    public function down(): void\n    {\n";
    $migration .= "        Schema::dropIfExists('{$tableName}');\n";
    $migration .= "    }\n";
    $migration .= "};\n";

    File::put("{$migrationDir}/{$migrationFile}", $migration);
    echo "   ✓ {$migrationFile}\n";

    $counter++;
}

echo "\n✅ {$counter} Migrationen erstellt in {$migrationDir}\n";

function generateColumnDefinition($col): ?string
{
    $name = $col->Field;
    $type = $col->Type;
    $null = $col->Null === 'YES';
    $default = $col->Default;
    $extra = $col->Extra;

    // id auto_increment überspringen (wird durch $table->id() erstellt)
    if ($name === 'id' && str_contains($extra, 'auto_increment')) {
        return "\$table->id();";
    }

    // created_at/updated_at überspringen (werden durch timestamps() erstellt)
    if ($name === 'created_at' || $name === 'updated_at') {
        if ($name === 'created_at') {
            return "\$table->timestamps();";
        }
        return null;
    }

    $line = "\$table->";

    // Typ bestimmen
    if (preg_match('/^bigint\((\d+)\)( unsigned)?$/', $type, $m)) {
        $line .= "bigInteger('{$name}')";
        if (isset($m[2])) $line .= "->unsigned()";
    } elseif (preg_match('/^int\((\d+)\)( unsigned)?$/', $type, $m)) {
        $line .= "integer('{$name}')";
        if (isset($m[2])) $line .= "->unsigned()";
    } elseif (preg_match('/^smallint\((\d+)\)( unsigned)?$/', $type, $m)) {
        $line .= "smallInteger('{$name}')";
        if (isset($m[2])) $line .= "->unsigned()";
    } elseif (preg_match('/^tinyint\(1\)$/', $type)) {
        $line .= "boolean('{$name}')";
    } elseif (preg_match('/^varchar\((\d+)\)$/', $type, $m)) {
        $line .= "string('{$name}', {$m[1]})";
    } elseif ($type === 'text') {
        $line .= "text('{$name}')";
    } elseif ($type === 'longtext') {
        $line .= "longText('{$name}')";
    } elseif ($type === 'date') {
        $line .= "date('{$name}')";
    } elseif ($type === 'datetime') {
        $line .= "dateTime('{$name}')";
    } elseif ($type === 'timestamp') {
        $line .= "timestamp('{$name}')";
    } elseif (preg_match('/^decimal\((\d+),(\d+)\)$/', $type, $m)) {
        $line .= "decimal('{$name}', {$m[1]}, {$m[2]})";
    } elseif (preg_match('/^enum\((.*)\)$/', $type, $m)) {
        $values = str_replace("'", "", $m[1]);
        $line .= "enum('{$name}', [" . $m[1] . "])";
    } else {
        echo "   ⚠️  Unbekannter Typ: {$type} für {$name}\n";
        return null;
    }

    // Nullable
    if ($null) {
        $line .= "->nullable()";
    }

    // Default
    if ($default !== null && $default !== 'NULL') {
        if (is_numeric($default)) {
            $line .= "->default({$default})";
        } elseif ($default === 'CURRENT_TIMESTAMP') {
            $line .= "->useCurrent()";
        } else {
            $line .= "->default('{$default}')";
        }
    }

    $line .= ";";

    return $line;
}
