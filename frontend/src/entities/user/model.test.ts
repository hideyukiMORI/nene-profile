import { describe, expect, it } from 'vitest'
import type { UserDto } from './api-types'
import { toCreateUserDto, toUpdateUserDto, toUser } from './model'

const dto: UserDto = {
  id: 5,
  email: 'op@example.com',
  role: 'member',
  organization_id: 7,
  status: 'invited',
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-31T00:00:00Z',
}

describe('user mappers', () => {
  it('maps the DTO to the domain model', () => {
    expect(toUser(dto)).toEqual({
      id: 5,
      email: 'op@example.com',
      role: 'member',
      organizationId: 7,
      status: 'invited',
      createdAt: '2026-05-30T00:00:00Z',
      updatedAt: '2026-05-31T00:00:00Z',
    })
  })

  it('builds the create payload', () => {
    expect(
      toCreateUserDto({ email: 'op@example.com', password: 'supersecret', role: 'member' }),
    ).toEqual({ email: 'op@example.com', password: 'supersecret', role: 'member' })
  })

  it('omits password from the update payload when blank', () => {
    expect(toUpdateUserDto({ id: 5, role: 'admin', status: 'active', password: '' })).toEqual({
      role: 'admin',
      status: 'active',
    })
    expect(toUpdateUserDto({ id: 5, role: 'admin', status: 'active' })).toEqual({
      role: 'admin',
      status: 'active',
    })
  })

  it('includes password when provided', () => {
    expect(
      toUpdateUserDto({ id: 5, role: 'admin', status: 'active', password: 'newsecret1' }),
    ).toEqual({ role: 'admin', status: 'active', password: 'newsecret1' })
  })
})
