import { useImportJobs, type ImportJob, type ImportJobStatus } from '@/entities/import-job'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { AsyncBoundary, DataTable, Stack, Text, type Column } from '@/shared/ui'

const RECENT_LIMIT = 5

const statusLabelKey: Record<ImportJobStatus, MessageKey> = {
  pending: 'admin.importJobs.status.pending',
  running: 'admin.importJobs.status.running',
  completed: 'admin.importJobs.status.completed',
  completed_with_errors: 'admin.importJobs.status.completedWithErrors',
  failed: 'admin.importJobs.status.failed',
}

function errorRatePercent(jobs: readonly ImportJob[]): string {
  const totals = jobs.reduce(
    (acc, job) => ({ rows: acc.rows + job.rowCount, errors: acc.errors + job.errorCount }),
    { rows: 0, errors: 0 },
  )
  if (totals.rows === 0) return '0.0'
  return ((totals.errors / totals.rows) * 100).toFixed(1)
}

/** Dashboard: recent import jobs and an aggregate error rate. */
export function HomePage() {
  const { t } = useTranslation()
  const query = useImportJobs({ limit: RECENT_LIMIT, offset: 0 })
  const jobs = query.data?.items ?? []

  const columns: readonly Column<ImportJob>[] = [
    {
      id: 'filename',
      header: t('admin.importJobs.col.filename'),
      render: (j) => j.originalFilename,
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
  ]

  return (
    <Stack gap="lg">
      <Text as="h1" variant="display">
        {t('admin.dashboard.title')}
      </Text>

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
        {jobs.length === 0 ? (
          <Text variant="body" tone="muted">
            {t('admin.dashboard.empty')}
          </Text>
        ) : (
          <Stack gap="lg">
            <div className="max-w-xs rounded-md border border-border bg-surface p-inline-lg">
              <Stack gap="xs">
                <Text variant="caption" tone="muted">
                  {t('admin.dashboard.errorRate')}
                </Text>
                <Text variant="display">{`${errorRatePercent(jobs)}%`}</Text>
              </Stack>
            </div>

            <Stack gap="md">
              <Text as="h2" variant="heading">
                {t('admin.dashboard.recentJobs')}
              </Text>
              <DataTable
                columns={columns}
                rows={jobs}
                rowKey={(j) => j.id}
                emptyLabel={t('admin.dashboard.empty')}
              />
            </Stack>
          </Stack>
        )}
      </AsyncBoundary>
    </Stack>
  )
}
