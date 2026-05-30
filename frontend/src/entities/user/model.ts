import type {
  CreateUserDto,
  UpdateUserDto,
  UserDto,
  UserListDto,
  UserRole,
  UserStatus,
} from './api-types'

/** Domain model (camelCase). UI only ever sees this shape, never the DTO. */
export interface User {
  id: number
  email: string
  role: UserRole
  organizationId: number | null
  status: UserStatus
  createdAt: string
  updatedAt: string
}

export interface UserList {
  items: User[]
  total: number
  limit: number
  offset: number
}

export interface CreateUserInput {
  email: string
  password: string
  role: UserRole
}

/** Edit input: role + status always sent; password only when non-empty. */
export interface UpdateUserInput {
  id: number
  role: UserRole
  status: UserStatus
  password?: string
}

export interface PageParams {
  limit: number
  offset: number
}

export function toUser(dto: UserDto): User {
  return {
    id: dto.id,
    email: dto.email,
    role: dto.role,
    organizationId: dto.organization_id,
    status: dto.status,
    createdAt: dto.created_at,
    updatedAt: dto.updated_at,
  }
}

export function toUserList(dto: UserListDto): UserList {
  return {
    items: dto.items.map(toUser),
    total: dto.total,
    limit: dto.limit,
    offset: dto.offset,
  }
}

export function toCreateUserDto(input: CreateUserInput): CreateUserDto {
  return { email: input.email, password: input.password, role: input.role }
}

export function toUpdateUserDto(input: UpdateUserInput): UpdateUserDto {
  return {
    role: input.role,
    status: input.status,
    ...(input.password !== undefined && input.password !== '' ? { password: input.password } : {}),
  }
}
