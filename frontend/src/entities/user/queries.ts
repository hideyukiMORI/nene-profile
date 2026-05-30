import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { UserListDto } from './api-types'
import { toUserList, type PageParams, type UserList } from './model'

export const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
  list: (page: PageParams) => [...userKeys.lists(), page] as const,
}

/** Paginated users list within the caller's organization (admin). */
export function useUsers(page: PageParams): UseQueryResult<UserList, AppError> {
  return useQuery<UserList, AppError>({
    queryKey: userKeys.list(page),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(page.limit),
        offset: String(page.offset),
      })
      const dto = await apiClient.get<UserListDto>(`/admin/users?${search.toString()}`, signal)
      return toUserList(dto)
    },
  })
}
