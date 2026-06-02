import { useTranslation, type MessageKey } from '@/shared/i18n'
import { Field, FormCard, Input } from '@/shared/ui'
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
    <FormCard
      title={t('admin.users.create.title')}
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      isSubmitting={isSubmitting}
      submitLabel={t('admin.users.create.submit')}
      submittingLabel={t('common.state.submitting')}
      submitTestId="user-create-submit"
      cancel={{ label: t('common.actions.cancel'), onClick: onCancel }}
      {...(error !== null ? { error: t('admin.users.create.error') } : {})}
    >
      <Field
        label={t('admin.users.create.email')}
        {...(formState.errors.email ? { error: t('admin.users.create.emailRequired') } : {})}
      >
        {({ id, invalid }) => (
          <Input id={id} type="email" autoComplete="off" invalid={invalid} {...register('email')} />
        )}
      </Field>

      <Field
        label={t('admin.users.create.password')}
        {...(formState.errors.password ? { error: t('admin.users.create.passwordRequired') } : {})}
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
            <label key={choice.value} className={`choice${role === choice.value ? ' is-sel' : ''}`}>
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
    </FormCard>
  )
}
