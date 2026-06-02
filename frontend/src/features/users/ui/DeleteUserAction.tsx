import { useDeleteUser, type User } from '@/entities/user'
import { useTranslation } from '@/shared/i18n'
import { DeleteAction } from '@/shared/ui'

interface DeleteUserActionProps {
  user: Pick<User, 'id' | 'email'>
}

/** Delete trigger + confirmation for a single user. */
export function DeleteUserAction({ user }: DeleteUserActionProps) {
  const { t } = useTranslation()
  const mutation = useDeleteUser()

  return (
    <DeleteAction
      triggerLabel={t('common.actions.delete')}
      title={t('admin.users.delete.title')}
      message={t('admin.users.delete.message', { email: user.email })}
      confirmLabel={t('admin.users.delete.confirm')}
      cancelLabel={t('common.actions.cancel')}
      isPending={mutation.isPending}
      error={mutation.error !== null ? t('admin.users.delete.error') : undefined}
      onConfirm={() => mutation.mutateAsync(user.id)}
      onReset={mutation.reset}
    />
  )
}
