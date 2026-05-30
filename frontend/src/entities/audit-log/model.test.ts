import { describe, expect, it } from 'vitest'
import type { AuditLogListDto } from './api-types'
import { toAuditLogList } from './model'

describe('audit-log mapper', () => {
  it('maps entries including before/after snapshots and nullable actor', () => {
    const list: AuditLogListDto = {
      items: [
        {
          id: 1,
          actor_user_id: 5,
          organization_id: 7,
          action: 'organization.updated',
          entity_type: 'organization',
          entity_id: 9,
          before: { name: 'Old' },
          after: { name: 'New' },
          created_at: '2026-05-30T00:00:00Z',
        },
        {
          id: 2,
          actor_user_id: null,
          organization_id: null,
          action: 'system.event',
          entity_type: 'system',
          entity_id: null,
          before: null,
          after: null,
          created_at: '2026-05-31T00:00:00Z',
        },
      ],
      total: 2,
      limit: 20,
      offset: 0,
    }

    const result = toAuditLogList(list)

    expect(result.total).toBe(2)
    expect(result.items[0]).toEqual({
      id: 1,
      actorUserId: 5,
      organizationId: 7,
      action: 'organization.updated',
      entityType: 'organization',
      entityId: 9,
      before: { name: 'Old' },
      after: { name: 'New' },
      createdAt: '2026-05-30T00:00:00Z',
    })
    expect(result.items[1]?.actorUserId).toBeNull()
    expect(result.items[1]?.before).toBeNull()
  })
})
