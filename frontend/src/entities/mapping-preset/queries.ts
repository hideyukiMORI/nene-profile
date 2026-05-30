import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { MappingPresetListDto } from './api-types'
import { toMappingPresetList, type MappingPresetList, type PageParams } from './model'

export const mappingPresetKeys = {
  all: ['mapping-presets'] as const,
  lists: () => [...mappingPresetKeys.all, 'list'] as const,
  list: (page: PageParams) => [...mappingPresetKeys.lists(), page] as const,
}

/** Paginated mapping-presets list. */
export function useMappingPresets(page: PageParams): UseQueryResult<MappingPresetList, AppError> {
  return useQuery<MappingPresetList, AppError>({
    queryKey: mappingPresetKeys.list(page),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(page.limit),
        offset: String(page.offset),
      })
      const dto = await apiClient.get<MappingPresetListDto>(
        `/admin/mapping-presets?${search.toString()}`,
        signal,
      )
      return toMappingPresetList(dto)
    },
  })
}
