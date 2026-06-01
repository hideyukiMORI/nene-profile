import { useState } from 'react'
import { useImportJobs, type ImportJob, type ImportJobStatus } from '@/entities/import-job'
import {
  ExportJobActions,
  JobErrorsView,
  JobStatusBadge,
  UploadJobForm,
} from '@/features/import-jobs'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import {
  AsyncBoundary,
  Button,
  DataTable,
  Icon,
  PageHeader,
  Pagination,
  type Column,
} from '@/shared/ui'

const PAGE_SIZE = 20

const statusLabelKey: Record<ImportJobStatus, MessageKey> = {
  pending: 'admin.importJobs.status.pending',
  running: 'admin.importJobs.status.running',
  completed: 'admin.importJobs.status.completed',
  completed_with_errors: 'admin.importJobs.status.completedWithErrors',
  failed: 'admin.importJobs.status.failed',
}

const EXPORTABLE: ReadonlySet<ImportJobStatus> = new Set<ImportJobStatus>([
  'completed',
  'completed_with_errors',
])

/** Import-jobs admin screen: list + CSV upload + error rows + export. */
export function ImportJobsPage() {
  const { t } = useTranslation()
  const [offset, setOffset] = useState(0)
  const [creating, setCreating] = useState(false)
  const [expandedId, setExpandedId] = useState<number | null>(null)

  const query = useImportJobs({ limit: PAGE_SIZE, offset })
  const total = query.data?.total ?? 0
  const rows = query.data?.items ?? []

  const columns: readonly Column<ImportJob>[] = [
    {
      id: 'filename',
      header: t('admin.importJobs.col.filename'),
      render: (j) => (
        <span className="row">
          <span style={{ color: 'var(--ink-3)', flex: '0 0 auto', display: 'inline-grid' }}>
            <Icon name="file" />
          </span>
          <span className="primary-cell mono">{j.originalFilename}</span>
        </span>
      ),
    },
    {
      id: 'preset',
      header: t('admin.importJobs.col.preset'),
      render: (j) => <span className="tag">{`#${String(j.presetVersionId)}`}</span>,
    },
    {
      id: 'status',
      header: t('admin.importJobs.col.status'),
      render: (j) => <JobStatusBadge status={j.status} label={t(statusLabelKey[j.status])} />,
    },
    {
      id: 'rowCount',
      header: t('admin.importJobs.col.rowCount'),
      align: 'end',
      render: (j) => <span className="tnum">{j.rowCount}</span>,
    },
    {
      id: 'errorCount',
      header: t('admin.importJobs.col.errorCount'),
      align: 'end',
      render: (j) => <span className="tnum">{j.errorCount}</span>,
    },
    {
      id: 'createdAt',
      header: t('admin.importJobs.col.createdAt'),
      render: (j) => <span className="muted">{j.createdAt}</span>,
    },
    {
      id: 'actions',
      header: t('admin.importJobs.col.actions'),
      align: 'end',
      render: (j) => (
        <>
          {j.errorCount > 0 ? (
            <Button
              variant="link"
              onClick={() => {
                setExpandedId((current) => (current === j.id ? null : j.id))
              }}
            >
              {t('admin.importJobs.errors.title')}
            </Button>
          ) : null}
          {EXPORTABLE.has(j.status) ? <ExportJobActions jobId={j.id} /> : null}
        </>
      ),
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <>
      <PageHeader
        title={t('admin.importJobs.title')}
        actions={
          !creating ? (
            <Button
              onClick={() => {
                setCreating(true)
              }}
            >
              <Icon name="upload" />
              {t('admin.importJobs.newButton')}
            </Button>
          ) : undefined
        }
      />

      {creating ? (
        <div className="card" style={{ marginBottom: 20 }}>
          <div className="card__body">
            <UploadJobForm
              onUploaded={() => {
                setCreating(false)
              }}
              onCancel={() => {
                setCreating(false)
              }}
            />
          </div>
        </div>
      ) : null}

      <AsyncBoundary
        isLoading={query.isPending}
        isError={query.isError}
        loadingLabel={t('common.state.loading')}
        errorLabel={t('admin.importJobs.error')}
        retryLabel={t('common.actions.retry')}
        onRetry={() => {
          void query.refetch()
        }}
      >
        <DataTable
          columns={columns}
          rows={rows}
          rowKey={(j) => j.id}
          emptyLabel={t('admin.importJobs.empty')}
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
        {expandedId !== null ? (
          <div className="card" style={{ marginTop: 16 }}>
            <div className="card__body">
              <JobErrorsView jobId={expandedId} />
            </div>
          </div>
        ) : null}
      </AsyncBoundary>
    </>
  )
}
