import { useTranslation } from '@/shared/i18n'
import { Button } from '@/shared/ui'
import { useCreatePresetForm } from '../hooks/use-create-preset-form'
import { PresetFields } from './PresetFields'

interface CreatePresetFormProps {
  onCreated: () => void
  onCancel: () => void
}

/** Create-preset form: shared definition editor + create mutation. */
export function CreatePresetForm({ onCreated, onCancel }: CreatePresetFormProps) {
  const { t } = useTranslation()
  const { form, submit, isSubmitting, error } = useCreatePresetForm(onCreated)
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
        title={t('admin.mappingPresets.create.title')}
        footer={
          <>
            {error !== null ? (
              <span className="field__error" role="alert" style={{ marginRight: 'auto' }}>
                {t('admin.mappingPresets.create.error')}
              </span>
            ) : null}
            <Button variant="ghost" disabled={isSubmitting} onClick={onCancel}>
              {t('common.actions.cancel')}
            </Button>
            <Button type="submit" disabled={isSubmitting} data-testid="preset-create-submit">
              {isSubmitting
                ? t('common.state.submitting')
                : t('admin.mappingPresets.create.submit')}
            </Button>
          </>
        }
      />
    </form>
  )
}
