import type { AuditLogDto, AuditLogListDto } from './api-types'

export interface AuditLog {
  id: number
  actorUserId: number | null
  organizationId: number | null
  action: string
  entityType: string
  entityId: number | null
  before: Record<string, unknown> | null
  after: Record<string, unknown> | null
  createdAt: string
}

export interface AuditLogList {
  items: AuditLog[]
  total: number
  limit: number
  offset: number
}

export interface PageParams {
  limit: number
  offset: number
}

function toAuditLog(dto: AuditLogDto): AuditLog {
  return {
    id: dto.id,
    actorUserId: dto.actor_user_id,
    organizationId: dto.organization_id,
    action: dto.action,
    entityType: dto.entity_type,
    entityId: dto.entity_id,
    before: dto.before,
    after: dto.after,
    createdAt: dto.created_at,
  }
}

export function toAuditLogList(dto: AuditLogListDto): AuditLogList {
  return {
    items: dto.items.map(toAuditLog),
    total: dto.total,
    limit: dto.limit,
    offset: dto.offset,
  }
}
