import type { ImportJob } from '@/entities/import-job'
import { useTranslation } from '@/shared/i18n'
import { Button, Field, Select, Stack, Text, type SelectOption } from '@/shared/ui'
import { useUploadJobForm } from '../hooks/use-upload-job-form'

interface UploadJobFormProps {
  onUploaded: (job: ImportJob) => void
  onCancel: () => void
}

/** CSV upload form: file + preset selection, driving the multipart mutation. */
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
      <Stack gap="lg">
        <Text as="h2" variant="heading">
          {t('admin.importJobs.create.title')}
        </Text>

        <Field
          label={t('admin.importJobs.create.file')}
          {...(showFileError ? { error: t('admin.importJobs.create.fileRequired') } : {})}
        >
          {({ id, invalid }) => (
            <input
              id={id}
              type="file"
              accept=".csv,text/csv"
              aria-invalid={invalid}
              onChange={(event) => {
                setFile(event.target.files?.[0] ?? null)
              }}
            />
          )}
        </Field>

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
          <Text variant="caption" tone="danger">
            {t('admin.importJobs.create.error')}
          </Text>
        ) : null}

        <div className="flex justify-end gap-inline-sm">
          <Button variant="ghost" size="sm" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" size="sm" disabled={isSubmitting} data-testid="job-upload-submit">
            {isSubmitting ? t('common.state.submitting') : t('admin.importJobs.create.submit')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
