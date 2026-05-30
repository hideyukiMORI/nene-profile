import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Stack, Text } from '@/shared/ui'
import { useCreateOrganizationForm } from '../hooks/use-create-organization-form'

interface CreateOrganizationFormProps {
  onCreated: () => void
  onCancel: () => void
}

/**
 * Create-organization form. All copy is i18n; logic lives in the hook. The slug
 * field surfaces two distinct messages (required vs. invalid) via the RHF error
 * type so the catalog stays the single source of truth.
 */
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
      <Stack gap="lg">
        <Text as="h2" variant="heading">
          {t('admin.organizations.create.title')}
        </Text>

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
            <Input id={id} type="text" invalid={invalid} {...register('slug')} />
          )}
        </Field>

        <Field label={t('admin.organizations.create.customDomain')}>
          {({ id }) => <Input id={id} type="text" {...register('customDomain')} />}
        </Field>

        {error !== null ? (
          <Text variant="caption" tone="danger">
            {t('admin.organizations.create.error')}
          </Text>
        ) : null}

        <div className="flex justify-end gap-inline-sm">
          <Button variant="ghost" size="sm" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" size="sm" disabled={isSubmitting} data-testid="org-create-submit">
            {isSubmitting ? t('common.state.submitting') : t('admin.organizations.create.submit')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
