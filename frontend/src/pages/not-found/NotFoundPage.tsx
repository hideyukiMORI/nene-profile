import { Link } from 'react-router-dom'
import { useTranslation } from '@/shared/i18n'
import { Stack, Text } from '@/shared/ui'

export function NotFoundPage() {
  const { t } = useTranslation()

  return (
    <main className="flex min-h-full items-center justify-center p-inline-lg">
      <Stack gap="md">
        <Text as="h1" variant="display">
          404
        </Text>
        <Text variant="body" tone="muted">
          {t('common.error.notFound')}
        </Text>
        <Link to="/">
          <Text as="span" variant="body" tone="muted">
            {t('common.actions.back')}
          </Text>
        </Link>
      </Stack>
    </main>
  )
}
