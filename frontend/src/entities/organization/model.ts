import type {
  CreateOrganizationDto,
  OrganizationDto,
  OrganizationListDto,
  UpdateOrganizationDto,
} from './api-types'

/** Domain model (camelCase). UI code only ever sees this shape, never the DTO. */
export interface Organization {
  id: number
  name: string
  slug: string
  isActive: boolean
  customDomain: string | null
  createdAt: string
  updatedAt: string
}

export interface OrganizationList {
  items: Organization[]
  total: number
  limit: number
  offset: number
}

/** Input accepted by the create form / mutation (camelCase). */
export interface CreateOrganizationInput {
  name: string
  slug: string
  customDomain?: string | null
}

export interface UpdateOrganizationInput {
  id: number
  name?: string
  slug?: string
  isActive?: boolean
  customDomain?: string | null
}

/** Pagination cursor used by the list query and page. */
export interface PageParams {
  limit: number
  offset: number
}

export function toOrganization(dto: OrganizationDto): Organization {
  return {
    id: dto.id,
    name: dto.name,
    slug: dto.slug,
    isActive: dto.is_active,
    customDomain: dto.custom_domain,
    createdAt: dto.created_at,
    updatedAt: dto.updated_at,
  }
}

export function toOrganizationList(dto: OrganizationListDto): OrganizationList {
  return {
    items: dto.items.map(toOrganization),
    total: dto.total,
    limit: dto.limit,
    offset: dto.offset,
  }
}

export function toCreateOrganizationDto(input: CreateOrganizationInput): CreateOrganizationDto {
  return {
    name: input.name,
    slug: input.slug,
    ...(input.customDomain !== undefined && input.customDomain !== ''
      ? { custom_domain: input.customDomain }
      : {}),
  }
}

export function toUpdateOrganizationDto(input: UpdateOrganizationInput): UpdateOrganizationDto {
  const dto: UpdateOrganizationDto = {}
  if (input.name !== undefined) dto.name = input.name
  if (input.slug !== undefined) dto.slug = input.slug
  if (input.isActive !== undefined) dto.is_active = input.isActive
  if (input.customDomain !== undefined) dto.custom_domain = input.customDomain
  return dto
}
