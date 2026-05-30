<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

enum Role: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';

    public function hasCapability(Capability $capability): bool
    {
        return match ($this) {
            self::Superadmin => true,
            self::Admin      => $capability !== Capability::ManageOrganizations,
            self::Member     => match ($capability) {
                Capability::ManagePresets,
                Capability::ManageImportJobs,
                Capability::ViewImportJobs    => true,
                Capability::ManageOrganizations,
                Capability::ManageUsers,
                Capability::ManageOrganizationSettings => false,
            },
            self::Viewer => $capability === Capability::ViewImportJobs,
        };
    }
}
