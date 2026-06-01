import type { Organization } from '@/entities/organization'
import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input } from '@/shared/ui'
import { useEditOrganizationForm } from '../hooks/use-edit-organization-form'

interface EditOrganizationFormProps {
  organization: Organization
  onSaved: () => void
  onCancel: () => void
}

export function EditOrganizationForm({
  organization,
  onSaved,
  onCancel,
}: EditOrganizationFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, isSaved, isSlugConflict, isGenericError } =
    useEditOrganizationForm(organization, onSaved)
  const { register, handleSubmit, formState, watch } = form
  const isActive = watch('isActive')

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <div className="card">
        <div className="card__head">
          <h2 className="card__title">{t('admin.organizations.edit.title')}</h2>
        </div>
        <div className="card__body">
          <div className="form-grid">
            <Field
              label={t('admin.organizations.form.name')}
              {...(formState.errors.name
                ? { error: t('admin.organizations.form.nameRequired') }
                : {})}
            >
              {({ id, invalid }) => (
                <Input id={id} invalid={invalid} data-testid="org-name" {...register('name')} />
              )}
            </Field>

            <Field
              label={t('admin.organizations.form.slug')}
              {...(formState.errors.slug
                ? { error: t('admin.organizations.form.slugInvalid') }
                : isSlugConflict
                  ? { error: t('admin.organizations.form.slugConflict') }
                  : {})}
            >
              {({ id, invalid }) => (
                <Input
                  id={id}
                  className="mono"
                  invalid={invalid || isSlugConflict}
                  data-testid="org-slug"
                  {...register('slug')}
                />
              )}
            </Field>

            <Field label={t('admin.organizations.form.customDomain')}>
              {({ id }) => (
                <Input
                  id={id}
                  className="mono"
                  data-testid="org-custom-domain"
                  {...register('customDomain')}
                />
              )}
            </Field>

            <label className={`toggle${isActive ? ' is-on' : ''}`}>
              <input type="checkbox" hidden data-testid="org-is-active" {...register('isActive')} />
              <span className="toggle__track" aria-hidden="true" />
              <span className="toggle__label">{t('admin.organizations.form.isActive')}</span>
            </label>

            {isGenericError ? (
              <span className="field__error" role="alert">
                {t('common.error.generic')}
              </span>
            ) : null}
            {isSaved ? (
              <span role="status" className="form-status">
                {t('admin.organizations.form.saved')}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          <Button variant="ghost" type="button" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" disabled={isSubmitting} data-testid="org-edit-submit">
            {isSubmitting ? t('common.state.saving') : t('common.actions.save')}
          </Button>
        </div>
      </div>
    </form>
  )
}
