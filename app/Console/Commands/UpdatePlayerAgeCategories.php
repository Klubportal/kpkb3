<?php

namespace App\Console\Commands;

use App\Models\Comet\CometPlayer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePlayerAgeCategories extends Command
{
    protected $signature = 'comet:update-player-age-categories';

    protected $description = 'Update player age categories from their matches';

    public function handle()
    {
        $this->info('Updating player age categories...');

        $players = CometPlayer::whereNotNull('person_fifa_id')->get();
        $updated = 0;

        foreach ($players as $player) {
            // Get most common age_category from matches
            $ageCategory = DB::table('comet_match_players as mp')
                ->join('comet_matches as m', 'mp.match_fifa_id', '=', 'm.match_fifa_id')
                ->where('mp.person_fifa_id', $player->person_fifa_id)
                ->select('m.age_category', DB::raw('COUNT(*) as count'))
                ->groupBy('m.age_category')
                ->orderByDesc('count')
                ->first();

            // Calculate birth year from date_of_birth
            $birthYear = null;
            if ($player->date_of_birth) {
                $birthYear = $player->date_of_birth->year;
            }

            if ($ageCategory || $birthYear) {
                $player->update([
                    'primary_age_category' => $ageCategory?->age_category,
                    'birth_year' => $birthYear,
                ]);

                $updated++;
                if ($ageCategory) {
                    $this->info("✓ Updated {$player->name} - {$ageCategory->age_category} ({$birthYear})");
                } else {
                    $this->info("✓ Updated {$player->name} - No matches ({$birthYear})");
                }
            }
        }

        $this->info("✓ Updated {$updated} players");

        return Command::SUCCESS;
    }
}
