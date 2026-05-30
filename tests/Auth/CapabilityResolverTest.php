<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Auth;

use NeneProfile\Auth\Capability;
use NeneProfile\Auth\CapabilityResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CapabilityResolverTest extends TestCase
{
    /** @return array<string, array{string, string, Capability|null}> */
    public static function resolveProvider(): array
    {
        return [
            'GET /health returns null'                           => ['/health', 'GET', null],
            'POST /admin/auth/login returns null'               => ['/admin/auth/login', 'POST', null],

            'GET /admin/organizations returns ManageOrganizations'  => ['/admin/organizations', 'GET', Capability::ManageOrganizations],
            'POST /admin/organizations returns ManageOrganizations' => ['/admin/organizations', 'POST', Capability::ManageOrganizations],

            'GET /admin/users returns ManageUsers'              => ['/admin/users', 'GET', Capability::ManageUsers],
            'POST /admin/users returns ManageUsers'             => ['/admin/users', 'POST', Capability::ManageUsers],

            'GET /admin/organization-settings returns ManageOrganizationSettings'   => ['/admin/organization-settings', 'GET', Capability::ManageOrganizationSettings],
            'PATCH /admin/organization-settings returns ManageOrganizationSettings' => ['/admin/organization-settings', 'PATCH', Capability::ManageOrganizationSettings],

            'GET /admin/mapping-presets returns null (open read)' => ['/admin/mapping-presets', 'GET', null],
            'POST /admin/mapping-presets returns ManagePresets'  => ['/admin/mapping-presets', 'POST', Capability::ManagePresets],
            'PATCH /admin/mapping-presets/1 returns ManagePresets' => ['/admin/mapping-presets/1', 'PATCH', Capability::ManagePresets],
            'DELETE /admin/mapping-presets/1 returns ManagePresets' => ['/admin/mapping-presets/1', 'DELETE', Capability::ManagePresets],

            'POST /admin/import-jobs returns ManageImportJobs'  => ['/admin/import-jobs', 'POST', Capability::ManageImportJobs],
            'GET /admin/import-jobs returns ViewImportJobs'     => ['/admin/import-jobs', 'GET', Capability::ViewImportJobs],
            'GET /admin/import-jobs/1 returns ViewImportJobs'   => ['/admin/import-jobs/1', 'GET', Capability::ViewImportJobs],
            'GET /admin/import-jobs/1/errors returns ViewImportJobs' => ['/admin/import-jobs/1/errors', 'GET', Capability::ViewImportJobs],
            'GET /admin/import-jobs/1/export.json returns ViewImportJobs' => ['/admin/import-jobs/1/export.json', 'GET', Capability::ViewImportJobs],
        ];
    }

    #[DataProvider('resolveProvider')]
    public function test_resolve_returns_expected_capability(string $path, string $method, ?Capability $expected): void
    {
        $this->assertSame($expected, CapabilityResolver::resolve($path, $method));
    }
}
