import type { Organization } from '@/entities/organization'
import { useTranslation } from '@/shared/i18n'
import { Field, FormCard, Input } from '@/shared/ui'
import { useEditOrganizationForm } from '../model/use-edit-organization-form'

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
    <FormCard
      title={t('admin.organizations.edit.title')}
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      isSubmitting={isSubmitting}
      submitLabel={t('common.actions.save')}
      submittingLabel={t('common.state.saving')}
      submitTestId="org-edit-submit"
      cancel={{ label: t('common.actions.cancel'), onClick: onCancel }}
      {...(isGenericError ? { error: t('common.error.generic') } : {})}
      {...(isSaved ? { status: t('admin.organizations.form.saved') } : {})}
    >
      <Field
        label={t('admin.organizations.form.name')}
        {...(formState.errors.name ? { error: t('admin.organizations.form.nameRequired') } : {})}
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
    </FormCard>
  )
}
