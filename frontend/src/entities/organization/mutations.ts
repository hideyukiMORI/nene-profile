import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { OrganizationDto } from './api-types'
import { organizationKeys } from './queries'
import {
  toCreateOrganizationDto,
  toOrganization,
  type CreateOrganizationInput,
  type Organization,
} from './model'

/** Create a tenant (superadmin). Invalidates the list so it refetches. */
export function useCreateOrganization(): UseMutationResult<
  Organization,
  AppError,
  CreateOrganizationInput
> {
  const queryClient = useQueryClient()
  return useMutation<Organization, AppError, CreateOrganizationInput>({
    mutationFn: async (input) => {
      const dto = await apiClient.post<OrganizationDto>(
        '/admin/organizations',
        toCreateOrganizationDto(input),
      )
      return toOrganization(dto)
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: organizationKeys.lists() })
    },
  })
}

/** Delete a tenant (superadmin, audited). Invalidates the list so it refetches. */
export function useDeleteOrganization(): UseMutationResult<number, AppError, number> {
  const queryClient = useQueryClient()
  return useMutation<number, AppError, number>({
    mutationFn: async (id) => {
      await apiClient.delete(`/admin/organizations/${String(id)}`)
      return id
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: organizationKeys.lists() })
    },
  })
}
