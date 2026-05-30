import {
  useMutation,
  useQuery,
  useQueryClient,
  type UseMutationResult,
  type UseQueryResult,
} from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { OrganizationSettingsDto } from './api-types'
import {
  toOrganizationSettings,
  toUpdateOrganizationSettingsDto,
  type OrganizationSettings,
  type UpdateOrganizationSettingsInput,
} from './model'

export const organizationSettingsKeys = {
  all: ['organization-settings'] as const,
}

/** The caller organization's settings (singleton). */
export function useOrganizationSettings(): UseQueryResult<OrganizationSettings, AppError> {
  return useQuery<OrganizationSettings, AppError>({
    queryKey: organizationSettingsKeys.all,
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<OrganizationSettingsDto>(
        '/admin/organization-settings',
        signal,
      )
      return toOrganizationSettings(dto)
    },
  })
}

/** Update settings (admin). Refreshes the cached singleton on success. */
export function useUpdateOrganizationSettings(): UseMutationResult<
  OrganizationSettings,
  AppError,
  UpdateOrganizationSettingsInput
> {
  const queryClient = useQueryClient()
  return useMutation<OrganizationSettings, AppError, UpdateOrganizationSettingsInput>({
    mutationFn: async (input) => {
      const dto = await apiClient.patch<OrganizationSettingsDto>(
        '/admin/organization-settings',
        toUpdateOrganizationSettingsDto(input),
      )
      return toOrganizationSettings(dto)
    },
    onSuccess: (data) => {
      queryClient.setQueryData(organizationSettingsKeys.all, data)
    },
  })
}
