import { type ReactElement, useEffect, useState } from 'react'
import { LoginForm } from '@/features/login'
import { LocaleSwitcher, type MessageKey, useTranslation } from '@/shared/i18n'
import { Icon, type IconName, Logo } from '@/shared/ui'

/* Trust-strip glyphs that have no shared-Icon equivalent (login-only). */
const serverGlyph: ReactElement = (
  <>
    <rect x="3" y="4" width="18" height="7" rx="1.6" />
    <rect x="3" y="13" width="18" height="7" rx="1.6" />
    <path d="M7 7.5h.01M7 16.5h.01" />
  </>
)
const codeGlyph: ReactElement = <path d="M9 8l-4 4 4 4M15 8l4 4-4 4" />

function Glyph({ children }: { children: ReactElement }): ReactElement {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.8"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      {children}
    </svg>
  )
}

interface Point {
  icon: IconName
  title: MessageKey
  desc: MessageKey
}
const POINTS: readonly Point[] = [
  { icon: 'check', title: 'admin.auth.point.validate', desc: 'admin.auth.point.validateDesc' },
  { icon: 'shield', title: 'admin.auth.point.audit', desc: 'admin.auth.point.auditDesc' },
  { icon: 'sliders', title: 'admin.auth.point.presets', desc: 'admin.auth.point.presetsDesc' },
]

interface Trust {
  glyph: ReactElement
  label: MessageKey
}
const TRUST: readonly Trust[] = [
  { glyph: <Icon name="shield" />, label: 'admin.auth.trust.audited' },
  { glyph: <Glyph>{serverGlyph}</Glyph>, label: 'admin.auth.trust.selfHosted' },
  { glyph: <Glyph>{codeGlyph}</Glyph>, label: 'admin.auth.trust.oss' },
]

/** Split-screen sign-in: navy brand panel + form card. On phones it becomes a
 * mobile-native layout with a fixed thumb action bar and a detail bottom-sheet. */
export function LoginPage() {
  const { t } = useTranslation()
  const [detailOpen, setDetailOpen] = useState(false)

  const closeDetail = (): void => {
    setDetailOpen(false)
  }

  useEffect(() => {
    document.body.classList.toggle('auth-sheet-open', detailOpen)
    return () => {
      document.body.classList.remove('auth-sheet-open')
    }
  }, [detailOpen])

  useEffect(() => {
    if (!detailOpen) return
    const onKey = (event: KeyboardEvent): void => {
      if (event.key === 'Escape') setDetailOpen(false)
    }
    document.addEventListener('keydown', onKey)
    return () => {
      document.removeEventListener('keydown', onKey)
    }
  }, [detailOpen])

  return (
    <>
      <div className="auth">
        <div className="auth__brandside">
          <div className="brand-lockup">
            <div className="bm">
              <Logo variant="light" size={24} />
            </div>
            <div>
              <div className="bn">{t('admin.shell.brandName')}</div>
              <div className="bs">{t('admin.shell.brandTagline')}</div>
            </div>
          </div>

          <p className="m-tag">{t('admin.auth.brandHeadline')}</p>

          <div className="bhero">
            <h1>{t('admin.auth.brandHeadline')}</h1>
            <p>{t('admin.auth.brandLead')}</p>
          </div>

          <div className="bpoints">
            {POINTS.map((point) => (
              <div className="bpoint" key={point.title}>
                <span className="pi">
                  <Icon name={point.icon} />
                </span>
                <div className="pt">
                  <b>{t(point.title)}</b>
                  <span>{t(point.desc)}</span>
                </div>
              </div>
            ))}
          </div>

          <div className="bfoot">
            {TRUST.map((item, index) => (
              <span className="trust" key={item.label}>
                {index > 0 ? <span className="sepdot" aria-hidden="true" /> : null}
                {item.glyph}
                <span>{t(item.label)}</span>
              </span>
            ))}
          </div>
        </div>

        <div className="auth__formside">
          <div className="auth__formtop">
            <LocaleSwitcher />
          </div>
          <div className="auth__card">
            <div className="card-head">
              <h1 className="page-title">{t('admin.auth.title')}</h1>
              <p className="page-sub">{t('admin.auth.subtitle')}</p>
            </div>
            <LoginForm
              onOpenDetails={() => {
                setDetailOpen(true)
              }}
            />
          </div>
        </div>
      </div>

      {/* Mobile detail bottom-sheet — hidden by CSS until ≤560px. */}
      <div className="auth-sheet-wrap">
        <div
          className={`auth-sheet-backdrop${detailOpen ? ' open' : ''}`}
          aria-hidden="true"
          onClick={closeDetail}
        />
        <div
          className={`auth-sheet${detailOpen ? ' open' : ''}`}
          role="dialog"
          aria-modal="true"
          aria-label={t('admin.auth.details')}
        >
          <div className="auth-sheet__handle" />
          <div className="auth-sheet__head">
            <div className="auth-sheet__brand">
              <div className="bm">
                <Logo variant="navy" size={22} />
              </div>
              <div>
                <div className="bn">{t('admin.shell.brandName')}</div>
                <div className="bs">{t('admin.shell.brandTagline')}</div>
              </div>
            </div>
            <button
              type="button"
              className="auth-sheet__close"
              aria-label={t('common.actions.close')}
              onClick={closeDetail}
            >
              <Icon name="close" />
            </button>
          </div>

          <p className="auth-sheet__lead">{t('admin.auth.brandLead')}</p>

          <div className="s-sec">
            <div className="s-h">{t('admin.auth.sheet.features')}</div>
            {POINTS.map((point) => (
              <div className="s-point" key={point.title}>
                <span className="ic">
                  <Icon name={point.icon} />
                </span>
                <div className="tx">
                  <b>{t(point.title)}</b>
                  <span>{t(point.desc)}</span>
                </div>
              </div>
            ))}
          </div>

          <div className="s-sec">
            <div className="s-h">{t('admin.auth.sheet.about')}</div>
            {TRUST.map((item) => (
              <div className="s-trust" key={item.label}>
                {item.glyph}
                <span>{t(item.label)}</span>
              </div>
            ))}
          </div>

          <div className="s-sec s-lang">
            <div className="s-h">{t('common.locale.label')}</div>
            <LocaleSwitcher />
          </div>
        </div>
      </div>
    </>
  )
}
