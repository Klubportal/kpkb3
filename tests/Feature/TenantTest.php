<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use App\Models\Tenant\Team;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're in central context at start of each test
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }

    protected function tearDown(): void
    {
        // Clean up tenant context
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        // Drop test tenant databases
        $testTenants = Tenant::where('id', 'like', 'test_%')->get();
        foreach ($testTenants as $tenant) {
            try {
                DB::statement("DROP DATABASE IF EXISTS `{$tenant->tenancy_db_name}`");
            } catch (\Exception $e) {
                // Ignore errors if database doesn't exist
            }
        }

        parent::tearDown();
    }

    /**
     * Test: Tenant can be created with domain
     */
    public function test_tenant_can_be_created_with_domain()
    {
        $tenant = Tenant::create([
            'id' => 'test_club',
            'name' => 'Test Club',
            'email' => 'admin@testclub.com',
        ]);

        $domain = $tenant->domains()->create([
            'domain' => 'test-club.localhost',
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'test_club',
            'name' => 'Test Club',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'test-club.localhost',
            'tenant_id' => 'test_club',
        ]);
    }

    /**
     * Test: Tenant database can be created and migrated
     */
    public function test_tenant_database_can_be_created_and_migrated()
    {
        $tenant = Tenant::create([
            'id' => 'test_migrate',
            'name' => 'Test Migration Club',
        ]);

        $tenant->domains()->create(['domain' => 'test-migrate.localhost']);

        // Create tenant database
        $dbName = $tenant->tenancy_db_name;
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

        // Run migrations
        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
        ]);

        // Initialize tenant to check tables
        tenancy()->initialize($tenant);

        // Check that tenant tables exist
        $this->assertTrue(
            DB::getSchemaBuilder()->hasTable('teams'),
            'Teams table should exist in tenant database'
        );

        $this->assertTrue(
            DB::getSchemaBuilder()->hasTable('players'),
            'Players table should exist in tenant database'
        );

        $this->assertTrue(
            DB::getSchemaBuilder()->hasTable('users'),
            'Users table should exist in tenant database'
        );

        tenancy()->end();
    }

    /**
     * Test: Tenant context isolation - data from one tenant doesn't appear in another
     */
    public function test_tenant_data_isolation()
    {
        // Create two test tenants
        $tenant1 = $this->createTestTenant('tenant1', 'Tenant One');
        $tenant2 = $this->createTestTenant('tenant2', 'Tenant Two');

        // Create data in tenant1
        tenancy()->initialize($tenant1);

        $team1 = Team::create([
            'name' => 'Team from Tenant 1',
            'age_group' => 'U19',
            'gender' => 'male',
            'is_active' => true,
        ]);

        $tenant1TeamId = $team1->id;
        tenancy()->end();

        // Create data in tenant2
        tenancy()->initialize($tenant2);

        $team2 = Team::create([
            'name' => 'Team from Tenant 2',
            'age_group' => 'U17',
            'gender' => 'female',
            'is_active' => true,
        ]);

        $tenant2TeamId = $team2->id;
        tenancy()->end();

        // Verify tenant1 can only see its own data
        tenancy()->initialize($tenant1);

        $this->assertEquals(1, Team::count(), 'Tenant 1 should only see 1 team');
        $this->assertEquals('Team from Tenant 1', Team::first()->name);
        $this->assertEquals($tenant1TeamId, Team::first()->id);

        tenancy()->end();

        // Verify tenant2 can only see its own data
        tenancy()->initialize($tenant2);

        $this->assertEquals(1, Team::count(), 'Tenant 2 should only see 1 team');
        $this->assertEquals('Team from Tenant 2', Team::first()->name);
        $this->assertEquals($tenant2TeamId, Team::first()->id);

        tenancy()->end();
    }

    /**
     * Test: Tenant can access own data via domain
     */
    public function test_tenant_can_access_own_data()
    {
        $tenant = $this->createTestTenant('test_access', 'Access Test Club');

        tenancy()->initialize($tenant);

        // Create test team
        $team = Team::create([
            'name' => 'First Team',
            'age_group' => 'senior',
            'gender' => 'male',
            'is_active' => true,
        ]);

        // Verify we can retrieve it
        $this->assertNotNull($team->id);
        $this->assertEquals('First Team', $team->name);
        $this->assertDatabaseHas('teams', [
            'name' => 'First Team',
            'age_group' => 'senior',
        ]);

        tenancy()->end();
    }

    /**
     * Test: Switching between tenants updates database connection
     */
    public function test_switching_between_tenants_updates_connection()
    {
        $tenant1 = $this->createTestTenant('switch1', 'Switch Club 1');
        $tenant2 = $this->createTestTenant('switch2', 'Switch Club 2');

        // Initialize tenant 1
        tenancy()->initialize($tenant1);
        $db1 = DB::connection()->getDatabaseName();
        $this->assertEquals($tenant1->tenancy_db_name, $db1);
        tenancy()->end();

        // Initialize tenant 2
        tenancy()->initialize($tenant2);
        $db2 = DB::connection()->getDatabaseName();
        $this->assertEquals($tenant2->tenancy_db_name, $db2);
        tenancy()->end();

        // Verify databases are different
        $this->assertNotEquals($db1, $db2);
    }

    /**
     * Test: Ending tenancy returns to central database
     */
    public function test_ending_tenancy_returns_to_central_database()
    {
        $centralDb = config('database.connections.central.database');

        $tenant = $this->createTestTenant('test_end', 'End Test Club');

        // Start in central
        $this->assertEquals($centralDb, DB::connection('central')->getDatabaseName());

        // Initialize tenant
        tenancy()->initialize($tenant);
        $this->assertEquals($tenant->tenancy_db_name, DB::connection()->getDatabaseName());

        // End tenancy
        tenancy()->end();

        // Should be back to central
        $this->assertFalse(tenancy()->initialized);
        $this->assertEquals($centralDb, DB::connection('central')->getDatabaseName());
    }

    /**
     * Test: Tenant user is isolated from central users
     */
    public function test_tenant_users_are_isolated_from_central_users()
    {
        $tenant = $this->createTestTenant('test_users', 'User Test Club');

        // Create central user
        \App\Models\Central\User::create([
            'name' => 'Central Admin',
            'email' => 'admin@central.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals(1, \App\Models\Central\User::count());

        // Initialize tenant and create tenant user
        tenancy()->initialize($tenant);

        TenantUser::create([
            'first_name' => 'Tenant',
            'last_name' => 'User',
            'email' => 'user@tenant.com',
            'password' => bcrypt('password'),
        ]);

        // Tenant should only see tenant users
        $this->assertEquals(1, TenantUser::count());
        $this->assertEquals('user@tenant.com', TenantUser::first()->email);

        tenancy()->end();

        // Central should still only have central user
        $this->assertEquals(1, \App\Models\Central\User::count());
        $this->assertEquals('admin@central.com', \App\Models\Central\User::first()->email);
    }

    /**
     * Test: Tenant can be identified by domain
     */
    public function test_tenant_can_be_identified_by_domain()
    {
        $tenant = Tenant::create([
            'id' => 'test_domain',
            'name' => 'Domain Test Club',
        ]);

        $domain = $tenant->domains()->create([
            'domain' => 'domain-test.localhost',
        ]);

        $foundTenant = Tenant::whereHas('domains', function ($query) {
            $query->where('domain', 'domain-test.localhost');
        })->first();

        $this->assertNotNull($foundTenant);
        $this->assertEquals('test_domain', $foundTenant->id);
        $this->assertEquals('Domain Test Club', $foundTenant->name);
    }

    /**
     * Test: Multiple domains can point to same tenant
     */
    public function test_multiple_domains_can_point_to_same_tenant()
    {
        $tenant = Tenant::create([
            'id' => 'test_multi_domain',
            'name' => 'Multi Domain Club',
        ]);

        $domain1 = $tenant->domains()->create(['domain' => 'club1.localhost']);
        $domain2 = $tenant->domains()->create(['domain' => 'club2.localhost']);
        $domain3 = $tenant->domains()->create(['domain' => 'club3.localhost']);

        $this->assertEquals(3, $tenant->domains()->count());

        // All domains should resolve to same tenant
        foreach (['club1.localhost', 'club2.localhost', 'club3.localhost'] as $domainName) {
            $foundTenant = Tenant::whereHas('domains', function ($query) use ($domainName) {
                $query->where('domain', $domainName);
            })->first();

            $this->assertEquals('test_multi_domain', $foundTenant->id);
        }
    }

    /**
     * Helper: Create a test tenant with database and migrations
     */
    protected function createTestTenant(string $id, string $name): Tenant
    {
        $tenant = Tenant::create([
            'id' => $id,
            'name' => $name,
            'email' => "{$id}@example.com",
        ]);

        $tenant->domains()->create([
            'domain' => "{$id}.localhost",
        ]);

        // Create and migrate tenant database
        $dbName = $tenant->tenancy_db_name;
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
        ]);

        return $tenant;
    }
}
