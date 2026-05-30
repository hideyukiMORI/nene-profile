import type { MappingPresetDetail } from '@/entities/mapping-preset'
import { useTranslation } from '@/shared/i18n'
import { Button, Stack, Text } from '@/shared/ui'
import { useEditPresetForm } from '../hooks/use-edit-preset-form'
import { PresetFields } from './PresetFields'

interface EditPresetFormProps {
  preset: MappingPresetDetail
  onSaved: () => void
  onCancel: () => void
}

/** Edit-preset form: shared definition editor pre-filled + update (new version). */
export function EditPresetForm({ preset, onSaved, onCancel }: EditPresetFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useEditPresetForm(preset, onSaved)
  const { register, handleSubmit, formState } = form

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <Stack gap="lg">
        <Text as="h2" variant="heading">
          {t('admin.mappingPresets.edit.title')}
        </Text>

        <PresetFields register={register} errors={formState.errors} />

        {error !== null ? (
          <Text variant="caption" tone="danger">
            {t('admin.mappingPresets.edit.error')}
          </Text>
        ) : null}

        <div className="flex justify-end gap-inline-sm">
          <Button variant="ghost" size="sm" disabled={isSubmitting} onClick={onCancel}>
            {t('common.actions.cancel')}
          </Button>
          <Button type="submit" size="sm" disabled={isSubmitting} data-testid="preset-edit-submit">
            {isSubmitting ? t('common.state.saving') : t('admin.mappingPresets.edit.submit')}
          </Button>
        </div>
      </Stack>
    </form>
  )
}
