<?php

namespace Tests\Traits;

use App\Models\Central\Tenant;
use App\Models\Tenant\Event;
use App\Models\Tenant\FootballMatch;
use App\Models\Tenant\News;
use App\Models\Tenant\Player;
use App\Models\Tenant\Team;
use App\Models\Tenant\User as TenantUser;

/**
 * Trait for creating tenant test data
 *
 * Provides factory methods for quickly creating tenant entities in tests
 */
trait CreatesTenantData
{
    /**
     * Create a tenant user
     */
    protected function createTenantUser(array $attributes = []): TenantUser
    {
        return TenantUser::create(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'phone' => '+49 123 456789',
            'gender' => 'male',
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Create a team
     */
    protected function createTeam(array $attributes = []): Team
    {
        return Team::create(array_merge([
            'name' => 'Test Team ' . uniqid(),
            'age_group' => 'senior',
            'gender' => 'male',
            'is_active' => true,
            'display_order' => 1,
        ], $attributes));
    }

    /**
     * Create a player
     */
    protected function createPlayer(?Team $team = null, array $attributes = []): Player
    {
        if (!$team) {
            $team = $this->createTeam();
        }

        return Player::create(array_merge([
            'team_id' => $team->id,
            'first_name' => 'Test',
            'last_name' => 'Player',
            'jersey_number' => rand(1, 99),
            'position' => 'midfielder',
            'birth_date' => now()->subYears(20),
            'gender' => 'male',
            'nationality' => 'DE',
            'joined_date' => now()->subYear(),
            'is_active' => true,
            'status' => 'active',
        ], $attributes));
    }

    /**
     * Create a match
     */
    protected function createMatch(?Team $homeTeam = null, array $attributes = []): FootballMatch
    {
        if (!$homeTeam) {
            $homeTeam = $this->createTeam();
        }

        return FootballMatch::create(array_merge([
            'home_team_id' => $homeTeam->id,
            'opponent_name' => 'Test Opponent',
            'match_date' => now()->addWeek(),
            'location' => 'home',
            'match_type' => 'league',
            'status' => 'scheduled',
        ], $attributes));
    }

    /**
     * Create news article
     */
    protected function createNews(?TenantUser $author = null, array $attributes = []): News
    {
        if (!$author) {
            $author = $this->createTenantUser();
        }

        return News::create(array_merge([
            'title' => 'Test News ' . uniqid(),
            'slug' => 'test-news-' . uniqid(),
            'content' => 'Test news content',
            'excerpt' => 'Test excerpt',
            'author_user_id' => $author->id,
            'status' => 'published',
            'published_at' => now(),
            'is_featured' => false,
            'allow_comments' => true,
        ], $attributes));
    }

    /**
     * Create event
     */
    protected function createEvent(?TenantUser $creator = null, array $attributes = []): Event
    {
        if (!$creator) {
            $creator = $this->createTenantUser();
        }

        return Event::create(array_merge([
            'title' => 'Test Event ' . uniqid(),
            'description' => 'Test event description',
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addHours(2),
            'location' => 'Test Location',
            'type' => 'meeting',
            'visibility' => 'public',
            'status' => 'scheduled',
            'created_by_user_id' => $creator->id,
        ], $attributes));
    }

    /**
     * Create multiple users
     */
    protected function createMultipleUsers(int $count, array $attributes = []): \Illuminate\Support\Collection
    {
        $users = collect();

        for ($i = 0; $i < $count; $i++) {
            $users->push($this->createTenantUser($attributes));
        }

        return $users;
    }

    /**
     * Create multiple teams
     */
    protected function createMultipleTeams(int $count, array $attributes = []): \Illuminate\Support\Collection
    {
        $teams = collect();

        for ($i = 0; $i < $count; $i++) {
            $teams->push($this->createTeam($attributes));
        }

        return $teams;
    }

    /**
     * Create team with players
     */
    protected function createTeamWithPlayers(int $playerCount = 11, array $teamAttributes = []): Team
    {
        $team = $this->createTeam($teamAttributes);

        for ($i = 0; $i < $playerCount; $i++) {
            $this->createPlayer($team, [
                'jersey_number' => $i + 1,
            ]);
        }

        return $team->fresh(['players']);
    }

    /**
     * Create a complete match with teams and players
     */
    protected function createCompleteMatch(): FootballMatch
    {
        $homeTeam = $this->createTeamWithPlayers(11);

        return $this->createMatch($homeTeam, [
            'status' => 'scheduled',
        ]);
    }
}
