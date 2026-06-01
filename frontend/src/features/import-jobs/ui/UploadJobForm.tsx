import type { ImportJob } from '@/entities/import-job'
import { useTranslation } from '@/shared/i18n'
import { Button, Field, Icon, Select, type SelectOption } from '@/shared/ui'
import { useUploadJobForm } from '../hooks/use-upload-job-form'

interface UploadJobFormProps {
  onUploaded: (job: ImportJob) => void
  onCancel: () => void
}

export function UploadJobForm({ onUploaded, onCancel }: UploadJobFormProps) {
  const { t } = useTranslation()
  const {
    presets,
    setFile,
    presetId,
    setPresetId,
    submit,
    isSubmitting,
    error,
    showFileError,
    showPresetError,
  } = useUploadJobForm(onUploaded)

  const presetOptions: readonly SelectOption[] = presets.map((preset) => ({
    value: String(preset.id),
    label: preset.name,
  }))

  return (
    <form
      onSubmit={(event) => {
        event.preventDefault()
        submit()
      }}
      noValidate
    >
      <div className="card">
        <div className="card__head">
          <h2 className="card__title">{t('admin.importJobs.create.cardTitle')}</h2>
        </div>
        <div className="card__body">
          <div className="form-grid">
            <div className="field">
              <span className="field__label">{t('admin.importJobs.create.file')}</span>
              <label className="dropzone">
                <input
                  type="file"
                  accept=".csv,text/csv"
                  hidden
                  aria-label={t('admin.importJobs.create.file')}
                  aria-invalid={showFileError || undefined}
                  onChange={(event) => {
                    setFile(event.target.files?.[0] ?? null)
                  }}
                />
                <span className="dz-icon">
                  <Icon name="upload" />
                </span>
                <b>{t('admin.importJobs.create.dropTitle')}</b>
                <p>{t('admin.importJobs.create.dropHint')}</p>
              </label>
              {showFileError ? (
                <span className="field__error" role="alert">
                  {t('admin.importJobs.create.fileRequired')}
                </span>
              ) : null}
            </div>

            <Field
              label={t('admin.importJobs.create.preset')}
              {...(showPresetError ? { error: t('admin.importJobs.create.presetRequired') } : {})}
            >
              {({ id, invalid }) => (
                <Select
                  id={id}
                  invalid={invalid}
                  options={presetOptions}
                  value={presetId !== null ? String(presetId) : ''}
                  onChange={(event) => {
                    setPresetId(event.target.value === '' ? null : Number(event.target.value))
                  }}
                />
              )}
            </Field>

            {error !== null ? (
              <span className="field__error" role="alert">
                {t('admin.importJobs.create.error')}
              </span>
            ) : null}
          </div>
        </div>
        <div className="card__foot">
          <Button variant="ghost" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" disabled={isSubmitting} data-testid="job-upload-submit">
            {isSubmitting ? t('common.state.submitting') : t('admin.importJobs.create.submit')}
          </Button>
        </div>
      </div>
    </form>
  )
}
