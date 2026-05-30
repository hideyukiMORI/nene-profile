<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use NeneProfile\Auth\Capability;
use NeneProfile\Auth\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    /** @return array<string, array{Role, Capability, bool}> */
    public static function capabilityProvider(): array
    {
        return [
            // superadmin has all capabilities
            'superadmin manage_organizations'       => [Role::Superadmin, Capability::ManageOrganizations, true],
            'superadmin manage_presets'             => [Role::Superadmin, Capability::ManagePresets, true],
            'superadmin view_import_jobs'           => [Role::Superadmin, Capability::ViewImportJobs, true],

            // admin cannot manage organizations
            'admin manage_organizations'            => [Role::Admin, Capability::ManageOrganizations, false],
            'admin manage_users'                    => [Role::Admin, Capability::ManageUsers, true],
            'admin manage_presets'                  => [Role::Admin, Capability::ManagePresets, true],
            'admin manage_import_jobs'              => [Role::Admin, Capability::ManageImportJobs, true],
            'admin view_import_jobs'                => [Role::Admin, Capability::ViewImportJobs, true],
            'admin manage_organization_settings'    => [Role::Admin, Capability::ManageOrganizationSettings, true],

            // member: presets + import jobs only
            'member manage_presets'                 => [Role::Member, Capability::ManagePresets, true],
            'member manage_import_jobs'             => [Role::Member, Capability::ManageImportJobs, true],
            'member view_import_jobs'               => [Role::Member, Capability::ViewImportJobs, true],
            'member manage_users'                   => [Role::Member, Capability::ManageUsers, false],
            'member manage_organizations'           => [Role::Member, Capability::ManageOrganizations, false],
            'member manage_organization_settings'   => [Role::Member, Capability::ManageOrganizationSettings, false],

            // viewer: read-only
            'viewer view_import_jobs'               => [Role::Viewer, Capability::ViewImportJobs, true],
            'viewer manage_import_jobs'             => [Role::Viewer, Capability::ManageImportJobs, false],
            'viewer manage_presets'                 => [Role::Viewer, Capability::ManagePresets, false],
            'viewer manage_organizations'           => [Role::Viewer, Capability::ManageOrganizations, false],
        ];
    }

    #[DataProvider('capabilityProvider')]
    public function test_hasCapability_returns_expected_result(Role $role, Capability $capability, bool $expected): void
    {
        $this->assertSame($expected, $role->hasCapability($capability));
    }

    public function test_superadmin_has_all_capabilities(): void
    {
        foreach (Capability::cases() as $capability) {
            $this->assertTrue(
                Role::Superadmin->hasCapability($capability),
                "Superadmin should have capability {$capability->name}",
            );
        }
    }

    public function test_viewer_has_only_view_import_jobs(): void
    {
        foreach (Capability::cases() as $capability) {
            $expected = $capability === Capability::ViewImportJobs;
            $this->assertSame(
                $expected,
                Role::Viewer->hasCapability($capability),
                "Viewer capability {$capability->name} expected: " . ($expected ? 'true' : 'false'),
            );
        }
    }
}
