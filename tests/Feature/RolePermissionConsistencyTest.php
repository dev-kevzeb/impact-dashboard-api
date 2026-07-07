<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regression test: guarantees the permission matrix for the three seeded
 * roles (admin, project-manager, country-manager) matches the source of
 * truth declared in `database/seeders/RolePermissionSeeder.php`.
 *
 * If a developer adds or removes a permission in the seeder without also
 * updating the `$expectedPermissions` map below, this test will fail in CI
 * before the change reaches production.
 */
class RolePermissionConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Source of truth: role => list of permission names that MUST be assigned.
     * Mirrors `RolePermissionSeeder::run()`. Update both files together.
     */
    private array $expectedPermissions = [
        'admin' => [
            'admin_dashboard:read',
            'programs:read',
            'projects:read',
            'projects:weight',
            'users:read',
            'users:write',
            'user_states:read',
            'user_states:write',
            'program_states:read',
            'program_states:write',
            'project_states:read',
            'project_states:write',
            'measures:read',
            'measures:write',
            'sdgs:read',
            'sdgs:write',
            'countries:read',
            'countries:write',
            'countries:activate',
            'currencies:read',
            'currencies:write',
            'donors:read',
            'donors:create',
            'donors:update',
            'donors:delete',
            'beneficiaries:read',
            'beneficiaries:write',
            'projects:progress',
            'kpas:read',
            'kpas:write',
            'indicators:read',
            'indicators:write',
            'indicator_types:read',
            'indicator_types:write',
            'agencies:read',
            'agencies:write',
            'country_dashboard_shares:read',
            'country_join_requests:read',
            'country_kpas:read',
            'country_kpas:write',
            'stats:read',
        ],
        'project-manager' => [
            'programs:read',
            'programs:write',
            'program_country_user_roles:read',
            'program_country_user_roles:write',
            'projects:read',
            'projects:progress',
            'projects:write',
            'projects:create',
            'projects:delete',
            'projects:weight',
            'contacts:read',
            'contacts:write',
            'programs:view_by_country',
            'projects:view_by_country',
            'country_join_requests:read',
            'country_join_requests:write',
            'donors:read',
        ],
        'country-manager' => [
            'programs:read',
            'programs:view_by_country',
            'projects:read',
            'projects:view_by_country',
            'projects:weight',
            'projects:progress',
            'donors:read',
            'country_dashboard_shares:read',
            'country_dashboard_shares:write',
            'country_join_requests:read',
            'country_join_requests:write',
            'countries:activate',
            'agencies:read',
            'stats:read',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * @dataProvider roleProvider
     */
    public function test_role_has_exactly_the_expected_permissions(string $roleName): void
    {
        $expected = $this->expectedPermissions[$roleName];

        $role = Role::where('name', $roleName)
            ->where('guard_name', 'api')
            ->firstOrFail();

        $actual = $role->permissions->pluck('name')->sort()->values()->all();
        $expectedSorted = collect($expected)->sort()->values()->all();

        $missing = array_values(array_diff($expectedSorted, $actual));
        $extra   = array_values(array_diff($actual, $expectedSorted));

        $this->assertSame(
            $expectedSorted,
            $actual,
            sprintf(
                "Permission drift on role '%s'.%sMissing: [%s]%sUnexpected: [%s]%sUpdate database/seeders/RolePermissionSeeder.php and the \$expectedPermissions map in this test together.",
                $roleName,
                PHP_EOL,
                implode(', ', $missing),
                PHP_EOL,
                implode(', ', $extra),
                PHP_EOL
            )
        );
    }

    /**
     * @dataProvider roleProvider
     */
    public function test_role_does_not_hold_wildcard_permission(string $roleName): void
    {
        $role = Role::where('name', $roleName)
            ->where('guard_name', 'api')
            ->firstOrFail();

        $this->assertFalse(
            $role->hasPermissionTo('*:*'),
            "Role '{$roleName}' must never carry the wildcard '*:*' permission. " .
            "Wildcard is reserved for the admin user grants only."
        );
    }

    public function test_three_seeded_roles_exist(): void
    {
        $expected = array_keys($this->expectedPermissions);

        foreach ($expected as $roleName) {
            $this->assertDatabaseHas('role', [
                'name'       => $roleName,
                'guard_name' => 'api',
            ]);
        }

        $this->assertCount(
            count($expected),
            Role::whereIn('name', $expected)->get(),
            'The seeders must keep exactly the three roles documented in this test.'
        );
    }

    public function test_admin_has_more_permissions_than_other_roles(): void
    {
        $adminCount       = count($this->expectedPermissions['admin']);
        $projectMgrCount  = count($this->expectedPermissions['project-manager']);
        $countryMgrCount  = count($this->expectedPermissions['country-manager']);

        $this->assertGreaterThan($projectMgrCount, $adminCount);
        $this->assertGreaterThan($countryMgrCount, $adminCount);
    }

    public function test_expected_permission_map_matches_seeder_keys(): void
    {
        $expected = array_keys($this->expectedPermissions);

        foreach ($expected as $roleName) {
            $exists = Role::where('name', $roleName)
                ->where('guard_name', 'api')
                ->exists();

            $this->assertTrue(
                $exists,
                "Role '{$roleName}' is declared in \$expectedPermissions but is not seeded. " .
                "Add it to RoleSeeder and RolePermissionSeeder, then re-run the suite."
            );
        }
    }

    public static function roleProvider(): array
    {
        return [
            'admin'           => ['admin'],
            'project-manager' => ['project-manager'],
            'country-manager' => ['country-manager'],
        ];
    }
}
