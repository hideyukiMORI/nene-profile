import { describe, expect, it } from 'vitest'
import { hasCapability, isAdmin, isSuperadmin } from './capabilities'

describe('capabilities', () => {
  it('superadmin has every capability', () => {
    expect(hasCapability('superadmin', 'manage_organizations')).toBe(true)
    expect(hasCapability('superadmin', 'manage_users')).toBe(true)
    expect(hasCapability('superadmin', 'view_import_jobs')).toBe(true)
  })

  it('admin manages users but not organizations', () => {
    expect(hasCapability('admin', 'manage_users')).toBe(true)
    expect(hasCapability('admin', 'manage_organizations')).toBe(false)
  })

  it('member can manage presets/jobs but not users', () => {
    expect(hasCapability('member', 'manage_presets')).toBe(true)
    expect(hasCapability('member', 'manage_import_jobs')).toBe(true)
    expect(hasCapability('member', 'manage_users')).toBe(false)
  })

  it('viewer can only view import jobs', () => {
    expect(hasCapability('viewer', 'view_import_jobs')).toBe(true)
    expect(hasCapability('viewer', 'manage_import_jobs')).toBe(false)
    expect(hasCapability('viewer', 'manage_presets')).toBe(false)
  })

  it('isSuperadmin / isAdmin reflect the role hierarchy', () => {
    expect(isSuperadmin('superadmin')).toBe(true)
    expect(isSuperadmin('admin')).toBe(false)

    expect(isAdmin('admin')).toBe(true)
    expect(isAdmin('superadmin')).toBe(true)
    expect(isAdmin('member')).toBe(false)
  })
})
