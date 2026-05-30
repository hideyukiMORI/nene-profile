/** Roles and status mirror the API (terminology.md §5). Defined locally to keep
 *  the user entity decoupled from sibling entities (FSD). */
export type UserRole = 'superadmin' | 'admin' | 'member' | 'viewer'
export type UserStatus = 'active' | 'invited'

/** Wire shapes for /admin/users (snake_case, exactly as the API returns). */
export interface UserDto {
  id: number
  email: string
  role: UserRole
  organization_id: number | null
  status: UserStatus
  created_at: string
  updated_at: string
}

export interface UserListDto {
  items: UserDto[]
  total: number
  limit: number
  offset: number
}

export interface CreateUserDto {
  email: string
  password: string
  role: UserRole
}

export interface UpdateUserDto {
  role?: UserRole
  status?: UserStatus
  password?: string
}
