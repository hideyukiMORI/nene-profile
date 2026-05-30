import { useState } from 'react'
import { useImportJobs, type ImportJob, type ImportJobStatus } from '@/entities/import-job'
import { ExportJobActions, JobErrorsView, UploadJobForm } from '@/features/import-jobs'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { AsyncBoundary, Button, DataTable, Pagination, Stack, Text, type Column } from '@/shared/ui'

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
      render: (j) => j.originalFilename,
    },
    {
      id: 'preset',
      header: t('admin.importJobs.col.preset'),
      render: (j) => `#${String(j.presetVersionId)}`,
    },
    {
      id: 'status',
      header: t('admin.importJobs.col.status'),
      render: (j) => t(statusLabelKey[j.status]),
    },
    {
      id: 'rowCount',
      header: t('admin.importJobs.col.rowCount'),
      align: 'end',
      render: (j) => j.rowCount,
    },
    {
      id: 'errorCount',
      header: t('admin.importJobs.col.errorCount'),
      align: 'end',
      render: (j) => j.errorCount,
    },
    { id: 'createdAt', header: t('admin.importJobs.col.createdAt'), render: (j) => j.createdAt },
    {
      id: 'actions',
      header: t('admin.importJobs.col.actions'),
      align: 'end',
      render: (j) => (
        <span className="inline-flex items-center gap-inline-sm">
          {j.errorCount > 0 ? (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => {
                setExpandedId((current) => (current === j.id ? null : j.id))
              }}
            >
              {t('admin.importJobs.errors.title')}
            </Button>
          ) : null}
          {EXPORTABLE.has(j.status) ? <ExportJobActions jobId={j.id} /> : null}
        </span>
      ),
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <Stack gap="lg">
      <div className="flex items-center justify-between">
        <Text as="h1" variant="display">
          {t('admin.importJobs.title')}
        </Text>
        {!creating ? (
          <Button
            size="sm"
            onClick={() => {
              setCreating(true)
            }}
          >
            {t('admin.importJobs.newButton')}
          </Button>
        ) : null}
      </div>

      {creating ? (
        <div className="rounded-md border border-border bg-surface p-inline-lg">
          <UploadJobForm
            onUploaded={() => {
              setCreating(false)
            }}
            onCancel={() => {
              setCreating(false)
            }}
          />
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
        <Stack gap="md">
          <DataTable
            columns={columns}
            rows={rows}
            rowKey={(j) => j.id}
            emptyLabel={t('admin.importJobs.empty')}
          />
          {expandedId !== null ? (
            <div className="rounded-md border border-border bg-surface p-inline-lg">
              <JobErrorsView jobId={expandedId} />
            </div>
          ) : null}
          {total > 0 ? (
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
          ) : null}
        </Stack>
      </AsyncBoundary>
    </Stack>
  )
}
