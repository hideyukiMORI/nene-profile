import type { OrganizationSettings } from '@/entities/organization-settings'
import { useTranslation } from '@/shared/i18n'
import { Button, Field, Input, Select, type SelectOption } from '@/shared/ui'
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
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <div className="card">
        <div className="card__head">
          <h2 className="card__title">{t('admin.settings.cardTitle')}</h2>
        </div>
        <div className="card__body">
          <div className="form-grid">
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

            {error !== null ? (
              <span className="field__error" role="alert">
                {t('admin.settings.error')}
              </span>
            ) : null}
            {isSaved ? (
              <span role="status" className="form-status">
                {t('admin.settings.saved')}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          <Button
            variant="ghost"
            type="button"
            disabled={isSubmitting}
            onClick={() => {
              reset()
            }}
          >
            {t('common.actions.discard')}
          </Button>
          <Button type="submit" disabled={isSubmitting} data-testid="settings-save">
            {isSubmitting ? t('common.state.saving') : t('admin.settings.save')}
          </Button>
        </div>
      </div>
    </form>
  )
}
