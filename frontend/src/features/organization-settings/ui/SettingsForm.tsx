import type { OrganizationSettings } from '@/entities/organization-settings'
import { useTranslation } from '@/shared/i18n'
import { Field, FormCard, Input, Select, type SelectOption } from '@/shared/ui'
import { ENCODINGS, useSettingsForm } from '../hooks/use-settings-form'

interface SettingsFormProps {
  settings: OrganizationSettings
}

function megabytes(bytes: number): string {
  if (!Number.isFinite(bytes) || bytes <= 0) return '—'
  const mb = bytes / 1024 / 1024
  return `= ${Number.isInteger(mb) ? String(mb) : mb.toFixed(1)} MB`
}

/** Organization settings form: default encoding, max file size, Clear token. */
export function SettingsForm({ settings }: SettingsFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, isSaved, error } = useSettingsForm(settings)
  const { register, handleSubmit, formState, watch, reset } = form

  const encodingOptions: readonly SelectOption[] = ENCODINGS.map((value) => ({
    value,
    label: value,
  }))
  const maxBytes = watch('maxFileSizeBytes')

  return (
    <FormCard
      title={t('admin.settings.cardTitle')}
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      isSubmitting={isSubmitting}
      submitLabel={t('admin.settings.save')}
      submittingLabel={t('common.state.saving')}
      submitTestId="settings-save"
      cancel={{
        label: t('common.actions.discard'),
        onClick: () => {
          reset()
        },
      }}
      {...(error !== null ? { error: t('admin.settings.error') } : {})}
      {...(isSaved ? { status: t('admin.settings.saved') } : {})}
    >
      <Field label={t('admin.settings.defaultEncoding')}>
        {({ id }) => <Select id={id} options={encodingOptions} {...register('defaultEncoding')} />}
      </Field>

      <Field
        label={t('admin.settings.maxFileSize')}
        {...(formState.errors.maxFileSizeBytes
          ? { error: t('admin.settings.maxFileSizeInvalid') }
          : {})}
      >
        {({ id, invalid }) => (
          <div className="input-affix">
            <Input
              id={id}
              type="number"
              min={1}
              className="mono"
              invalid={invalid}
              {...register('maxFileSizeBytes', { valueAsNumber: true })}
            />
            <span className="suffix">{megabytes(maxBytes)}</span>
          </div>
        )}
      </Field>

      <hr className="divider" />

      <Field label={t('admin.settings.clearBearerToken')}>
        {({ id }) => (
          <>
            <Input
              id={id}
              type="password"
              className="mono"
              autoComplete="off"
              placeholder={t('admin.settings.clearBearerTokenHint')}
              {...register('clearBearerToken')}
            />
            {settings.clearBearerTokenSet ? (
              <span className="field__hint">{t('admin.settings.clearBearerTokenSet')}</span>
            ) : null}
          </>
        )}
      </Field>
    </FormCard>
  )
}
