<?php

namespace Tests;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Base test case for tenant-aware tests
 *
 * Provides helper methods for creating, initializing, and managing tenants in tests
 */
abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Current tenant being tested
     */
    protected ?Tenant $tenant = null;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we start in central context
        $this->ensureCentralContext();
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        // End tenant context if active
        $this->ensureCentralContext();

        // Clean up test tenants
        $this->cleanupTestTenants();

        parent::tearDown();
    }

    /**
     * Create a test tenant with database and migrations
     *
     * @param string $id Tenant ID (will be prefixed with test_ if not already)
     * @param array $attributes Additional tenant attributes
     * @param bool $migrate Whether to run migrations (default: true)
     * @return Tenant
     */
    protected function createTestTenant(
        string $id,
        array $attributes = [],
        bool $migrate = true
    ): Tenant {
        // Ensure test prefix
        if (!str_starts_with($id, 'test_')) {
            $id = 'test_' . $id;
        }

        $defaultAttributes = [
            'id' => $id,
            'name' => $attributes['name'] ?? ucfirst(str_replace('_', ' ', $id)),
            'email' => $attributes['email'] ?? "{$id}@example.com",
        ];

        $tenant = Tenant::create(array_merge($defaultAttributes, $attributes));

        // Create default domain
        $domain = $attributes['domain'] ?? "{$id}.localhost";
        $tenant->domains()->create(['domain' => $domain]);

        // Create tenant database
        if ($migrate) {
            $this->createTenantDatabase($tenant);
            $this->migrateTenantDatabase($tenant);
        }

        return $tenant;
    }

    /**
     * Create tenant database
     */
    protected function createTenantDatabase(Tenant $tenant): void
    {
        $dbName = $tenant->tenancy_db_name;
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
    }

    /**
     * Run migrations on tenant database
     */
    protected function migrateTenantDatabase(Tenant $tenant): void
    {
        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
        ]);
    }

    /**
     * Initialize tenant context
     */
    protected function initializeTenant(Tenant $tenant): void
    {
        tenancy()->initialize($tenant);
        $this->tenant = $tenant;
    }

    /**
     * End tenant context and return to central
     */
    protected function endTenancy(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
        $this->tenant = null;
    }

    /**
     * Ensure we're in central context
     */
    protected function ensureCentralContext(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
        $this->tenant = null;
    }

    /**
     * Switch to a different tenant
     */
    protected function switchToTenant(Tenant $tenant): void
    {
        $this->endTenancy();
        $this->initializeTenant($tenant);
    }

    /**
     * Execute callback in tenant context, then return to central
     *
     * @param Tenant $tenant
     * @param callable $callback
     * @return mixed
     */
    protected function actingAsTenant(Tenant $tenant, callable $callback): mixed
    {
        $this->initializeTenant($tenant);

        try {
            return $callback($tenant);
        } finally {
            $this->endTenancy();
        }
    }

    /**
     * Seed tenant database with demo data
     */
    protected function seedTenantData(Tenant $tenant): void
    {
        Artisan::call('tenants:seed', [
            '--tenants' => [$tenant->id],
            '--class' => 'Database\\Seeders\\Tenant\\TenantDatabaseSeeder',
        ]);
    }

    /**
     * Assert we're currently in tenant context
     */
    protected function assertInTenantContext(string $message = ''): void
    {
        $this->assertTrue(
            tenancy()->initialized,
            $message ?: 'Expected to be in tenant context'
        );
    }

    /**
     * Assert we're currently in central context
     */
    protected function assertInCentralContext(string $message = ''): void
    {
        $this->assertFalse(
            tenancy()->initialized,
            $message ?: 'Expected to be in central context'
        );
    }

    /**
     * Assert current tenant matches expected
     */
    protected function assertCurrentTenant(Tenant $expectedTenant, string $message = ''): void
    {
        $this->assertInTenantContext($message);

        $currentTenant = tenancy()->tenant;
        $this->assertEquals(
            $expectedTenant->id,
            $currentTenant->id,
            $message ?: "Expected current tenant to be {$expectedTenant->id}"
        );
    }

    /**
     * Assert database is tenant database
     */
    protected function assertTenantDatabase(Tenant $tenant, string $message = ''): void
    {
        $currentDb = DB::connection()->getDatabaseName();
        $this->assertEquals(
            $tenant->tenancy_db_name,
            $currentDb,
            $message ?: "Expected database to be {$tenant->tenancy_db_name}"
        );
    }

    /**
     * Assert database is central database
     */
    protected function assertCentralDatabase(string $message = ''): void
    {
        $centralDb = config('database.connections.central.database');
        $currentDb = DB::connection('central')->getDatabaseName();

        $this->assertEquals(
            $centralDb,
            $currentDb,
            $message ?: "Expected database to be central database"
        );
    }

    /**
     * Get current tenant
     */
    protected function currentTenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Check if we're in tenant context
     */
    protected function inTenantContext(): bool
    {
        return tenancy()->initialized;
    }

    /**
     * Clean up all test tenants
     */
    protected function cleanupTestTenants(): void
    {
        // End any active tenancy
        $this->ensureCentralContext();

        // Find all test tenants
        $testTenants = Tenant::where('id', 'like', 'test_%')->get();

        foreach ($testTenants as $tenant) {
            try {
                // Drop tenant database
                $dbName = $tenant->tenancy_db_name;
                DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
            } catch (\Exception $e) {
                // Ignore errors - database might not exist
            }

            try {
                // Delete tenant record (cascades to domains)
                $tenant->delete();
            } catch (\Exception $e) {
                // Ignore errors
            }
        }
    }

    /**
     * Create multiple test tenants
     *
     * @param int $count Number of tenants to create
     * @param bool $migrate Whether to migrate databases
     * @return \Illuminate\Support\Collection<Tenant>
     */
    protected function createMultipleTenants(int $count, bool $migrate = true): \Illuminate\Support\Collection
    {
        $tenants = collect();

        for ($i = 1; $i <= $count; $i++) {
            $tenants->push(
                $this->createTestTenant("tenant{$i}", [], $migrate)
            );
        }

        return $tenants;
    }

    /**
     * Assert tenant table exists
     */
    protected function assertTenantTableExists(string $table, string $message = ''): void
    {
        $this->assertTrue(
            DB::getSchemaBuilder()->hasTable($table),
            $message ?: "Table {$table} should exist in tenant database"
        );
    }

    /**
     * Assert central table exists
     */
    protected function assertCentralTableExists(string $table, string $message = ''): void
    {
        $this->assertTrue(
            DB::connection('central')->getSchemaBuilder()->hasTable($table),
            $message ?: "Table {$table} should exist in central database"
        );
    }

    /**
     * Get tenant database name
     */
    protected function getTenantDatabaseName(Tenant $tenant): string
    {
        return $tenant->tenancy_db_name;
    }

    /**
     * Check if tenant database exists
     */
    protected function tenantDatabaseExists(Tenant $tenant): bool
    {
        $dbName = $tenant->tenancy_db_name;

        try {
            $databases = DB::select("SHOW DATABASES LIKE '{$dbName}'");
            return count($databases) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
