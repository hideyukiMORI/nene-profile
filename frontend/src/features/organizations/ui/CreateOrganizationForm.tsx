import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input } from '@/shared/ui'
import { useCreateOrganizationForm } from '../hooks/use-create-organization-form'

interface CreateOrganizationFormProps {
  onCreated: () => void
  onCancel: () => void
}

export function CreateOrganizationForm({ onCreated, onCancel }: CreateOrganizationFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useCreateOrganizationForm(onCreated)
  const { register, handleSubmit, formState } = form

  const slugError = formState.errors.slug
    ? formState.errors.slug.type === 'too_small'
      ? t('admin.organizations.create.slugRequired')
      : t('admin.organizations.create.slugInvalid')
    : undefined

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <div className="card">
        <div className="card__head">
          <h2 className="card__title">{t('admin.organizations.create.title')}</h2>
        </div>
        <div className="card__body">
          <div className="form-grid">
            <Field
              label={t('admin.organizations.create.name')}
              {...(formState.errors.name
                ? { error: t('admin.organizations.create.nameRequired') }
                : {})}
            >
              {({ id, invalid }) => (
                <Input id={id} type="text" invalid={invalid} {...register('name')} />
              )}
            </Field>

            <Field
              label={t('admin.organizations.create.slug')}
              {...(slugError !== undefined ? { error: slugError } : {})}
            >
              {({ id, invalid }) => (
                <Input
                  id={id}
                  type="text"
                  className="mono"
                  invalid={invalid}
                  {...register('slug')}
                />
              )}
            </Field>

            <Field label={t('admin.organizations.create.customDomain')}>
              {({ id }) => (
                <Input id={id} type="text" className="mono" {...register('customDomain')} />
              )}
            </Field>

            {error !== null ? (
              <span className="field__error" role="alert">
                {t('admin.organizations.create.error')}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          <Button variant="ghost" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" disabled={isSubmitting} data-testid="org-create-submit">
            {isSubmitting ? t('common.state.submitting') : t('admin.organizations.create.submit')}
          </Button>
        </div>
      </div>
    </form>
  )
}
