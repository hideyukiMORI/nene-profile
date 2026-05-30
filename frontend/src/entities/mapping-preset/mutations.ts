import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { MappingPresetDto } from './api-types'
import { mappingPresetKeys } from './queries'
import {
  toCreateMappingPresetDto,
  toMappingPreset,
  type CreateMappingPresetInput,
  type MappingPreset,
  type UpdateMappingPresetInput,
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

/** Update a preset (creates a new version). Invalidates list + detail. */
export function useUpdateMappingPreset(): UseMutationResult<
  MappingPreset,
  AppError,
  UpdateMappingPresetInput
> {
  const queryClient = useQueryClient()
  return useMutation<MappingPreset, AppError, UpdateMappingPresetInput>({
    mutationFn: async (input) => {
      const dto = await apiClient.patch<MappingPresetDto>(
        `/admin/mapping-presets/${String(input.id)}`,
        toCreateMappingPresetDto(input),
      )
      return toMappingPreset(dto)
    },
    onSuccess: (_data, input) => {
      void queryClient.invalidateQueries({ queryKey: mappingPresetKeys.lists() })
      void queryClient.invalidateQueries({ queryKey: mappingPresetKeys.detail(input.id) })
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
