import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Select, type SelectOption } from '@/shared/ui'
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
      <div className="card">
        <div className="card__head">
          <div>
            <h2 className="card__title">{t('admin.users.edit.title')}</h2>
            <div className="card__desc">{user.email}</div>
          </div>
        </div>
        <div className="card__body">
          <div className="form-grid">
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
              {...(formState.errors.password
                ? { error: t('admin.users.edit.passwordInvalid') }
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

            {error !== null ? (
              <span className="field__error" role="alert">
                {t('admin.users.edit.error')}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          <Button variant="ghost" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" disabled={isSubmitting} data-testid="user-edit-submit">
            {isSubmitting ? t('common.state.saving') : t('admin.users.edit.submit')}
          </Button>
        </div>
      </div>
    </form>
  )
}
