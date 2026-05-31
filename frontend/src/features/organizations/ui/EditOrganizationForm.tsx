import type { Organization } from '@/entities/organization'
import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Stack, Text } from '@/shared/ui'
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
  const { register, handleSubmit, formState } = form

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <Stack gap="md">
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
              invalid={invalid || isSlugConflict}
              data-testid="org-slug"
              {...register('slug')}
            />
          )}
        </Field>

        <Field label={t('admin.organizations.form.isActive')}>
          {({ id }) => (
            <input id={id} type="checkbox" {...register('isActive')} data-testid="org-is-active" />
          )}
        </Field>

        <Field label={t('admin.organizations.form.customDomain')}>
          {({ id }) => (
            <Input id={id} data-testid="org-custom-domain" {...register('customDomain')} />
          )}
        </Field>

        {isGenericError ? (
          <Text variant="caption" tone="danger">
            {t('common.error.generic')}
          </Text>
        ) : null}

        {isSaved ? (
          <span role="status" className="text-caption text-text-muted">
            {t('admin.organizations.form.saved')}
          </span>
        ) : null}

        <div className="flex justify-end gap-inline-md">
          <Button variant="ghost" size="sm" type="button" onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" size="sm" disabled={isSubmitting} data-testid="org-edit-submit">
            {isSubmitting ? t('common.state.saving') : t('common.actions.save')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
