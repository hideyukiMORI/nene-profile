import type { OrganizationSettings } from '@/entities/organization-settings'
import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Select, Stack, Text, type SelectOption } from '@/shared/ui'
import { ENCODINGS, useSettingsForm } from '../hooks/use-settings-form'

interface SettingsFormProps {
  settings: OrganizationSettings
}

/** Organization settings form: default encoding, max file size, Clear token. */
export function SettingsForm({ settings }: SettingsFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, isSaved, error } = useSettingsForm(settings)
  const { register, handleSubmit, formState } = form

  const encodingOptions: readonly SelectOption[] = ENCODINGS.map((value) => ({
    value,
    label: value,
  }))

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <Stack gap="lg">
        <Field label={t('admin.settings.defaultEncoding')}>
          {({ id }) => (
            <Select id={id} options={encodingOptions} {...register('defaultEncoding')} />
          )}
        </Field>

        <Field
          label={t('admin.settings.maxFileSize')}
          {...(formState.errors.maxFileSizeBytes
            ? { error: t('admin.settings.maxFileSizeInvalid') }
            : {})}
        >
          {({ id, invalid }) => (
            <Input
              id={id}
              type="number"
              min={1}
              invalid={invalid}
              {...register('maxFileSizeBytes', { valueAsNumber: true })}
            />
          )}
        </Field>

        <Field label={t('admin.settings.clearBearerToken')}>
          {({ id }) => (
            <Stack gap="xs">
              <Input
                id={id}
                type="password"
                autoComplete="off"
                placeholder={t('admin.settings.clearBearerTokenHint')}
                {...register('clearBearerToken')}
              />
              {settings.clearBearerTokenSet ? (
                <Text variant="caption" tone="muted">
                  {t('admin.settings.clearBearerTokenSet')}
                </Text>
              ) : null}
            </Stack>
          )}
        </Field>

        {error !== null ? (
          <Text variant="caption" tone="danger">
            {t('admin.settings.error')}
          </Text>
        ) : null}
        {isSaved ? (
          <span role="status" className="text-caption text-text-muted">
            {t('admin.settings.saved')}
          </span>
        ) : null}

        <div className="flex justify-end">
          <Button type="submit" size="sm" disabled={isSubmitting} data-testid="settings-save">
            {isSubmitting ? t('common.state.saving') : t('admin.settings.save')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
