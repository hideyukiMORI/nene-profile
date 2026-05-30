import type { UserRole } from './capabilities'

/** Wire shapes for POST /admin/auth/login (snake_case, as the API returns). */
export interface LoginRequestDto {
  email: string
  password: string
}

export interface LoginResponseDto {
  token: string
  expires_at: string
  email: string
  role: UserRole
  org_id: number | null
}
