import { type KeyboardEvent, type ReactElement, useId, useState } from 'react'
import { useTranslation } from '@/shared/i18n'
import { Icon } from '@/shared/ui'
import { useLoginForm } from '../hooks/use-login-form'

/* Login-specific inline glyphs (24×24, currentColor). Kept local because they
   are only ever used on the sign-in screen — they don't belong in the shared
   Icon set. */
const mailGlyph: ReactElement = (
  <>
    <rect x="3" y="5" width="18" height="14" rx="2" />
    <path d="M4 7l8 5 8-5" />
  </>
)
const eyeGlyph: ReactElement = (
  <>
    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
    <circle cx="12" cy="12" r="3" />
  </>
)
const eyeOffGlyph: ReactElement = (
  <>
    <path d="M3 3l18 18" />
    <path d="M10.6 10.6a3 3 0 0 0 4.2 4.2" />
    <path d="M9.4 5.2A9.4 9.4 0 0 1 12 5c6.5 0 10 7 10 7a16 16 0 0 1-3.1 3.9" />
    <path d="M6.1 7.1A16 16 0 0 0 2 12s3.5 7 10 7a9.3 9.3 0 0 0 3-.5" />
  </>
)
const capsGlyph: ReactElement = (
  <>
    <path d="M12 5l6 6h-3v4H9v-4H6z" />
    <path d="M9 19h6" />
  </>
)
const infoGlyph: ReactElement = (
  <>
    <circle cx="12" cy="12" r="9" />
    <path d="M12 11v5M12 8h.01" />
  </>
)

function Glyph({ children }: { children: ReactElement }): ReactElement {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.9"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      {children}
    </svg>
  )
}

interface LoginFormProps {
  /** Opens the mobile detail bottom-sheet (owned by LoginPage). */
  onOpenDetails?: () => void
}

/**
 * Login form. Copy comes from the i18n catalog; auth logic lives in the hook.
 * Rendered inside the split-screen auth layout (LoginPage). Carries the mobile
 * thumb action bar (`.m-bar`), which the stylesheet reveals at ≤560px and which
 * submits this same form via `form="loginForm"`.
 *
 * The "keep me signed in" toggle is display-only: sessions are in-memory by
 * design (entities/auth/model.ts) and a persistent store needs an ADR.
 */
export function LoginForm({ onOpenDetails }: LoginFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useLoginForm()
  const { register, handleSubmit, formState } = form
  const emailId = useId()
  const passwordId = useId()
  const [showPassword, setShowPassword] = useState(false)
  const [capsOn, setCapsOn] = useState(false)
  const [remember, setRemember] = useState(true)

  const passwordReg = register('password')
  const detectCaps = (event: KeyboardEvent<HTMLInputElement>): void => {
    if (typeof event.getModifierState === 'function') {
      setCapsOn(event.getModifierState('CapsLock'))
    }
  }
  const toggleRemember = (): void => {
    setRemember((on) => !on)
  }

  const submitLabel = isSubmitting ? t('common.state.submitting') : t('admin.auth.submit')

  return (
    <>
      <form
        id="loginForm"
        className="form-grid"
        onSubmit={(event) => {
          void handleSubmit(submit)(event)
        }}
        noValidate
      >
        <div className="field">
          <label className="field__label" htmlFor={emailId}>
            {t('admin.auth.email')}
          </label>
          <div className="ifield">
            <input
              id={emailId}
              className="input"
              type="email"
              autoComplete="email"
              aria-invalid={formState.errors.email ? true : undefined}
              {...register('email')}
            />
            <span className="lead">
              <Glyph>{mailGlyph}</Glyph>
            </span>
          </div>
          {formState.errors.email ? (
            <span className="field__error" role="alert">
              {t('admin.auth.emailRequired')}
            </span>
          ) : null}
        </div>

        <div className="field">
          <div className="field__top">
            <label className="field__label" htmlFor={passwordId}>
              {t('admin.auth.password')}
            </label>
            <span className={`caps${capsOn ? ' show' : ''}`}>
              <Glyph>{capsGlyph}</Glyph>
              <span>{t('admin.auth.capsLock')}</span>
            </span>
          </div>
          <div className="ifield has-toggle">
            <input
              id={passwordId}
              className="input"
              type={showPassword ? 'text' : 'password'}
              autoComplete="current-password"
              aria-invalid={formState.errors.password ? true : undefined}
              {...passwordReg}
              onKeyUp={detectCaps}
              onKeyDown={detectCaps}
              onBlur={(event) => {
                void passwordReg.onBlur(event)
                setCapsOn(false)
              }}
            />
            <span className="lead">
              <Icon name="lock" />
            </span>
            <button
              type="button"
              className="reveal"
              aria-label={t(showPassword ? 'admin.auth.hidePassword' : 'admin.auth.showPassword')}
              onClick={() => {
                setShowPassword((show) => !show)
              }}
            >
              <Glyph>{showPassword ? eyeOffGlyph : eyeGlyph}</Glyph>
            </button>
          </div>
          {formState.errors.password ? (
            <span className="field__error" role="alert">
              {t('admin.auth.passwordRequired')}
            </span>
          ) : null}
        </div>

        <div className="formopts">
          <div
            className={`toggle${remember ? ' is-on' : ''}`}
            role="switch"
            aria-checked={remember}
            tabIndex={0}
            onClick={toggleRemember}
            onKeyDown={(event) => {
              if (event.key === ' ' || event.key === 'Enter') {
                event.preventDefault()
                toggleRemember()
              }
            }}
          >
            <span className="toggle__track" />
            <span className="toggle__label">{t('admin.auth.remember')}</span>
          </div>
        </div>

        {error !== null ? (
          <span className="field__error" role="alert">
            {error.status === 401 ? t('admin.auth.failed') : t('common.error.generic')}
          </span>
        ) : null}

        <button
          type="submit"
          className="btn btn--primary btn--lg btn--block auth__submit"
          disabled={isSubmitting}
          data-testid="login-submit"
        >
          {submitLabel}
        </button>
      </form>

      <div className="secline">
        <Icon name="lock" />
        <span>{t('admin.auth.secured')}</span>
      </div>

      {/* Mobile thumb action bar — hidden by CSS until ≤560px. */}
      <div className="m-bar">
        <button
          type="submit"
          form="loginForm"
          className="btn btn--primary"
          disabled={isSubmitting}
          data-testid="login-submit-mobile"
        >
          {submitLabel}
        </button>
        <div className="m-bar__row">
          <button type="button" className="m-link" aria-haspopup="dialog" onClick={onOpenDetails}>
            <Glyph>{infoGlyph}</Glyph>
            <span>{t('admin.auth.details')}</span>
          </button>
        </div>
      </div>
    </>
  )
}
