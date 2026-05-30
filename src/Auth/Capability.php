<?php

declare(strict_types=1);

namespace NeneProfile\Auth;

enum Capability
{
    case ManageOrganizations;
    case ManageUsers;
    case ManageOrganizationSettings;
    case ManagePresets;
    case ManageImportJobs;
    case ViewImportJobs;
}
