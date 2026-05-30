import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { AuditLogListDto } from './api-types'
import { toAuditLogList, type AuditLogList, type PageParams } from './model'

export const auditLogKeys = {
  all: ['audit-logs'] as const,
  lists: () => [...auditLogKeys.all, 'list'] as const,
  list: (page: PageParams) => [...auditLogKeys.lists(), page] as const,
}

/** Paginated audit log within the caller's organization. */
export function useAuditLogs(page: PageParams): UseQueryResult<AuditLogList, AppError> {
  return useQuery<AuditLogList, AppError>({
    queryKey: auditLogKeys.list(page),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(page.limit),
        offset: String(page.offset),
      })
      const dto = await apiClient.get<AuditLogListDto>(
        `/admin/audit-logs?${search.toString()}`,
        signal,
      )
      return toAuditLogList(dto)
    },
  })
}
