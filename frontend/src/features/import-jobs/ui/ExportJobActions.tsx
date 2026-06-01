import { exportImportJob } from '@/entities/import-job'
import { useTranslation } from '@/shared/i18n'
import { Button } from '@/shared/ui'

interface ExportJobActionsProps {
  jobId: number
}

/** JSON / CSV export buttons. Downloads run through the authed api client. */
export function ExportJobActions({ jobId }: ExportJobActionsProps) {
  const { t } = useTranslation()

  return (
    <>
      <Button
        variant="link"
        onClick={() => {
          void exportImportJob(jobId, 'json').catch(() => undefined)
        }}
      >
        {t('admin.importJobs.export.json')}
      </Button>
      <Button
        variant="link"
        onClick={() => {
          void exportImportJob(jobId, 'csv').catch(() => undefined)
        }}
      >
        {t('admin.importJobs.export.csv')}
      </Button>
    </>
  )
}
