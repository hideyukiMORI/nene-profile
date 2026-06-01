import { Link } from 'react-router-dom'
import { useTranslation } from '@/shared/i18n'
import { Icon } from '@/shared/ui'

export function ForbiddenPage() {
  const { t } = useTranslation()

  return (
    <div className="errpage">
      <div className="errcard">
        <div className="errico ic-tint-red">
          <Icon name="lock" />
        </div>
        <div className="errcode">403</div>
        <p>{t('common.error.forbidden')}</p>
        <div className="err-actions">
          <Link className="btn btn--primary" to="/">
            <Icon name="back" />
            {t('common.actions.back')}
          </Link>
        </div>
      </div>
    </div>
  )
}
