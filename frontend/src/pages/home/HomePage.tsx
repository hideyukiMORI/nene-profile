import { useImportJobs, type ImportJob, type ImportJobStatus } from '@/entities/import-job'
import { JobStatusBadge } from '@/features/import-jobs'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { AsyncBoundary, DataTable, Icon, PageHeader, type Column } from '@/shared/ui'

const RECENT_LIMIT = 5

const statusLabelKey: Record<ImportJobStatus, MessageKey> = {
  pending: 'admin.importJobs.status.pending',
  running: 'admin.importJobs.status.running',
  completed: 'admin.importJobs.status.completed',
  completed_with_errors: 'admin.importJobs.status.completedWithErrors',
  failed: 'admin.importJobs.status.failed',
}

function totals(jobs: readonly ImportJob[]): { rows: number; errors: number } {
  return jobs.reduce(
    (acc, job) => ({ rows: acc.rows + job.rowCount, errors: acc.errors + job.errorCount }),
    { rows: 0, errors: 0 },
  )
}

function errorRatePercent(jobs: readonly ImportJob[]): string {
  const { rows, errors } = totals(jobs)
  if (rows === 0) return '0.0'
  return ((errors / rows) * 100).toFixed(1)
}

/** Dashboard: aggregate stats + recent import jobs. */
export function HomePage() {
  const { t } = useTranslation()
  const query = useImportJobs({ limit: RECENT_LIMIT, offset: 0 })
  const jobs = query.data?.items ?? []
  const total = query.data?.total ?? 0
  const recentRows = totals(jobs).rows

  const columns: readonly Column<ImportJob>[] = [
    {
      id: 'filename',
      header: t('admin.importJobs.col.filename'),
      render: (j) => (
        <span className="row">
          <span className="primary-cell mono">{j.originalFilename}</span>
        </span>
      ),
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
  ]

  return (
    <>
      <PageHeader title={t('admin.dashboard.title')} sub={t('admin.dashboard.subtitle')} />

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
          <div className="empty-block">{t('admin.dashboard.empty')}</div>
        ) : (
          <>
            <div className="stat-grid">
              <div className="stat">
                <div className="stat__top">
                  <div className="stat__label">{t('admin.dashboard.stat.jobs')}</div>
                  <div className="stat__ic ic-tint-blue">
                    <Icon name="jobs" />
                  </div>
                </div>
                <div className="stat__val tnum">{total}</div>
                <div className="stat__delta flat">{t('admin.dashboard.stat.jobsSub')}</div>
              </div>
              <div className="stat">
                <div className="stat__top">
                  <div className="stat__label">{t('admin.dashboard.errorRate')}</div>
                  <div className="stat__ic ic-tint-amber">
                    <Icon name="percent" />
                  </div>
                </div>
                <div className="stat__val tnum">
                  {errorRatePercent(jobs)}
                  <small>%</small>
                </div>
                <div className="stat__delta flat">{t('admin.dashboard.stat.recentSub')}</div>
              </div>
              <div className="stat">
                <div className="stat__top">
                  <div className="stat__label">{t('admin.dashboard.stat.rows')}</div>
                  <div className="stat__ic ic-tint-green">
                    <Icon name="check" />
                  </div>
                </div>
                <div className="stat__val tnum">{recentRows}</div>
                <div className="stat__delta flat">{t('admin.dashboard.stat.recentSub')}</div>
              </div>
            </div>

            <div className="sec-head">
              <div className="sec-title">{t('admin.dashboard.recentJobs')}</div>
            </div>
            <DataTable
              columns={columns}
              rows={jobs}
              rowKey={(j) => j.id}
              emptyLabel={t('admin.dashboard.empty')}
            />
          </>
        )}
      </AsyncBoundary>
    </>
  )
}
