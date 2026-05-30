import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { MappingPresetDto } from './api-types'
import { mappingPresetKeys } from './queries'
import {
  toCreateMappingPresetDto,
  toMappingPreset,
  type CreateMappingPresetInput,
  type MappingPreset,
} from './model'

/** Create a mapping preset (version 1). Invalidates the list so it refetches. */
export function useCreateMappingPreset(): UseMutationResult<
  MappingPreset,
  AppError,
  CreateMappingPresetInput
> {
  const queryClient = useQueryClient()
  return useMutation<MappingPreset, AppError, CreateMappingPresetInput>({
    mutationFn: async (input) => {
      const dto = await apiClient.post<MappingPresetDto>(
        '/admin/mapping-presets',
        toCreateMappingPresetDto(input),
      )
      return toMappingPreset(dto)
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: mappingPresetKeys.lists() })
    },
  })
}

/** Soft-delete a mapping preset (audited). Invalidates the list. */
export function useDeleteMappingPreset(): UseMutationResult<number, AppError, number> {
  const queryClient = useQueryClient()
  return useMutation<number, AppError, number>({
    mutationFn: async (id) => {
      await apiClient.delete(`/admin/mapping-presets/${String(id)}`)
      return id
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: mappingPresetKeys.lists() })
    },
  })
}
