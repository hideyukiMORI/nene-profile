import { useOrganizationSettings } from '@/entities/organization-settings'
import { SettingsForm } from '@/features/organization-settings'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, Stack, Text } from '@/shared/ui'

/** Organization settings screen (singleton): load then edit. */
export function SettingsPage() {
  const { t } = useTranslation()
  const query = useOrganizationSettings()

  return (
    <Stack gap="lg">
      <Text as="h1" variant="display">
        {t('admin.settings.title')}
      </Text>
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
          <div className="max-w-md rounded-md border border-border bg-surface p-inline-lg">
            <SettingsForm settings={query.data} />
          </div>
        ) : null}
      </AsyncBoundary>
    </Stack>
  )
}
