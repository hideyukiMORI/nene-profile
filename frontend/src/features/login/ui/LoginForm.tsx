import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Stack, Text } from '@/shared/ui'
import { useLoginForm } from '../hooks/use-login-form'

/**
 * Login form. All copy comes from the i18n catalog; logic lives in the hook.
 * Renders the four UI states implicitly (idle / submitting / error / success →
 * navigation handled by the hook).
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
      <Stack gap="lg">
        <Text as="h1" variant="display">
          {t('admin.auth.title')}
        </Text>

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
          <Text variant="caption" tone="danger">
            {error.status === 401 ? t('admin.auth.failed') : t('common.error.generic')}
          </Text>
        ) : null}

        <Button type="submit" disabled={isSubmitting} data-testid="login-submit">
          {isSubmitting ? t('common.state.submitting') : t('admin.auth.submit')}
        </Button>
      </Stack>
    </form>
  )
}
