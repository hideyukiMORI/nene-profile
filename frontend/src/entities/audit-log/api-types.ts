export interface AuditLogDto {
  id: number
  actor_user_id: number | null
  organization_id: number | null
  action: string
  entity_type: string
  entity_id: number | null
  before: Record<string, unknown> | null
  after: Record<string, unknown> | null
  created_at: string
}

export interface AuditLogListDto {
  items: AuditLogDto[]
  total: number
  limit: number
  offset: number
}
