import { describe, expect, it } from 'vitest'
import type { OrganizationDto } from './api-types'
import { toCreateOrganizationDto, toOrganization, toOrganizationList } from './model'

const dto: OrganizationDto = {
  id: 9,
  name: 'Acme',
  slug: 'acme',
  is_active: true,
  custom_domain: null,
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-31T00:00:00Z',
}

describe('organization mappers', () => {
  it('maps the DTO to the camelCase domain model', () => {
    const org = toOrganization(dto)

    expect(org).toEqual({
      id: 9,
      name: 'Acme',
      slug: 'acme',
      isActive: true,
      customDomain: null,
      createdAt: '2026-05-30T00:00:00Z',
      updatedAt: '2026-05-31T00:00:00Z',
    })
  })

  it('maps a paginated list envelope', () => {
    const list = toOrganizationList({ items: [dto], total: 1, limit: 20, offset: 0 })

    expect(list.total).toBe(1)
    expect(list.items).toHaveLength(1)
    expect(list.items[0]?.slug).toBe('acme')
  })

  it('omits custom_domain from the create payload when empty', () => {
    expect(toCreateOrganizationDto({ name: 'Acme', slug: 'acme', customDomain: '' })).toEqual({
      name: 'Acme',
      slug: 'acme',
    })
    expect(toCreateOrganizationDto({ name: 'Acme', slug: 'acme' })).toEqual({
      name: 'Acme',
      slug: 'acme',
    })
  })

  it('includes custom_domain when provided', () => {
    expect(
      toCreateOrganizationDto({ name: 'Acme', slug: 'acme', customDomain: 'acme.example.com' }),
    ).toEqual({ name: 'Acme', slug: 'acme', custom_domain: 'acme.example.com' })
  })
})
