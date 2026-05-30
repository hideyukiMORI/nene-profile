/** Roles, mirroring the API (terminology.md §5). */
export type UserRole = 'superadmin' | 'admin' | 'member' | 'viewer'

/** Capabilities, mirroring the API (terminology.md §6). */
export type Capability =
  | 'manage_organizations'
  | 'manage_users'
  | 'manage_organization_settings'
  | 'manage_presets'
  | 'manage_import_jobs'
  | 'view_import_jobs'

const ROLE_CAPABILITIES: Record<UserRole, readonly Capability[]> = {
  superadmin: [
    'manage_organizations',
    'manage_users',
    'manage_organization_settings',
    'manage_presets',
    'manage_import_jobs',
    'view_import_jobs',
  ],
  admin: [
    'manage_users',
    'manage_organization_settings',
    'manage_presets',
    'manage_import_jobs',
    'view_import_jobs',
  ],
  member: ['manage_presets', 'manage_import_jobs', 'view_import_jobs'],
  viewer: ['view_import_jobs'],
}

/**
 * UI-side capability check. This mirrors the server's RBAC for showing/hiding
 * actions only — the API is the authority and enforces every mutation.
 */
export function hasCapability(role: UserRole, capability: Capability): boolean {
  return ROLE_CAPABILITIES[role].includes(capability)
}

export function isSuperadmin(role: UserRole): boolean {
  return role === 'superadmin'
}

export function isAdmin(role: UserRole): boolean {
  return role === 'admin' || role === 'superadmin'
}
