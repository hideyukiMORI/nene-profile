import { useOrganizationSettings } from '@/entities/organization-settings'
import { SettingsForm } from '@/features/organization-settings'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, PageHeader } from '@/shared/ui'

/** Organization settings screen (singleton): load then edit. */
export function SettingsPage() {
  const { t } = useTranslation()
  const query = useOrganizationSettings()

  return (
    <>
      <PageHeader title={t('admin.settings.title')} sub={t('admin.settings.subtitle')} />
      <AsyncBoundary
        isLoading={query.isPending}
        isError={query.isError}
        loadingLabel={t('common.state.loading')}
        errorLabel={t('admin.settings.error')}
        retryLabel={t('common.actions.retry')}
        onRetry={() => {
          void query.refetch()
        }}
      >
        {query.data !== undefined ? (
          <div style={{ maxWidth: 680 }}>
            <SettingsForm settings={query.data} />
          </div>
        ) : null}
      </AsyncBoundary>
    </>
  )
}
