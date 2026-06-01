import { useTranslation, type MessageKey } from '@/shared/i18n'
import { Button, Field, Input } from '@/shared/ui'
import { useCreateUserForm } from '../hooks/use-create-user-form'

interface CreateUserFormProps {
  onCreated: () => void
  onCancel: () => void
}

const ROLE_CHOICES: readonly { value: string; label: MessageKey; desc: MessageKey }[] = [
  { value: 'admin', label: 'admin.users.role.admin', desc: 'admin.users.role.adminDesc' },
  { value: 'member', label: 'admin.users.role.member', desc: 'admin.users.role.memberDesc' },
  { value: 'viewer', label: 'admin.users.role.viewer', desc: 'admin.users.role.viewerDesc' },
]

export function CreateUserForm({ onCreated, onCancel }: CreateUserFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useCreateUserForm(onCreated)
  const { register, handleSubmit, formState, watch } = form
  const role = watch('role')

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <div className="card">
        <div className="card__head">
          <h2 className="card__title">{t('admin.users.create.title')}</h2>
        </div>
        <div className="card__body">
          <div className="form-grid">
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

            <div className="field">
              <span className="field__label">{t('admin.users.create.role')}</span>
              <div className="choice-row">
                {ROLE_CHOICES.map((choice) => (
                  <label
                    key={choice.value}
                    className={`choice${role === choice.value ? ' is-sel' : ''}`}
                  >
                    <input type="radio" value={choice.value} hidden {...register('role')} />
                    <span className="radio" aria-hidden="true" />
                    <span className="t">
                      <b>{t(choice.label)}</b>
                      <span>{t(choice.desc)}</span>
                    </span>
                  </label>
                ))}
              </div>
            </div>

            {error !== null ? (
              <span className="field__error" role="alert">
                {t('admin.users.create.error')}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          <Button variant="ghost" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" disabled={isSubmitting} data-testid="user-create-submit">
            {isSubmitting ? t('common.state.submitting') : t('admin.users.create.submit')}
          </Button>
        </div>
      </div>
    </form>
  )
}
