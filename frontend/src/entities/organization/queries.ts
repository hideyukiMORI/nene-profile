import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { OrganizationListDto } from './api-types'
import { toOrganizationList, type OrganizationList, type PageParams } from './model'

/** Query-key factory. The single source for keys so invalidation stays correct. */
export const organizationKeys = {
  all: ['organizations'] as const,
  lists: () => [...organizationKeys.all, 'list'] as const,
  list: (page: PageParams) => [...organizationKeys.lists(), page] as const,
}

/** Paginated organizations list (superadmin). Data is mapped to the domain model. */
export function useOrganizations(page: PageParams): UseQueryResult<OrganizationList, AppError> {
  return useQuery<OrganizationList, AppError>({
    queryKey: organizationKeys.list(page),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(page.limit),
        offset: String(page.offset),
      })
      const dto = await apiClient.get<OrganizationListDto>(
        `/admin/organizations?${search.toString()}`,
        signal,
      )
      return toOrganizationList(dto)
    },
  })
}
