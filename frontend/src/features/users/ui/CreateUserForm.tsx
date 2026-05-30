import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Select, Stack, Text, type SelectOption } from '@/shared/ui'
import { useCreateUserForm } from '../hooks/use-create-user-form'

interface CreateUserFormProps {
  onCreated: () => void
  onCancel: () => void
}

/** Create-user form. Roles offered exclude superadmin (cross-tenant). */
export function CreateUserForm({ onCreated, onCancel }: CreateUserFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useCreateUserForm(onCreated)
  const { register, handleSubmit, formState } = form

  const roleOptions: readonly SelectOption[] = [
    { value: 'admin', label: t('admin.users.role.admin') },
    { value: 'member', label: t('admin.users.role.member') },
    { value: 'viewer', label: t('admin.users.role.viewer') },
  ]

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <Stack gap="lg">
        <Text as="h2" variant="heading">
          {t('admin.users.create.title')}
        </Text>

        <Field
          label={t('admin.users.create.email')}
          {...(formState.errors.email ? { error: t('admin.users.create.emailRequired') } : {})}
        >
          {({ id, invalid }) => (
            <Input
              id={id}
              type="email"
              autoComplete="off"
              invalid={invalid}
              {...register('email')}
            />
          )}
        </Field>

        <Field
          label={t('admin.users.create.password')}
          {...(formState.errors.password
            ? { error: t('admin.users.create.passwordRequired') }
            : {})}
        >
          {({ id, invalid }) => (
            <Input
              id={id}
              type="password"
              autoComplete="new-password"
              invalid={invalid}
              {...register('password')}
            />
          )}
        </Field>

        <Field label={t('admin.users.create.role')}>
          {({ id }) => <Select id={id} options={roleOptions} {...register('role')} />}
        </Field>

        {error !== null ? (
          <Text variant="caption" tone="danger">
            {t('admin.users.create.error')}
          </Text>
        ) : null}

        <div className="flex justify-end gap-inline-sm">
          <Button variant="ghost" size="sm" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" size="sm" disabled={isSubmitting} data-testid="user-create-submit">
            {isSubmitting ? t('common.state.submitting') : t('admin.users.create.submit')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
