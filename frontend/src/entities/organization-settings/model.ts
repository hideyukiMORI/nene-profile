import type { Encoding, OrganizationSettingsDto, UpdateOrganizationSettingsDto } from './api-types'

export interface OrganizationSettings {
  organizationId: number
  defaultEncoding: Encoding
  maxFileSizeBytes: number
  clearBearerTokenSet: boolean
}

/** Edit input: encoding + size always sent; token only when non-empty. */
export interface UpdateOrganizationSettingsInput {
  defaultEncoding: Encoding
  maxFileSizeBytes: number
  clearBearerToken?: string
}

export function toOrganizationSettings(dto: OrganizationSettingsDto): OrganizationSettings {
  return {
    organizationId: dto.organization_id,
    defaultEncoding: dto.default_encoding,
    maxFileSizeBytes: dto.max_file_size_bytes,
    clearBearerTokenSet: dto.clear_bearer_token_set,
  }
}

export function toUpdateOrganizationSettingsDto(
  input: UpdateOrganizationSettingsInput,
): UpdateOrganizationSettingsDto {
  return {
    default_encoding: input.defaultEncoding,
    max_file_size_bytes: input.maxFileSizeBytes,
    ...(input.clearBearerToken !== undefined && input.clearBearerToken !== ''
      ? { clear_bearer_token: input.clearBearerToken }
      : {}),
  }
}
