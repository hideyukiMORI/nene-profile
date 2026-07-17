import type { MappingPresetDetail } from '@/entities/mapping-preset'
import { useTranslation } from '@/shared/i18n'
import { Button } from '@/shared/ui'
import { useEditPresetForm } from '../model/use-edit-preset-form'
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
  const { register, handleSubmit, formState, watch } = form

  return (
    <form
      onSubmit={(event) => {
        void handleSubmit(submit)(event)
      }}
      noValidate
    >
      <PresetFields
        register={register}
        errors={formState.errors}
        watch={watch}
        title={t('admin.mappingPresets.edit.title')}
        footer={
          <>
            {error !== null ? (
              <span className="field__error" role="alert" style={{ marginRight: 'auto' }}>
                {t('admin.mappingPresets.edit.error')}
              </span>
            ) : null}
            <Button variant="ghost" disabled={isSubmitting} onClick={onCancel}>
              {t('common.actions.cancel')}
            </Button>
            <Button type="submit" disabled={isSubmitting} data-testid="preset-edit-submit">
              {isSubmitting ? t('common.state.saving') : t('admin.mappingPresets.edit.submit')}
            </Button>
          </>
        }
      />
    </form>
  )
}
