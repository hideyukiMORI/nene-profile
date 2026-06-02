import { useDeleteOrganization, type Organization } from '@/entities/organization'
import { useTranslation } from '@/shared/i18n'
import { DeleteAction } from '@/shared/ui'

interface DeleteOrganizationActionProps {
  organization: Pick<Organization, 'id' | 'name'>
}

/** Delete trigger + confirmation for a single organization. */
export function DeleteOrganizationAction({ organization }: DeleteOrganizationActionProps) {
  const { t } = useTranslation()
  const mutation = useDeleteOrganization()

  return (
    <DeleteAction
      triggerLabel={t('common.actions.delete')}
      title={t('admin.organizations.delete.title')}
      message={t('admin.organizations.delete.message', { name: organization.name })}
      confirmLabel={t('admin.organizations.delete.confirm')}
      cancelLabel={t('common.actions.cancel')}
      isPending={mutation.isPending}
      error={mutation.error !== null ? t('admin.organizations.delete.error') : undefined}
      onConfirm={() => mutation.mutateAsync(organization.id)}
      onReset={mutation.reset}
    />
  )
}
