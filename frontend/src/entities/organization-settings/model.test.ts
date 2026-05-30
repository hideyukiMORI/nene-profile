import { describe, expect, it } from 'vitest'
import type { OrganizationSettingsDto } from './api-types'
import { toOrganizationSettings, toUpdateOrganizationSettingsDto } from './model'

const dto: OrganizationSettingsDto = {
  organization_id: 7,
  default_encoding: 'shift_jis',
  max_file_size_bytes: 2048,
  clear_bearer_token_set: true,
}

describe('organization-settings mappers', () => {
  it('maps the DTO to the domain model', () => {
    expect(toOrganizationSettings(dto)).toEqual({
      organizationId: 7,
      defaultEncoding: 'shift_jis',
      maxFileSizeBytes: 2048,
      clearBearerTokenSet: true,
    })
  })

  it('omits the token from the update payload when blank', () => {
    expect(
      toUpdateOrganizationSettingsDto({ defaultEncoding: 'utf-8', maxFileSizeBytes: 4096 }),
    ).toEqual({ default_encoding: 'utf-8', max_file_size_bytes: 4096 })

    expect(
      toUpdateOrganizationSettingsDto({
        defaultEncoding: 'utf-8',
        maxFileSizeBytes: 4096,
        clearBearerToken: '',
      }),
    ).toEqual({ default_encoding: 'utf-8', max_file_size_bytes: 4096 })
  })

  it('includes the token when provided', () => {
    expect(
      toUpdateOrganizationSettingsDto({
        defaultEncoding: 'auto',
        maxFileSizeBytes: 1024,
        clearBearerToken: 'tok_1',
      }),
    ).toEqual({ default_encoding: 'auto', max_file_size_bytes: 1024, clear_bearer_token: 'tok_1' })
  })
})
