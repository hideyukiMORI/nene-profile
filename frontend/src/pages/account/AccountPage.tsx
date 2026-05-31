import { ChangePasswordForm } from '@/features/account'
import { useTranslation } from '@/shared/i18n'
import { Stack, Text } from '@/shared/ui'

/** Account page: authenticated user's own settings (change password). */
export function AccountPage() {
  const { t } = useTranslation()

  return (
    <Stack gap="lg">
      <Text as="h1" variant="display">
        {t('admin.account.title')}
      </Text>

      <div className="max-w-md rounded-md border border-border bg-surface p-inline-lg">
        <Stack gap="md">
          <Text as="h2" variant="heading">
            {t('admin.account.changePassword.title')}
          </Text>
          <ChangePasswordForm />
        </Stack>
      </div>
    </Stack>
  )
}
