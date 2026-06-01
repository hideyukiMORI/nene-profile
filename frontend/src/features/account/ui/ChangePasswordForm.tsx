import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input } from '@/shared/ui'
import { useChangePasswordForm } from '../hooks/use-change-password-form'

/** Form for changing the authenticated user's own password (design-system card). */
export function ChangePasswordForm() {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, isSuccess, isInvalidCurrentPassword, isGenericError } =
    useChangePasswordForm()
  const { register, handleSubmit, formState } = form

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <div className="card">
        <div className="card__head">
          <div>
            <h2 className="card__title">{t('admin.account.changePassword.title')}</h2>
            <div className="card__desc">{t('admin.account.changePassword.desc')}</div>
          </div>
        </div>
        <div className="card__body">
          <div className="form-grid">
            <Field
              label={t('admin.account.changePassword.currentPassword')}
              {...(formState.errors.currentPassword
                ? { error: t('admin.account.changePassword.currentPasswordRequired') }
                : {})}
              {...(isInvalidCurrentPassword
                ? { error: t('admin.account.changePassword.errorCurrentPassword') }
                : {})}
            >
              {({ id, invalid }) => (
                <Input
                  id={id}
                  type="password"
                  autoComplete="current-password"
                  invalid={invalid || isInvalidCurrentPassword}
                  data-testid="current-password"
                  {...register('currentPassword')}
                />
              )}
            </Field>

            <hr className="divider" />

            <Field
              label={t('admin.account.changePassword.newPassword')}
              {...(formState.errors.newPassword
                ? { error: t('admin.account.changePassword.newPasswordMin') }
                : {})}
            >
              {({ id, invalid }) => (
                <Input
                  id={id}
                  type="password"
                  autoComplete="new-password"
                  invalid={invalid}
                  data-testid="new-password"
                  {...register('newPassword')}
                />
              )}
            </Field>

            <Field
              label={t('admin.account.changePassword.confirmPassword')}
              {...(formState.errors.confirmPassword
                ? { error: t('admin.account.changePassword.confirmPasswordMismatch') }
                : {})}
            >
              {({ id, invalid }) => (
                <Input
                  id={id}
                  type="password"
                  autoComplete="new-password"
                  invalid={invalid}
                  data-testid="confirm-password"
                  {...register('confirmPassword')}
                />
              )}
            </Field>

            {isGenericError ? (
              <span className="field__error" role="alert">
                {t('admin.account.changePassword.errorGeneric')}
              </span>
            ) : null}
            {isSuccess ? (
              <span role="status" className="form-status">
                {t('admin.account.changePassword.success')}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          <Button type="submit" disabled={isSubmitting} data-testid="change-password-submit">
            {isSubmitting ? t('common.state.submitting') : t('admin.account.changePassword.submit')}
          </Button>
        </div>
      </div>
    </form>
  )
}
