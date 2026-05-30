import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { MappingPresetDto, MappingPresetListDto } from './api-types'
import {
  toMappingPresetDetail,
  toMappingPresetList,
  type MappingPresetDetail,
  type MappingPresetList,
  type PageParams,
} from './model'

export const mappingPresetKeys = {
  all: ['mapping-presets'] as const,
  lists: () => [...mappingPresetKeys.all, 'list'] as const,
  list: (page: PageParams) => [...mappingPresetKeys.lists(), page] as const,
  detail: (id: number) => [...mappingPresetKeys.all, 'detail', id] as const,
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

/** Single preset with its definition parsed for editing. Enabled when id is set. */
export function useMappingPreset(id: number | null): UseQueryResult<MappingPresetDetail, AppError> {
  return useQuery<MappingPresetDetail, AppError>({
    queryKey: mappingPresetKeys.detail(id ?? 0),
    enabled: id !== null,
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<MappingPresetDto>(
        `/admin/mapping-presets/${String(id)}`,
        signal,
      )
      return toMappingPresetDetail(dto)
    },
  })
}
