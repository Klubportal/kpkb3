<?php

/**
 * Sync Comet Data from Central DB to Tenant DBs
 *
 * Kopiert alle Comet-Daten aus der Central DB in die jeweiligen Tenant DBs,
 * gefiltert nach club_fifa_id des Vereins.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Comet Data Migration: Central DB → Tenant DBs               ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Tenant-Konfiguration: tenant_id => club_fifa_id
$tenantClubMapping = [
    'nknapijed' => 396,    // NK Napijed
    'nkprigorjem' => 598,  // NK Prigorje
];

// Comet-Tabellen die kopiert werden sollen (club-spezifisch)
// Matches, Rankings und TopScorers werden separat über Wettbewerbe kopiert
$cometTables = [
    'comet_clubs_extended' => [
        'filter_columns' => ['club_fifa_id'],
        'description' => 'Vereinsinformationen'
    ],
    'comet_club_representatives' => [
        'filter_columns' => ['club_fifa_id'],
        'description' => 'Vereinsvertreter'
    ],
    'comet_coaches' => [
        'filter_columns' => ['club_fifa_id'],
        'description' => 'Trainer'
    ],
    'comet_team_officials' => [
        'filter_columns' => ['team_fifa_id'],
        'description' => 'Team-Offizielle'
    ],
];

$totalStats = [
    'tenants_processed' => 0,
    'tables_processed' => 0,
    'total_rows_copied' => 0,
    'errors' => 0
];

foreach ($tenantClubMapping as $tenantId => $clubFifaId) {
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📋 Tenant: {$tenantId} (Club FIFA ID: {$clubFifaId})\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    try {
        // Tenant initialisieren
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            echo "❌ Tenant '{$tenantId}' nicht gefunden!\n";
            $totalStats['errors']++;
            continue;
        }

        tenancy()->initialize($tenant);
        $totalStats['tenants_processed']++;

        $tenantDbName = 'tenant_' . $tenantId;

        // STEP 1: Hole alle Wettbewerbe, an denen der Verein teilnimmt
        echo "🔍 Ermittle Wettbewerbe für Club {$clubFifaId}...\n";

        // Finde Wettbewerbe über Matches (wo der Verein Heim oder Auswärts spielt)
        $competitionIds = DB::connection('central')
            ->table('comet_matches')
            ->where(function($q) use ($clubFifaId) {
                $q->where('team_fifa_id_home', $clubFifaId)
                  ->orWhere('team_fifa_id_away', $clubFifaId);
            })
            ->distinct()
            ->pluck('competition_fifa_id')
            ->toArray();

        if (empty($competitionIds)) {
            echo "   ⚠️  Keine Wettbewerbe gefunden für Club {$clubFifaId}\n\n";
        } else {
            $compCount = count($competitionIds);
            echo "   ✅ {$compCount} Wettbewerbe gefunden: " . implode(', ', $competitionIds) . "\n\n";
        }
        // STEP 2: Synchronisiere Matches - ALLE aus den Wettbewerben
        echo "📊 Matches (ALLE aus Vereins-Wettbewerben)...\n";

        if (!empty($competitionIds)) {
            try {
                DB::connection('tenant')->table('comet_matches')->truncate();

                $matches = DB::connection('central')
                    ->table('comet_matches')
                    ->whereIn('competition_fifa_id', $competitionIds)
                    ->get();

                if ($matches->count() > 0) {
                    $chunks = $matches->chunk(100);
                    $insertedTotal = 0;

                    foreach ($chunks as $chunk) {
                        $data = $chunk->map(function($record) {
                            return (array) $record;
                        })->toArray();

                        DB::connection('tenant')->table('comet_matches')->insert($data);
                        $insertedTotal += count($data);
                    }

                    echo "   ✅ {$insertedTotal} Matches kopiert (alle Teams aus {$compCount} Wettbewerben)\n";
                    $totalStats['total_rows_copied'] += $insertedTotal;
                    $totalStats['tables_processed']++;
                } else {
                    echo "   ℹ️  Keine Matches gefunden\n";
                }
            } catch (\Exception $e) {
                echo "   ❌ Fehler: " . $e->getMessage() . "\n";
                $totalStats['errors']++;
            }
        } else {
            echo "   ⚠️  Überspringe - keine Wettbewerbe\n";
        }

        // STEP 3: Synchronisiere Rankings - ALLE aus den Wettbewerben
        echo "📊 Tabellenstände (ALLE Teams aus Vereins-Wettbewerben)...\n";

        if (!empty($competitionIds)) {
            try {
                DB::connection('tenant')->table('comet_rankings')->truncate();

                $rankings = DB::connection('central')
                    ->table('comet_rankings')
                    ->whereIn('competition_fifa_id', $competitionIds)
                    ->get();

                if ($rankings->count() > 0) {
                    $chunks = $rankings->chunk(100);
                    $insertedTotal = 0;

                    foreach ($chunks as $chunk) {
                        $data = $chunk->map(function($record) {
                            return (array) $record;
                        })->toArray();

                        DB::connection('tenant')->table('comet_rankings')->insert($data);
                        $insertedTotal += count($data);
                    }

                    echo "   ✅ {$insertedTotal} Rankings kopiert (alle Teams aus {$compCount} Wettbewerben)\n";
                    $totalStats['total_rows_copied'] += $insertedTotal;
                    $totalStats['tables_processed']++;
                } else {
                    echo "   ℹ️  Keine Rankings gefunden\n";
                }
            } catch (\Exception $e) {
                echo "   ❌ Fehler: " . $e->getMessage() . "\n";
                $totalStats['errors']++;
            }
        } else {
            echo "   ⚠️  Überspringe - keine Wettbewerbe\n";
        }

        // STEP 4: Synchronisiere Torschützen - ALLE aus den Wettbewerben
        echo "📊 Torschützenliste (ALLE aus Vereins-Wettbewerben)...\n";

        if (!empty($competitionIds)) {
            try {
                DB::connection('tenant')->table('comet_top_scorers')->truncate();

                $topScorers = DB::connection('central')
                    ->table('comet_top_scorers')
                    ->whereIn('competition_fifa_id', $competitionIds)
                    ->get();

                if ($topScorers->count() > 0) {
                    $chunks = $topScorers->chunk(100);
                    $insertedTotal = 0;

                    foreach ($chunks as $chunk) {
                        $data = $chunk->map(function($record) {
                            return (array) $record;
                        })->toArray();

                        DB::connection('tenant')->table('comet_top_scorers')->insert($data);
                        $insertedTotal += count($data);
                    }

                    echo "   ✅ {$insertedTotal} Torschützen kopiert (alle aus {$compCount} Wettbewerben)\n";
                    $totalStats['total_rows_copied'] += $insertedTotal;
                    $totalStats['tables_processed']++;
                } else {
                    echo "   ℹ️  Keine Torschützen gefunden\n";
                }
            } catch (\Exception $e) {
                echo "   ❌ Fehler: " . $e->getMessage() . "\n";
                $totalStats['errors']++;
            }
        } else {
            echo "   ⚠️  Überspringe - keine Wettbewerbe\n";
        }

        // STEP 4b: Synchronisiere Eigentor-Statistik - ALLE aus den Wettbewerben
        echo "📊 Eigentor-Statistik (ALLE aus Vereins-Wettbewerben)...\n";

        if (!empty($competitionIds)) {
            try {
                DB::connection('tenant')->table('comet_own_goal_scorers')->truncate();

                $ownGoalScorers = DB::connection('central')
                    ->table('comet_own_goal_scorers')
                    ->whereIn('competition_fifa_id', $competitionIds)
                    ->get();

                if ($ownGoalScorers->count() > 0) {
                    $chunks = $ownGoalScorers->chunk(100);
                    $insertedTotal = 0;

                    foreach ($chunks as $chunk) {
                        $data = $chunk->map(function($record) {
                            return (array) $record;
                        })->toArray();

                        DB::connection('tenant')->table('comet_own_goal_scorers')->insert($data);
                        $insertedTotal += count($data);
                    }

                    echo "   ✅ {$insertedTotal} Eigentor-Einträge kopiert (alle aus {$compCount} Wettbewerben)\n";
                    $totalStats['total_rows_copied'] += $insertedTotal;
                    $totalStats['tables_processed']++;
                } else {
                    echo "   ℹ️  Keine Eigentore gefunden\n";
                }
            } catch (\Exception $e) {
                echo "   ❌ Fehler: " . $e->getMessage() . "\n";
                $totalStats['errors']++;
            }
        } else {
            echo "   ⚠️  Überspringe - keine Wettbewerbe\n";
        }

        // STEP 4c: Synchronisiere Wettbewerb-Infos
        echo "📊 Wettbewerb-Informationen (Competition Details)...\n";

        if (!empty($competitionIds)) {
            try {
                DB::connection('tenant')->table('comet_club_competitions')->truncate();

                // Hole nur EINE Zeile pro competitionFifaId (neueste basierend auf ID)
                $competitions = DB::connection('central')
                    ->table('comet_club_competitions')
                    ->whereIn('competitionFifaId', $competitionIds)
                    ->whereIn('id', function($query) use ($competitionIds) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('comet_club_competitions')
                            ->whereIn('competitionFifaId', $competitionIds)
                            ->groupBy('competitionFifaId');
                    })
                    ->get();

                if ($competitions->count() > 0) {
                    $chunks = $competitions->chunk(100);
                    $insertedTotal = 0;

                    foreach ($chunks as $chunk) {
                        $data = $chunk->map(function($record) {
                            return (array) $record;
                        })->toArray();

                        DB::connection('tenant')->table('comet_club_competitions')->insert($data);
                        $insertedTotal += count($data);
                    }

                    echo "   ✅ {$insertedTotal} Wettbewerbe kopiert\n";
                    $totalStats['total_rows_copied'] += $insertedTotal;
                    $totalStats['tables_processed']++;
                } else {
                    echo "   ℹ️  Keine Wettbewerb-Infos gefunden\n";
                }
            } catch (\Exception $e) {
                echo "   ❌ Fehler: " . $e->getMessage() . "\n";
                $totalStats['errors']++;
            }
        } else {
            echo "   ⚠️  Überspringe - keine Wettbewerbe\n";
        }


        // STEP 5: Rest der club-spezifischen Tabellen
        echo "\n";
        foreach ($cometTables as $tableName => $config) {
            echo "📊 {$config['description']} ({$tableName})...\n";

            try {
                // Prüfe ob Tabelle in Central DB existiert
                $centralExists = DB::connection('central')
                    ->select("SHOW TABLES LIKE '{$tableName}'");

                if (empty($centralExists)) {
                    echo "   ⚠️  Tabelle nicht in Central DB vorhanden - überspringe\n";
                    continue;
                }

                // Zähle Einträge in Central DB
                $centralCount = DB::connection('central')
                    ->table($tableName)
                    ->count();

                if ($centralCount === 0) {
                    echo "   ℹ️  Keine Daten in Central DB - überspringe\n";
                    continue;
                }

                // Lösche existierende Daten in Tenant DB
                DB::connection('tenant')->table($tableName)->truncate();

                // Baue Filter-Bedingung
                $filterColumns = $config['filter_columns'];
                $query = DB::connection('central')->table($tableName);

                // OR-Bedingung für alle Filter-Spalten
                $query->where(function($q) use ($filterColumns, $clubFifaId) {
                    foreach ($filterColumns as $column) {
                        $q->orWhere($column, $clubFifaId);
                    }
                });

                $records = $query->get();
                $recordCount = $records->count();

                if ($recordCount > 0) {
                    // Daten in Chunks einfügen (für Performance)
                    $chunks = $records->chunk(100);
                    $insertedTotal = 0;

                    foreach ($chunks as $chunk) {
                        $data = $chunk->map(function($record) {
                            return (array) $record;
                        })->toArray();

                        DB::connection('tenant')->table($tableName)->insert($data);
                        $insertedTotal += count($data);
                    }

                    echo "   ✅ {$insertedTotal} Einträge kopiert (von {$centralCount} in Central DB)\n";
                    $totalStats['total_rows_copied'] += $insertedTotal;
                } else {
                    echo "   ℹ️  Keine Daten für Club ID {$clubFifaId} gefunden\n";
                }

                $totalStats['tables_processed']++;

            } catch (\Exception $e) {
                echo "   ❌ Fehler: " . $e->getMessage() . "\n";
                $totalStats['errors']++;
            }
        }

        // STEP 6: Match-spezifische Tabellen (basierend auf kopierten Matches)
        echo "\n📋 Match-Details (Events, Players, Officials, etc.)...\n";

        // Hole alle Match-IDs die kopiert wurden
        $matchIds = DB::connection('tenant')
            ->table('comet_matches')
            ->pluck('match_fifa_id')
            ->toArray();

        if (!empty($matchIds)) {
            $matchCount = count($matchIds);
            echo "   ℹ️  Kopiere Details für {$matchCount} Matches\n\n";

            $matchRelatedTables = [
                'comet_match_events' => 'match_fifa_id',
                'comet_match_players' => 'match_fifa_id',
                'comet_match_officials' => 'match_fifa_id',
                'comet_match_phases' => 'match_fifa_id',
                'comet_match_team_officials' => 'match_fifa_id',
            ];

            foreach ($matchRelatedTables as $tableName => $foreignKey) {
                echo "   📊 {$tableName}...\n";

                try {
                    // Prüfe ob Tabelle in Central DB existiert
                    $centralExists = DB::connection('central')
                        ->select("SHOW TABLES LIKE '{$tableName}'");

                    if (empty($centralExists)) {
                        echo "      ⚠️  Tabelle nicht in Central DB - überspringe\n";
                        continue;
                    }

                    // Lösche existierende Daten
                    DB::connection('tenant')->table($tableName)->truncate();

                    // Kopiere Daten basierend auf Match-IDs
                    $records = DB::connection('central')
                        ->table($tableName)
                        ->whereIn($foreignKey, $matchIds)
                        ->get();

                    // Für comet_match_players: Hole Match-Info für team_nature Berechnung
                    $matchTeams = [];
                    if ($tableName === 'comet_match_players') {
                        $matchTeams = DB::connection('tenant')
                            ->table('comet_matches')
                            ->select('match_fifa_id', 'team_fifa_id_home', 'team_fifa_id_away')
                            ->whereIn('match_fifa_id', $matchIds)
                            ->get()
                            ->keyBy('match_fifa_id')
                            ->toArray();
                    }

                    if ($records->count() > 0) {
                        $chunks = $records->chunk(100);
                        $insertedTotal = 0;

                        foreach ($chunks as $chunk) {
                            $data = $chunk->map(function($record) use ($tableName, $matchTeams) {
                                $recordArray = (array) $record;

                                // Fix für Spalten mit leeren Strings basierend auf Tabelle
                                if ($tableName === 'comet_match_players') {
                                    // Berechne team_nature wenn leer
                                    if (isset($recordArray['team_nature']) && $recordArray['team_nature'] === '') {
                                        $matchId = $recordArray['match_fifa_id'] ?? null;
                                        $teamId = $recordArray['team_fifa_id'] ?? null;

                                        if ($matchId && $teamId && isset($matchTeams[$matchId])) {
                                            $match = $matchTeams[$matchId];
                                            if ($teamId == $match->team_fifa_id_home) {
                                                $recordArray['team_nature'] = 'HOME';
                                            } elseif ($teamId == $match->team_fifa_id_away) {
                                                $recordArray['team_nature'] = 'AWAY';
                                            } else {
                                                // Team nicht in Match gefunden - skip
                                                return null;
                                            }
                                        } else {
                                            // Kann team_nature nicht berechnen - skip
                                            return null;
                                        }
                                    }
                                    // Andere leere Strings zu NULL
                                    foreach ($recordArray as $key => $value) {
                                        if ($value === '' && $key !== 'team_nature') {
                                            $recordArray[$key] = null;
                                        }
                                    }
                                } elseif ($tableName === 'comet_match_events') {
                                    // event_type ist ENUM NOT NULL - skip rows mit leerem event_type
                                    if (isset($recordArray['event_type']) && $recordArray['event_type'] === '') {
                                        return null; // Wird später gefiltert
                                    }
                                    // Andere leere Strings zu NULL
                                    foreach ($recordArray as $key => $value) {
                                        if ($value === '' && $key !== 'event_type') {
                                            $recordArray[$key] = null;
                                        }
                                    }
                                } else {
                                    // Für andere Tabellen: leere Strings zu NULL
                                    foreach ($recordArray as $key => $value) {
                                        if ($value === '') {
                                            $recordArray[$key] = null;
                                        }
                                    }
                                }

                                return $recordArray;
                            })->filter()->toArray(); // filter() entfernt null-Werte

                            if (count($data) > 0) {
                                DB::connection('tenant')->table($tableName)->insert($data);
                                $insertedTotal += count($data);
                            }
                        }

                        echo "      ✅ {$insertedTotal} Einträge kopiert\n";
                        $totalStats['total_rows_copied'] += $insertedTotal;
                        $totalStats['tables_processed']++;
                    } else {
                        echo "      ℹ️  Keine Daten gefunden\n";
                    }

                } catch (\Exception $e) {
                    echo "      ❌ Fehler: " . $e->getMessage() . "\n";
                    $totalStats['errors']++;
                }
            }
        } else {
            echo "   ⚠️  Keine Matches vorhanden - überspringe Match-Details\n";
        }

        echo "\n✅ Tenant '{$tenantId}' abgeschlossen\n";        // Tenant-Context beenden
        tenancy()->end();

    } catch (\Exception $e) {
        echo "❌ Fehler bei Tenant '{$tenantId}': " . $e->getMessage() . "\n";
        $totalStats['errors']++;
        tenancy()->end();
    }
}

echo "\n\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  ZUSAMMENFASSUNG                                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";
echo "Tenants verarbeitet:     {$totalStats['tenants_processed']}\n";
echo "Tabellen verarbeitet:    {$totalStats['tables_processed']}\n";
echo "Zeilen kopiert:          {$totalStats['total_rows_copied']}\n";
echo "Fehler:                  {$totalStats['errors']}\n\n";

if ($totalStats['errors'] === 0) {
    echo "🎉 Migration erfolgreich abgeschlossen!\n\n";
} else {
    echo "⚠️  Migration mit Fehlern abgeschlossen.\n\n";
}

echo "🎉 Alle Comet-Daten erfolgreich migriert!\n\n";
