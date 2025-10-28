<?php

// MASTER RUNNER: Vollständiger COMET Sync für Clubs 598, 396, 601
// Deckt folgende Tabellen ab:
// - comet_club_competitions, comet_matches, comet_rankings, comet_top_scorers
// - comet_match_events, comet_match_phases, comet_match_players, comet_match_officials, comet_match_team_officials
// - comet_coaches, comet_club_representatives, comet_team_officials

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║         COMET FULL SYNC FOR CLUBS (598, 396, 601)           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$steps = [
    ['label' => 'Competitions/Matches/Rankings/Top Scorers', 'cmd' => 'php sync_comet_direct.php'],
    ['label' => 'Match Events (all matches)', 'cmd' => 'php sync_match_events_all_matches.php'],
    ['label' => 'Match Phases (played matches)', 'cmd' => 'php sync_match_phases_all.php'],
    ['label' => 'Match Players (all matches)', 'cmd' => 'php sync_match_players_all.php'],
    ['label' => 'Match Officials (played matches)', 'cmd' => 'php sync_match_officials_all.php'],
    ['label' => 'Match Team Officials', 'cmd' => 'php sync_match_team_officials.php'],
    ['label' => 'Team Officials (Coaches + Representatives + Static)', 'cmd' => 'php sync_team_officials_for_clubs.php'],
];

$results = [];

foreach ($steps as $step) {
    echo "\n┌" . str_repeat("─", 60) . "┐\n";
    $pad = 60 - 12 - strlen($step['label']);
    if ($pad < 0) { $pad = 0; }
    echo "│ 🔄 RUNNING: {$step['label']}" . str_repeat(" ", $pad) . "│\n";
    echo "└" . str_repeat("─", 60) . "┘\n\n";

    $start = microtime(true);
    $output = [];
    $exit = 0;
    exec($step['cmd'] . ' 2>&1', $output, $exit);
    $dur = round(microtime(true) - $start, 2);

    $results[] = ['label' => $step['label'], 'ok' => $exit === 0, 'time' => $dur];

    echo ($exit === 0 ? "✅" : "❌") . " Completed in {$dur}s\n";
}

// Summary

echo "\n\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                          SUMMARY                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

foreach ($results as $r) {
    $icon = $r['ok'] ? '✅' : '❌';
    printf("%s %-45s | %5.2fs\n", $icon, $r['label'], $r['time']);
}

echo "\nAll done.\n\n";
