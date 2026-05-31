/** Wire shapes for /admin/organizations (snake_case, exactly as the API returns). */
export interface OrganizationDto {
  id: number
  name: string
  slug: string
  is_active: boolean
  custom_domain: string | null
  created_at: string
  updated_at: string
}

export interface OrganizationListDto {
  items: OrganizationDto[]
  total: number
  limit: number
  offset: number
}

export interface CreateOrganizationDto {
  name: string
  slug: string
  custom_domain?: string | null
}

export interface UpdateOrganizationDto {
  name?: string
  slug?: string
  is_active?: boolean
  custom_domain?: string | null
}
