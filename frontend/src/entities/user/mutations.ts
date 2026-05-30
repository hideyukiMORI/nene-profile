import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { UserDto } from './api-types'
import { userKeys } from './queries'
import {
  toCreateUserDto,
  toUpdateUserDto,
  toUser,
  type CreateUserInput,
  type UpdateUserInput,
  type User,
} from './model'

/** Create an operator account (admin). Invalidates the list so it refetches. */
export function useCreateUser(): UseMutationResult<User, AppError, CreateUserInput> {
  const queryClient = useQueryClient()
  return useMutation<User, AppError, CreateUserInput>({
    mutationFn: async (input) => {
      const dto = await apiClient.post<UserDto>('/admin/users', toCreateUserDto(input))
      return toUser(dto)
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: userKeys.lists() })
    },
  })
}

/** Update an operator account (admin). Invalidates the list so it refetches. */
export function useUpdateUser(): UseMutationResult<User, AppError, UpdateUserInput> {
  const queryClient = useQueryClient()
  return useMutation<User, AppError, UpdateUserInput>({
    mutationFn: async (input) => {
      const dto = await apiClient.patch<UserDto>(
        `/admin/users/${String(input.id)}`,
        toUpdateUserDto(input),
      )
      return toUser(dto)
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: userKeys.lists() })
    },
  })
}

/** Delete an operator account (admin, audited). Invalidates the list. */
export function useDeleteUser(): UseMutationResult<number, AppError, number> {
  const queryClient = useQueryClient()
  return useMutation<number, AppError, number>({
    mutationFn: async (id) => {
      await apiClient.delete(`/admin/users/${String(id)}`)
      return id
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: userKeys.lists() })
    },
  })
}
