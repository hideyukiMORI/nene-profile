import { ChangePasswordForm } from '@/features/account'
import { useTranslation } from '@/shared/i18n'
import { PageHeader } from '@/shared/ui'

/** Account page: authenticated user's own settings (change password). */
export function AccountPage() {
  const { t } = useTranslation()

  return (
    <>
      <PageHeader title={t('admin.account.title')} />
      <div className="card" style={{ maxWidth: 560 }}>
        <div className="card__head">
          <h2 className="card__title">{t('admin.account.changePassword.title')}</h2>
        </div>
        <div className="card__body">
          <ChangePasswordForm />
        </div>
      </div>
    </>
  )
}
