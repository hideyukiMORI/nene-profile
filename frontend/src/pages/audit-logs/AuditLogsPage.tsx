import { useState } from 'react'
import { useAuditLogs, type AuditLog } from '@/entities/audit-log'
import { AuditLogChanges } from '@/features/audit-logs'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, DataTable, PageHeader, Pagination, type Column } from '@/shared/ui'

const PAGE_SIZE = 20

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
      render: (l) => <span className="muted">{l.createdAt}</span>,
    },
    {
      id: 'actor',
      header: t('admin.auditLogs.col.actor'),
      render: (l) =>
        l.actorUserId !== null ? `#${String(l.actorUserId)}` : t('admin.auditLogs.actor.system'),
    },
    {
      id: 'action',
      header: t('admin.auditLogs.col.action'),
      render: (l) => <span className="tag">{l.action}</span>,
    },
    {
      id: 'entity',
      header: t('admin.auditLogs.col.entity'),
      render: (l) =>
        l.entityId !== null ? `${l.entityType} #${String(l.entityId)}` : l.entityType,
    },
    {
      id: 'changes',
      header: t('admin.auditLogs.col.changes'),
      render: (l) => <AuditLogChanges before={l.before} after={l.after} />,
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <>
      <PageHeader title={t('admin.auditLogs.title')} />
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
