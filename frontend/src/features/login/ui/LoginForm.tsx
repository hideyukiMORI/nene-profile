import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input } from '@/shared/ui'
import { useLoginForm } from '../hooks/use-login-form'

/**
 * Login form. All copy comes from the i18n catalog; logic lives in the hook.
 * Rendered inside the split-screen auth layout (LoginPage).
 */
export function LoginForm() {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useLoginForm()
  const { register, handleSubmit, formState } = form

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <div className="form-grid">
        <Field
          label={t('admin.auth.email')}
          {...(formState.errors.email ? { error: t('admin.auth.emailRequired') } : {})}
        >
          {({ id, invalid }) => (
            <Input
              id={id}
              type="email"
              autoComplete="email"
              invalid={invalid}
              {...register('email')}
            />
          )}
        </Field>

        <Field
          label={t('admin.auth.password')}
          {...(formState.errors.password ? { error: t('admin.auth.passwordRequired') } : {})}
        >
          {({ id, invalid }) => (
            <Input
              id={id}
              type="password"
              autoComplete="current-password"
              invalid={invalid}
              {...register('password')}
            />
          )}
        </Field>

        {error !== null ? (
          <span className="field__error" role="alert">
            {error.status === 401 ? t('admin.auth.failed') : t('common.error.generic')}
          </span>
        ) : null}

        <Button
          type="submit"
          variant="primary"
          size="lg"
          block
          disabled={isSubmitting}
          data-testid="login-submit"
        >
          {isSubmitting ? t('common.state.submitting') : t('admin.auth.submit')}
        </Button>
      </div>
    </form>
  )
}
