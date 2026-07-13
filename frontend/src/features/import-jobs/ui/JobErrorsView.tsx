import { useImportJobErrors, type ImportJobError } from '@/entities/import-job'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, DataTable, Stack, Text, type Column } from '@/shared/ui'

interface JobErrorsViewProps {
  jobId: number
}

/** Read-only list of a job's rejected rows (raw row number, message, snippet). */
export function JobErrorsView({ jobId }: JobErrorsViewProps) {
  const { t } = useTranslation()
  const query = useImportJobErrors(jobId)
  const rows = query.data ?? []

  const columns: readonly Column<ImportJobError>[] = [
    {
      id: 'rowNumber',
      header: t('admin.importJobs.errors.col.rowNumber'),
      render: (e) => e.rawRowNumber,
    },
    { id: 'message', header: t('admin.importJobs.errors.col.message'), render: (e) => e.message },
    {
      id: 'snippet',
      header: t('admin.importJobs.errors.col.snippet'),
      render: (e) => e.rawSnippet ?? '—',
    },
  ]

  return (
    <Stack gap="sm">
      <Text as="h2" variant="heading">
        {t('admin.importJobs.errors.title')}
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
        <DataTable
          columns={columns}
          rows={rows}
          rowKey={(e) => e.id}
          emptyLabel={t('common.state.empty')}
        />
      </AsyncBoundary>
    </Stack>
  )
}
