import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Stack, Text } from '@/shared/ui'
import { useChangePasswordForm } from '../hooks/use-change-password-form'

/** Form for changing the authenticated user's own password. */
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
      <Stack gap="lg">
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
          <Text variant="caption" tone="danger">
            {t('admin.account.changePassword.errorGeneric')}
          </Text>
        ) : null}

        {isSuccess ? (
          <span role="status" className="text-caption text-text-muted">
            {t('admin.account.changePassword.success')}
          </span>
        ) : null}

        <div className="flex justify-end">
          <Button
            type="submit"
            size="sm"
            disabled={isSubmitting}
            data-testid="change-password-submit"
          >
            {isSubmitting ? t('common.state.submitting') : t('admin.account.changePassword.submit')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
