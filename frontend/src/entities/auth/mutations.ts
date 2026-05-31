import { useMutation, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { ChangePasswordRequestDto, LoginRequestDto, LoginResponseDto } from './api-types'
import { authStore, type AuthSession } from './model'

function toSession(dto: LoginResponseDto): AuthSession {
  return {
    token: dto.token,
    expiresAt: dto.expires_at,
    email: dto.email,
    role: dto.role,
    orgId: dto.org_id,
  }
}

/**
 * Authenticates and stores the session in the in-memory auth store on success.
 * Returns the typed mutation; features call it and react to status/error.
 */
export function useLogin(): UseMutationResult<AuthSession, AppError, LoginRequestDto> {
  return useMutation<AuthSession, AppError, LoginRequestDto>({
    mutationFn: async (input) => {
      const dto = await apiClient.post<LoginResponseDto>('/admin/auth/login', input)
      const session = toSession(dto)
      authStore.setSession(session)
      return session
    },
  })
}

/** Changes the authenticated user's own password. Returns undefined on success (204). */
export function useChangeOwnPassword(): UseMutationResult<
  undefined,
  AppError,
  ChangePasswordRequestDto
> {
  return useMutation<undefined, AppError, ChangePasswordRequestDto>({
    mutationFn: async (input) => {
      await apiClient.patch('/admin/auth/me/password', input)
      return undefined
    },
  })
}
