import { useState } from 'react'
import { useAuditLogs, type AuditLog } from '@/entities/audit-log'
import { AuditLogDiff } from '@/features/audit-logs'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, DataTable, Icon, PageHeader, Pagination, type Column } from '@/shared/ui'

const PAGE_SIZE = 20

type BadgeTone = 'danger' | 'warn' | 'ok' | 'info' | 'neutral'

/** Tone the action badge by its verb, keeping the raw action label. Order matters
 * (e.g. "...completed_with_errors" → warn, "...completed" → info). */
function actionTone(action: string): BadgeTone {
  if (/delete|destroy|purge|remove|revoke/i.test(action)) return 'danger'
  if (/error|fail|reject/i.test(action)) return 'warn'
  if (/create|add|register/i.test(action)) return 'ok'
  if (/update|edit|change|patch|complete|import/i.test(action)) return 'info'
  return 'neutral'
}

/** Audit log screen: read-only paginated trail with before/after snapshots. */
export function AuditLogsPage() {
  const { t } = useTranslation()
  const [offset, setOffset] = useState(0)

  const query = useAuditLogs({ limit: PAGE_SIZE, offset })
  const total = query.data?.total ?? 0
  const rows = query.data?.items ?? []

  const columns: readonly Column<AuditLog>[] = [
    {
      id: 'createdAt',
      header: t('admin.auditLogs.col.createdAt'),
      render: (l) => (
        <span className="mono muted" style={{ fontSize: 12.5 }}>
          {l.createdAt}
        </span>
      ),
    },
    {
      id: 'actor',
      header: t('admin.auditLogs.col.actor'),
      render: (l) => (
        <span className="row">
          <span className="avatar avatar--sm" aria-hidden="true">
            <Icon name={l.actorUserId !== null ? 'user' : 'settings'} />
          </span>
          <span style={{ fontSize: 13 }}>
            {l.actorUserId !== null
              ? `#${String(l.actorUserId)}`
              : t('admin.auditLogs.actor.system')}
          </span>
        </span>
      ),
    },
    {
      id: 'action',
      header: t('admin.auditLogs.col.action'),
      render: (l) => (
        <span className={`badge badge--${actionTone(l.action)}`}>
          <span className="dot" />
          {l.action}
        </span>
      ),
    },
    {
      id: 'entity',
      header: t('admin.auditLogs.col.entity'),
      render: (l) => (
        <span className="tag">
          {l.entityId !== null ? `${l.entityType} #${String(l.entityId)}` : l.entityType}
        </span>
      ),
    },
    {
      id: 'actions',
      header: t('admin.auditLogs.col.changes'),
      align: 'end',
      render: (l) => <AuditLogDiff log={l} />,
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <>
      <PageHeader title={t('admin.auditLogs.title')} sub={t('admin.auditLogs.subtitle')} />
      <AsyncBoundary
        isLoading={query.isPending}
        isError={query.isError}
        loadingLabel={t('common.state.loading')}
        errorLabel={t('admin.auditLogs.error')}
        retryLabel={t('common.actions.retry')}
        onRetry={() => {
          void query.refetch()
        }}
      >
        <DataTable
          columns={columns}
          rows={rows}
          rowKey={(l) => l.id}
          emptyLabel={t('admin.auditLogs.empty')}
          footer={
            total > 0 ? (
              <Pagination
                summary={t('common.pagination.summary', { from, to, total })}
                prevLabel={t('common.pagination.prev')}
                nextLabel={t('common.pagination.next')}
                canPrev={offset > 0}
                canNext={offset + PAGE_SIZE < total}
                onPrev={() => {
                  setOffset((current) => Math.max(0, current - PAGE_SIZE))
                }}
                onNext={() => {
                  setOffset((current) => current + PAGE_SIZE)
                }}
              />
            ) : undefined
          }
        />
      </AsyncBoundary>
    </>
  )
}
