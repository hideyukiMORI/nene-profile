import { useState } from 'react'
import { useDeleteMappingPreset, type MappingPreset } from '@/entities/mapping-preset'
import { useTranslation } from '@/shared/i18n'
import { Button, ConfirmDialog } from '@/shared/ui'

interface DeletePresetActionProps {
  preset: Pick<MappingPreset, 'id' | 'name'>
}

/** Delete trigger + confirmation for a single mapping preset. */
export function DeletePresetAction({ preset }: DeletePresetActionProps) {
  const { t } = useTranslation()
  const [open, setOpen] = useState(false)
  const mutation = useDeleteMappingPreset()

  const close = (): void => {
    setOpen(false)
    mutation.reset()
  }

  const confirm = (): void => {
    mutation.mutate(preset.id, {
      onSuccess: () => {
        setOpen(false)
      },
    })
  }

  return (
    <>
      <Button
        variant="ghost"
        size="sm"
        onClick={() => {
          setOpen(true)
        }}
      >
        {t('common.actions.delete')}
      </Button>
      <ConfirmDialog
        open={open}
        destructive
        title={t('admin.mappingPresets.delete.title')}
        message={t('admin.mappingPresets.delete.message', { name: preset.name })}
        confirmLabel={t('admin.mappingPresets.delete.confirm')}
        cancelLabel={t('common.actions.cancel')}
        isConfirming={mutation.isPending}
        error={mutation.error !== null ? t('admin.mappingPresets.delete.error') : undefined}
        onConfirm={confirm}
        onCancel={close}
      />
    </>
  )
}
