import { authStore } from '@/entities/auth'
import { ChangePasswordForm } from '@/features/account'
import { useTranslation } from '@/shared/i18n'
import { PageHeader } from '@/shared/ui'

/** Account page: authenticated user's own settings (change password). */
export function AccountPage() {
  const { t } = useTranslation()
  const email = authStore.getSession()?.email

  return (
    <>
      <PageHeader
        title={t('admin.account.title')}
        {...(email !== undefined ? { sub: t('admin.account.signedInAs', { email }) } : {})}
      />
      <div style={{ maxWidth: 520 }}>
        <ChangePasswordForm />
      </div>
    </>
  )
}
