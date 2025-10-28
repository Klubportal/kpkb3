<?php

namespace App\Console\Commands;

use App\Models\Comet\CometPlayer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePlayerStatistics extends Command
{
    protected $signature = 'comet:update-player-stats {--player_id= : Update specific player only}';

    protected $description = 'Update player statistics from match_players data';

    public function handle()
    {
        $this->info('Updating player statistics...');

        $playerIdFilter = $this->option('player_id');

        // Get all players with their person_fifa_id
        $playersQuery = CometPlayer::whereNotNull('person_fifa_id');

        if ($playerIdFilter) {
            $playersQuery->where('id', $playerIdFilter);
        }

        $players = $playersQuery->get();
        $updated = 0;
        $skipped = 0;

        foreach ($players as $player) {
            // Get all match participations for this player
            $matchPlayers = DB::table('comet_match_players as mp')
                ->join('comet_matches as m', 'mp.match_fifa_id', '=', 'm.match_fifa_id')
                ->where('mp.person_fifa_id', $player->person_fifa_id)
                ->select(
                    'mp.*',
                    'm.age_category'
                )
                ->get();

            if ($matchPlayers->isEmpty()) {
                $skipped++;
                continue;
            }

            $totalMinutes = 0;
            $totalGoals = 0;
            $totalAssists = 0;
            $totalYellow = 0;
            $totalRed = 0;
            $totalMatches = 0;

            foreach ($matchPlayers as $mp) {
                // Determine match duration based on age category
                $matchDuration = 90; // Default: Seniori/Juniori/Kadeti (2x45)

                if ($mp->age_category) {
                    $ageCategory = strtolower($mp->age_category);

                    // Exact match durations per age category
                    if (str_contains($ageCategory, 'seniori') || str_contains($ageCategory, 'seniorke') || str_contains($ageCategory, 'žene')) {
                        $matchDuration = 90; // 2x45
                    } elseif (str_contains($ageCategory, 'juniori')) {
                        $matchDuration = 90; // 2x45
                    } elseif (str_contains($ageCategory, 'kadeti')) {
                        $matchDuration = 90; // 2x45 (but can also be 2x35 = 70)
                    } elseif (str_contains($ageCategory, 'pioniri') && !str_contains($ageCategory, 'mlađi')) {
                        $matchDuration = 70; // 2x35
                    } elseif (str_contains($ageCategory, 'mlađi pioniri') || str_contains($ageCategory, 'mladi pioniri')) {
                        $matchDuration = 60; // 2x30
                    } elseif (str_contains($ageCategory, 'početnici') || str_contains($ageCategory, 'pocetnici')) {
                        $matchDuration = 50; // 2x25
                    } elseif (str_contains($ageCategory, 'prednatjecatelji')) {
                        $matchDuration = 40; // 2x20
                    } elseif (str_contains($ageCategory, 'mali nogomet') && str_contains($ageCategory, 'juniori')) {
                        $matchDuration = 36; // 2x18
                    } elseif (str_contains($ageCategory, 'mali nogomet') && str_contains($ageCategory, 'kadeti')) {
                        $matchDuration = 30; // 2x15
                    } elseif (str_contains($ageCategory, 'mali nogomet')) {
                        $matchDuration = 40; // 2x20 (default small football)
                    } elseif (str_contains($ageCategory, 'pijesak') || str_contains($ageCategory, 'beach')) {
                        $matchDuration = 36; // 3x12
                    }

                    // Legacy support for old naming
                    if (str_contains($ageCategory, 'zagić') || str_contains($ageCategory, 'zagic')) {
                        $matchDuration = 40; // 2x20
                    } elseif (str_contains($ageCategory, 'limač') || str_contains($ageCategory, 'limac')) {
                        $matchDuration = 50; // 2x25
                    }
                }

                // Calculate minutes played
                $minutesPlayed = 0;
                if ($mp->played) {
                    if ($mp->minutes_played > 0) {
                        // Use actual minutes if available
                        $minutesPlayed = $mp->minutes_played;
                    } elseif ($mp->substituted_out_minute > 0) {
                        // Player was substituted out
                        $minutesPlayed = $mp->substituted_out_minute;
                    } elseif ($mp->substituted_in_minute > 0) {
                        // Player was substituted in
                        $minutesPlayed = $matchDuration - $mp->substituted_in_minute;
                    } elseif ($mp->starting_lineup) {
                        // Started and played full match
                        $minutesPlayed = $matchDuration;
                    } else {
                        // Played but no substitution info - assume came from bench and played 30 min
                        $minutesPlayed = 30;
                    }
                }

                $totalMinutes += $minutesPlayed;
                $totalGoals += $mp->goals ?? 0;
                $totalAssists += $mp->assists ?? 0;
                $totalYellow += $mp->yellow_cards ?? 0;
                $totalRed += $mp->red_cards ?? 0;
                $totalMatches++;
            }

            $player->update([
                // Total statistics
                'total_matches' => $totalMatches,
                'total_goals' => $totalGoals,
                'total_assists' => $totalAssists,
                'total_yellow_cards' => $totalYellow,
                'total_red_cards' => $totalRed,
                'total_minutes_played' => $totalMinutes,

                // Season statistics (same for now)
                'season_matches' => $totalMatches,
                'season_goals' => $totalGoals,
                'season_assists' => $totalAssists,
                'season_yellow_cards' => $totalYellow,
                'season_red_cards' => $totalRed,
                'season_minutes_played' => $totalMinutes,
            ]);

            $updated++;
            $this->info("✓ Updated {$player->name} - {$totalMatches} matches, {$totalGoals} goals, {$totalMinutes} min");
        }

        $this->info("✓ Updated {$updated} players");
        $this->info("- Skipped {$skipped} players (no match data)");

        return Command::SUCCESS;
    }
}
