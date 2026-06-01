import { LoginForm } from '@/features/login'
import { useTranslation } from '@/shared/i18n'
import { Icon, Logo } from '@/shared/ui'

/** Split-screen sign-in: navy brand panel + form card (collapses to one column on mobile). */
export function LoginPage() {
  const { t } = useTranslation()

  return (
    <div className="auth">
      <div className="auth__brandside">
        <div className="brand" style={{ border: 0, padding: 0, gap: 12 }}>
          <div className="brand__mark" style={{ width: 40, height: 40, flex: '0 0 40px' }}>
            <Logo variant="light" size={40} />
          </div>
          <div>
            <div className="brand__name" style={{ fontSize: 17 }}>
              {t('admin.shell.brandName')}
            </div>
            <div className="brand__sub">{t('admin.shell.brandTagline')}</div>
          </div>
        </div>
        <div className="auth__big">{t('admin.auth.brandHeadline')}</div>
        <p className="auth__lead">{t('admin.auth.brandLead')}</p>
        <div className="auth__points">
          <div className="auth__point">
            <span className="pi">
              <Icon name="shield" />
            </span>
            {t('admin.auth.point.audit')}
          </div>
          <div className="auth__point">
            <span className="pi">
              <Icon name="check" />
            </span>
            {t('admin.auth.point.validate')}
          </div>
          <div className="auth__point">
            <span className="pi">
              <Icon name="sliders" />
            </span>
            {t('admin.auth.point.presets')}
          </div>
        </div>
      </div>

      <div className="auth__formside">
        <div className="auth__card">
          <div style={{ marginBottom: 26 }}>
            <h1 className="page-title" style={{ fontSize: 21 }}>
              {t('admin.auth.title')}
            </h1>
            <p className="page-sub">{t('admin.auth.subtitle')}</p>
          </div>
          <LoginForm />
        </div>
      </div>
    </div>
  )
}
