import { useTranslation } from '@/shared/i18n'
import { Field, FormCard, Input, Select, type SelectOption } from '@/shared/ui'
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
    <FormCard
      title={t('admin.users.edit.title')}
      description={user.email}
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      isSubmitting={isSubmitting}
      submitLabel={t('admin.users.edit.submit')}
      submittingLabel={t('common.state.saving')}
      submitTestId="user-edit-submit"
      cancel={{ label: t('common.actions.cancel'), onClick: onCancel }}
      {...(error !== null ? { error: t('admin.users.edit.error') } : {})}
    >
      <div className="field-row">
        <Field label={t('admin.users.edit.role')}>
          {({ id }) => <Select id={id} options={roleOptions} {...register('role')} />}
        </Field>
        <Field label={t('admin.users.edit.status')}>
          {({ id }) => <Select id={id} options={statusOptions} {...register('status')} />}
        </Field>
      </div>

      <Field
        label={t('admin.users.edit.password')}
        hint={t('admin.users.edit.passwordHint')}
        {...(formState.errors.password ? { error: t('admin.users.edit.passwordInvalid') } : {})}
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
    </FormCard>
  )
}
