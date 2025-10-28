<?php

namespace Tests\Traits;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Trait for tenant-related assertions in tests
 *
 * Provides custom assertions for verifying tenant isolation and context
 */
trait TenantAssertions
{
    /**
     * Assert that we are in tenant context
     */
    protected function assertInTenantContext(string $message = ''): void
    {
        $this->assertTrue(
            tenancy()->initialized,
            $message ?: 'Expected to be in tenant context, but not initialized'
        );
    }

    /**
     * Assert that we are in central context
     */
    protected function assertInCentralContext(string $message = ''): void
    {
        $this->assertFalse(
            tenancy()->initialized,
            $message ?: 'Expected to be in central context, but tenant is initialized'
        );
    }

    /**
     * Assert current tenant matches expected
     */
    protected function assertCurrentTenant(Tenant $expectedTenant, string $message = ''): void
    {
        $this->assertInTenantContext($message);

        $currentTenant = tenancy()->tenant;

        $this->assertNotNull($currentTenant, 'Current tenant is null');

        $this->assertEquals(
            $expectedTenant->id,
            $currentTenant->id,
            $message ?: "Expected current tenant to be '{$expectedTenant->id}', got '{$currentTenant->id}'"
        );
    }

    /**
     * Assert database connection is using tenant database
     */
    protected function assertTenantDatabase(Tenant $tenant, string $message = ''): void
    {
        $currentDb = DB::connection()->getDatabaseName();
        $expectedDb = $tenant->tenancy_db_name;

        $this->assertEquals(
            $expectedDb,
            $currentDb,
            $message ?: "Expected database to be '{$expectedDb}', got '{$currentDb}'"
        );
    }

    /**
     * Assert database connection is using central database
     */
    protected function assertCentralDatabase(string $message = ''): void
    {
        $centralDb = config('database.connections.central.database');
        $currentDb = DB::connection('central')->getDatabaseName();

        $this->assertEquals(
            $centralDb,
            $currentDb,
            $message ?: "Expected database to be central database '{$centralDb}', got '{$currentDb}'"
        );
    }

    /**
     * Assert tenant table exists in current database
     */
    protected function assertTenantTableExists(string $table, string $message = ''): void
    {
        $this->assertTrue(
            DB::getSchemaBuilder()->hasTable($table),
            $message ?: "Expected table '{$table}' to exist in tenant database"
        );
    }

    /**
     * Assert central table exists
     */
    protected function assertCentralTableExists(string $table, string $message = ''): void
    {
        $this->assertTrue(
            DB::connection('central')->getSchemaBuilder()->hasTable($table),
            $message ?: "Expected table '{$table}' to exist in central database"
        );
    }

    /**
     * Assert data exists only in current tenant database
     */
    protected function assertDataIsolatedToTenant(
        string $table,
        array $data,
        Tenant $tenant,
        string $message = ''
    ): void {
        // Assert in current tenant
        $this->assertTenantDatabase($tenant);
        $this->assertDatabaseHas($table, $data);
    }

    /**
     * Assert tenant has specific domain
     */
    protected function assertTenantHasDomain(Tenant $tenant, string $domain, string $message = ''): void
    {
        $hasDomain = $tenant->domains()->where('domain', $domain)->exists();

        $this->assertTrue(
            $hasDomain,
            $message ?: "Expected tenant '{$tenant->id}' to have domain '{$domain}'"
        );
    }

    /**
     * Assert tenant count
     */
    protected function assertTenantCount(int $expected, string $message = ''): void
    {
        $actual = Tenant::count();

        $this->assertEquals(
            $expected,
            $actual,
            $message ?: "Expected {$expected} tenants, found {$actual}"
        );
    }

    /**
     * Assert tenant database exists
     */
    protected function assertTenantDatabaseExists(Tenant $tenant, string $message = ''): void
    {
        $dbName = $tenant->tenancy_db_name;

        try {
            $databases = DB::select("SHOW DATABASES LIKE '{$dbName}'");
            $exists = count($databases) > 0;
        } catch (\Exception $e) {
            $exists = false;
        }

        $this->assertTrue(
            $exists,
            $message ?: "Expected tenant database '{$dbName}' to exist"
        );
    }

    /**
     * Assert tenant database does not exist
     */
    protected function assertTenantDatabaseNotExists(Tenant $tenant, string $message = ''): void
    {
        $dbName = $tenant->tenancy_db_name;

        try {
            $databases = DB::select("SHOW DATABASES LIKE '{$dbName}'");
            $exists = count($databases) > 0;
        } catch (\Exception $e) {
            $exists = false;
        }

        $this->assertFalse(
            $exists,
            $message ?: "Expected tenant database '{$dbName}' to not exist"
        );
    }

    /**
     * Assert config value is set for tenant
     */
    protected function assertTenantConfig(string $key, mixed $expected, string $message = ''): void
    {
        $actual = config($key);

        $this->assertEquals(
            $expected,
            $actual,
            $message ?: "Expected config '{$key}' to be '{$expected}', got '{$actual}'"
        );
    }

    /**
     * Assert cache prefix is set for tenant
     */
    protected function assertTenantCachePrefix(Tenant $tenant, string $message = ''): void
    {
        $expectedPrefix = 'tenant_' . $tenant->id . '_cache';
        $actualPrefix = config('cache.prefix');

        $this->assertEquals(
            $expectedPrefix,
            $actualPrefix,
            $message ?: "Expected cache prefix '{$expectedPrefix}', got '{$actualPrefix}'"
        );
    }

    /**
     * Assert tenant filesystem disk is configured
     */
    protected function assertTenantFilesystemDisk(Tenant $tenant, string $message = ''): void
    {
        $expectedPath = 'tenants/' . $tenant->id;
        $actualPath = config('filesystems.disks.public.root');

        $this->assertStringContainsString(
            $expectedPath,
            $actualPath,
            $message ?: "Expected filesystem path to contain '{$expectedPath}'"
        );
    }
}
