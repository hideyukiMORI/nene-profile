import { useDeleteMappingPreset, type MappingPreset } from '@/entities/mapping-preset'
import { useTranslation } from '@/shared/i18n'
import { DeleteAction } from '@/shared/ui'

interface DeletePresetActionProps {
  preset: Pick<MappingPreset, 'id' | 'name'>
}

/** Delete trigger + confirmation for a single mapping preset. */
export function DeletePresetAction({ preset }: DeletePresetActionProps) {
  const { t } = useTranslation()
  const mutation = useDeleteMappingPreset()

  return (
    <DeleteAction
      triggerLabel={t('common.actions.delete')}
      title={t('admin.mappingPresets.delete.title')}
      message={t('admin.mappingPresets.delete.message', { name: preset.name })}
      confirmLabel={t('admin.mappingPresets.delete.confirm')}
      cancelLabel={t('common.actions.cancel')}
      isPending={mutation.isPending}
      error={mutation.error !== null ? t('admin.mappingPresets.delete.error') : undefined}
      onConfirm={() => mutation.mutateAsync(preset.id)}
      onReset={mutation.reset}
    />
  )
}
