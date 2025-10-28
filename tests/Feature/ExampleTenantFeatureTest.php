<?php

namespace Tests\Feature;

use App\Models\Tenant\Team;
use App\Models\Tenant\Player;
use Tests\TenantTestCase;
use Tests\Traits\CreatesTenantData;
use Tests\Traits\TenantAssertions;

/**
 * Example Test: Demonstrates how to use the Tenant Testing Infrastructure
 *
 * This is a complete example showing best practices for tenant testing
 */
class ExampleTenantFeatureTest extends TenantTestCase
{
    use CreatesTenantData, TenantAssertions;

    /**
     * Example 1: Basic tenant isolation test
     */
    public function test_basic_tenant_isolation()
    {
        // Create two tenants
        $tenant1 = $this->createTestTenant('club1', ['name' => 'Club One']);
        $tenant2 = $this->createTestTenant('club2', ['name' => 'Club Two']);

        // Work in tenant1 context
        $this->actingAsTenant($tenant1, function () use ($tenant1) {
            // Create a team
            $team = $this->createTeam(['name' => 'Team from Club 1']);

            // Assertions
            $this->assertInTenantContext();
            $this->assertCurrentTenant($tenant1);
            $this->assertEquals(1, Team::count());
            $this->assertEquals('Team from Club 1', Team::first()->name);
        });

        // Work in tenant2 context
        $this->actingAsTenant($tenant2, function () use ($tenant2) {
            // Create a team
            $team = $this->createTeam(['name' => 'Team from Club 2']);

            // Assertions - should only see tenant2 data
            $this->assertInTenantContext();
            $this->assertCurrentTenant($tenant2);
            $this->assertEquals(1, Team::count()); // Only 1 team!
            $this->assertEquals('Team from Club 2', Team::first()->name);
        });

        // Verify isolation - each tenant only sees its own data
        $this->actingAsTenant($tenant1, function () {
            $this->assertEquals(1, Team::count());
            $this->assertEquals('Team from Club 1', Team::first()->name);
        });
    }

    /**
     * Example 2: Creating complex data structures
     */
    public function test_create_team_with_full_squad()
    {
        $tenant = $this->createTestTenant('complete_club');

        $this->actingAsTenant($tenant, function () {
            // Create team with 11 players
            $team = $this->createTeamWithPlayers(11, [
                'name' => 'First Team',
                'age_group' => 'senior',
            ]);

            // Verify team and players
            $this->assertEquals('First Team', $team->name);
            $this->assertEquals(11, $team->players->count());

            // Verify players have jersey numbers 1-11
            $jerseyNumbers = $team->players->pluck('jersey_number')->sort()->values();
            $this->assertEquals(range(1, 11), $jerseyNumbers->toArray());
        });
    }

    /**
     * Example 3: Testing with seeded data
     */
    public function test_working_with_seeded_data()
    {
        $tenant = $this->createTestTenant('seeded_club');

        $this->actingAsTenant($tenant, function () use ($tenant) {
            // Seed tenant with demo data
            $this->seedTenantData($tenant);

            // Verify seeded data exists
            $this->assertEquals(3, \App\Models\Tenant\User::count());
            $this->assertEquals(5, Team::count());
            $this->assertEquals(11, Player::count());

            // Verify specific team names from seeder
            $this->assertDatabaseHas('teams', ['name' => 'Erste Mannschaft']);
            $this->assertDatabaseHas('teams', ['name' => 'Zweite Mannschaft']);

            // Work with seeded data
            $firstTeam = Team::where('name', 'Erste Mannschaft')->first();
            $this->assertNotNull($firstTeam);
            $this->assertEquals(11, $firstTeam->players->count());
        });
    }

    /**
     * Example 4: Multiple tenants and data isolation
     */
    public function test_multiple_tenants_data_isolation()
    {
        // Create 3 tenants
        $tenants = $this->createMultipleTenants(3);

        // Give each tenant unique data
        foreach ($tenants as $index => $tenant) {
            $this->actingAsTenant($tenant, function () use ($index) {
                $this->createTeam(['name' => "Team {$index}"]);
                $this->createTenantUser([
                    'first_name' => "User",
                    'last_name' => "{$index}",
                ]);
            });
        }

        // Verify each tenant only sees its own data
        foreach ($tenants as $index => $tenant) {
            $this->actingAsTenant($tenant, function () use ($index) {
                $this->assertEquals(1, Team::count());
                $this->assertEquals("Team {$index}", Team::first()->name);

                $this->assertEquals(1, \App\Models\Tenant\User::count());
                $user = \App\Models\Tenant\User::first();
                $this->assertEquals("User {$index}", $user->full_name);
            });
        }
    }

    /**
     * Example 5: Database assertions
     */
    public function test_database_context_switching()
    {
        $tenant = $this->createTestTenant('db_test');

        // Initially in central context
        $this->assertInCentralContext();
        $this->assertCentralDatabase();

        // Switch to tenant context
        $this->initializeTenant($tenant);

        $this->assertInTenantContext();
        $this->assertTenantDatabase($tenant);
        $this->assertCurrentTenant($tenant);

        // Verify tenant tables exist
        $this->assertTenantTableExists('teams');
        $this->assertTenantTableExists('players');
        $this->assertTenantTableExists('users');

        // Return to central
        $this->endTenancy();

        $this->assertInCentralContext();
    }

    /**
     * Example 6: Config assertions
     */
    public function test_tenant_config_is_applied()
    {
        $tenant = $this->createTestTenant('config_test', [
            'name' => 'Config Test Club',
            'email' => 'admin@configtest.com',
        ]);

        $this->actingAsTenant($tenant, function () use ($tenant) {
            // Verify config was set by ConfigureTenantEnvironment listener
            $this->assertTenantConfig('app.name', 'Config Test Club');
            $this->assertTenantConfig('mail.from.address', 'admin@configtest.com');

            // Verify cache prefix
            $this->assertTenantCachePrefix($tenant);

            // Verify app URL contains tenant domain
            $appUrl = config('app.url');
            $this->assertStringContainsString('config_test.localhost', $appUrl);
        });
    }

    /**
     * Example 7: Domain tests
     */
    public function test_tenant_domains()
    {
        $tenant = $this->createTestTenant('domain_test');

        // Add additional domains
        $tenant->domains()->create(['domain' => 'alternate.localhost']);
        $tenant->domains()->create(['domain' => 'another.localhost']);

        // Assert domains exist
        $this->assertEquals(3, $tenant->domains()->count()); // 1 default + 2 added

        $this->assertTenantHasDomain($tenant, 'domain_test.localhost');
        $this->assertTenantHasDomain($tenant, 'alternate.localhost');
        $this->assertTenantHasDomain($tenant, 'another.localhost');
    }

    /**
     * Example 8: Creating relationships
     */
    public function test_creating_related_entities()
    {
        $tenant = $this->createTestTenant('relations_test');

        $this->actingAsTenant($tenant, function () {
            // Create user
            $user = $this->createTenantUser([
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]);

            // Create news by this user
            $news = $this->createNews($user, [
                'title' => 'Important Announcement',
                'status' => 'published',
            ]);

            // Create event by this user
            $event = $this->createEvent($user, [
                'title' => 'Team Meeting',
                'type' => 'meeting',
            ]);

            // Verify relationships
            $this->assertEquals($user->id, $news->author_user_id);
            $this->assertEquals($user->id, $event->created_by_user_id);

            // Check database
            $this->assertDatabaseHas('news', [
                'title' => 'Important Announcement',
                'author_user_id' => $user->id,
            ]);
        });
    }
}
