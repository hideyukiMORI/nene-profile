import { useState } from 'react'
import { useDeleteOrganization, type Organization } from '@/entities/organization'
import { useTranslation } from '@/shared/i18n'
import { Button, ConfirmDialog } from '@/shared/ui'

interface DeleteOrganizationActionProps {
  organization: Pick<Organization, 'id' | 'name'>
}

/**
 * Delete trigger + confirmation for a single organization. Owns the dialog open
 * state and drives the entity mutation; the list refreshes via query invalidation.
 */
export function DeleteOrganizationAction({ organization }: DeleteOrganizationActionProps) {
  const { t } = useTranslation()
  const [open, setOpen] = useState(false)
  const mutation = useDeleteOrganization()

  const close = (): void => {
    setOpen(false)
    mutation.reset()
  }

  const confirm = (): void => {
    mutation.mutate(organization.id, {
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
        title={t('admin.organizations.delete.title')}
        message={t('admin.organizations.delete.message', { name: organization.name })}
        confirmLabel={t('admin.organizations.delete.confirm')}
        cancelLabel={t('common.actions.cancel')}
        isConfirming={mutation.isPending}
        error={mutation.error !== null ? t('admin.organizations.delete.error') : undefined}
        onConfirm={confirm}
        onCancel={close}
      />
    </>
  )
}
