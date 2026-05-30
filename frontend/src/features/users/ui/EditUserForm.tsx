import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Select, Stack, Text, type SelectOption } from '@/shared/ui'
import type { User } from '@/entities/user'
import { useEditUserForm } from '../hooks/use-edit-user-form'

interface EditUserFormProps {
  user: User
  onSaved: () => void
  onCancel: () => void
}

/** Edit-user form: role + status + optional password (blank keeps current). */
export function EditUserForm({ user, onSaved, onCancel }: EditUserFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useEditUserForm(user, onSaved)
  const { register, handleSubmit, formState } = form

  const roleOptions: readonly SelectOption[] = [
    { value: 'superadmin', label: t('admin.users.role.superadmin') },
    { value: 'admin', label: t('admin.users.role.admin') },
    { value: 'member', label: t('admin.users.role.member') },
    { value: 'viewer', label: t('admin.users.role.viewer') },
  ]
  const statusOptions: readonly SelectOption[] = [
    { value: 'active', label: t('admin.users.status.active') },
    { value: 'invited', label: t('admin.users.status.invited') },
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
          {t('admin.users.edit.title')}
        </Text>
        <Text variant="caption" tone="muted">
          {user.email}
        </Text>

        <Field label={t('admin.users.edit.role')}>
          {({ id }) => <Select id={id} options={roleOptions} {...register('role')} />}
        </Field>

        <Field label={t('admin.users.edit.status')}>
          {({ id }) => <Select id={id} options={statusOptions} {...register('status')} />}
        </Field>

        <Field
          label={t('admin.users.edit.password')}
          {...(formState.errors.password ? { error: t('admin.users.edit.passwordInvalid') } : {})}
        >
          {({ id, invalid }) => (
            <Input
              id={id}
              type="password"
              autoComplete="new-password"
              placeholder={t('admin.users.edit.passwordHint')}
              invalid={invalid}
              {...register('password')}
            />
          )}
        </Field>

        {error !== null ? (
          <Text variant="caption" tone="danger">
            {t('admin.users.edit.error')}
          </Text>
        ) : null}

        <div className="flex justify-end gap-inline-sm">
          <Button variant="ghost" size="sm" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" size="sm" disabled={isSubmitting} data-testid="user-edit-submit">
            {isSubmitting ? t('common.state.saving') : t('admin.users.edit.submit')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
