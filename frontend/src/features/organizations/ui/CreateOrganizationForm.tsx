import { useTranslation } from '@/shared/i18n'
import { Field, FormCard, Input } from '@/shared/ui'
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
    <FormCard
      title={t('admin.organizations.create.title')}
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      isSubmitting={isSubmitting}
      submitLabel={t('admin.organizations.create.submit')}
      submittingLabel={t('common.state.submitting')}
      submitTestId="org-create-submit"
      cancel={{ label: t('common.actions.cancel'), onClick: onCancel }}
      {...(error !== null ? { error: t('admin.organizations.create.error') } : {})}
    >
      <Field
        label={t('admin.organizations.create.name')}
        {...(formState.errors.name ? { error: t('admin.organizations.create.nameRequired') } : {})}
      >
        {({ id, invalid }) => <Input id={id} type="text" invalid={invalid} {...register('name')} />}
      </Field>

      <Field
        label={t('admin.organizations.create.slug')}
        {...(slugError !== undefined ? { error: slugError } : {})}
      >
        {({ id, invalid }) => (
          <Input id={id} type="text" className="mono" invalid={invalid} {...register('slug')} />
        )}
      </Field>

      <Field label={t('admin.organizations.create.customDomain')}>
        {({ id }) => <Input id={id} type="text" className="mono" {...register('customDomain')} />}
      </Field>
    </FormCard>
  )
}
