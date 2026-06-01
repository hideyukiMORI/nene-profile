import { Link } from 'react-router-dom'
import { useTranslation } from '@/shared/i18n'
import { Icon } from '@/shared/ui'

export function NotFoundPage() {
  const { t } = useTranslation()

  return (
    <div className="errpage">
      <div className="errcard">
        <div className="errico ic-tint-blue">
          <Icon name="search" />
        </div>
        <div className="errcode">404</div>
        <p>{t('common.error.notFound')}</p>
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
