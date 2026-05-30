import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { ImportJobErrorListDto, ImportJobListDto } from './api-types'
import {
  toImportJobErrors,
  toImportJobList,
  type ImportJobError,
  type ImportJobList,
  type PageParams,
} from './model'

export const importJobKeys = {
  all: ['import-jobs'] as const,
  lists: () => [...importJobKeys.all, 'list'] as const,
  list: (page: PageParams) => [...importJobKeys.lists(), page] as const,
  errors: (id: number) => [...importJobKeys.all, 'errors', id] as const,
}

/** Paginated import-jobs list. */
export function useImportJobs(page: PageParams): UseQueryResult<ImportJobList, AppError> {
  return useQuery<ImportJobList, AppError>({
    queryKey: importJobKeys.list(page),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(page.limit),
        offset: String(page.offset),
      })
      const dto = await apiClient.get<ImportJobListDto>(
        `/admin/import-jobs?${search.toString()}`,
        signal,
      )
      return toImportJobList(dto)
    },
  })
}

/** Error rows for a job. Enabled only when an id is provided (detail view). */
export function useImportJobErrors(id: number | null): UseQueryResult<ImportJobError[], AppError> {
  return useQuery<ImportJobError[], AppError>({
    queryKey: importJobKeys.errors(id ?? 0),
    enabled: id !== null,
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<ImportJobErrorListDto>(
        `/admin/import-jobs/${String(id)}/errors`,
        signal,
      )
      return toImportJobErrors(dto)
    },
  })
}
