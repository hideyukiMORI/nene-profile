import { useTranslation } from '@/shared/i18n'
import { Stack, Text } from '@/shared/ui'

export function HomePage() {
  const { t } = useTranslation()

  return (
    <Stack gap="md">
      <Text as="h1" variant="display">
        {t('admin.dashboard.title')}
      </Text>
      <Text variant="body" tone="muted">
        {t('admin.dashboard.empty')}
      </Text>
    </Stack>
  )
}
