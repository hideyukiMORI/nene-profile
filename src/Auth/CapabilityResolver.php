<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

final class CapabilityResolver
{
    public static function resolve(string $path, string $method): ?Capability
    {
        $method = strtoupper($method);

        // Superadmin-only: cross-tenant organization management
        if (str_starts_with($path, '/admin/organizations')) {
            return Capability::ManageOrganizations;
        }

        // User management: admin-only (all methods)
        if (str_starts_with($path, '/admin/users')) {
            return Capability::ManageUsers;
        }

        // Audit logs: admin-only read (oversight)
        if (str_starts_with($path, '/admin/audit-logs')) {
            return Capability::ManageUsers;
        }

        // Organization settings: admin-only (all methods)
        if (str_starts_with($path, '/admin/organization-settings')) {
            return Capability::ManageOrganizationSettings;
        }

        // Mapping presets: mutations require ManagePresets (member+); reads are open to any authenticated user
        if (str_starts_with($path, '/admin/mapping-presets') && self::isMutationMethod($method)) {
            return Capability::ManagePresets;
        }

        // Import jobs: creation requires ManageImportJobs; reading requires ViewImportJobs
        if (str_starts_with($path, '/admin/import-jobs')) {
            if ($method === 'POST' && $path === '/admin/import-jobs') {
                return Capability::ManageImportJobs;
            }

            if ($method === 'GET' || $method === 'HEAD') {
                return Capability::ViewImportJobs;
            }
        }

        return null;
    }

    private static function isMutationMethod(string $method): bool
    {
        return !in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
    }
}
