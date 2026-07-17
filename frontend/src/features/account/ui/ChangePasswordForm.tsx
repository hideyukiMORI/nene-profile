import { useTranslation } from '@/shared/i18n'
import { Field, FormCard, Input } from '@/shared/ui'
import { useChangePasswordForm } from '../model/use-change-password-form'

/** Form for changing the authenticated user's own password (design-system card). */
export function ChangePasswordForm() {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, isSuccess, isInvalidCurrentPassword, isGenericError } =
    useChangePasswordForm()
  const { register, handleSubmit, formState } = form

  return (
    <FormCard
      title={t('admin.account.changePassword.title')}
      description={t('admin.account.changePassword.desc')}
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      isSubmitting={isSubmitting}
      submitLabel={t('admin.account.changePassword.submit')}
      submittingLabel={t('common.state.submitting')}
      submitTestId="change-password-submit"
      {...(isGenericError ? { error: t('admin.account.changePassword.errorGeneric') } : {})}
      {...(isSuccess ? { status: t('admin.account.changePassword.success') } : {})}
    >
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
    </FormCard>
  )
}
