import { useState } from 'react'
import { useDeleteUser, type User } from '@/entities/user'
import { useTranslation } from '@/shared/i18n'
import { Button, ConfirmDialog } from '@/shared/ui'

interface DeleteUserActionProps {
  user: Pick<User, 'id' | 'email'>
}

/** Delete trigger + confirmation for a single user. */
export function DeleteUserAction({ user }: DeleteUserActionProps) {
  const { t } = useTranslation()
  const [open, setOpen] = useState(false)
  const mutation = useDeleteUser()

  const close = (): void => {
    setOpen(false)
    mutation.reset()
  }

  const confirm = (): void => {
    mutation.mutate(user.id, {
      onSuccess: () => {
        setOpen(false)
      },
    })
  }

  return (
    <>
      <Button
        variant="link-danger"
        onClick={() => {
          setOpen(true)
        }}
      >
        {t('common.actions.delete')}
      </Button>
      <ConfirmDialog
        open={open}
        destructive
        title={t('admin.users.delete.title')}
        message={t('admin.users.delete.message', { email: user.email })}
        confirmLabel={t('admin.users.delete.confirm')}
        cancelLabel={t('common.actions.cancel')}
        isConfirming={mutation.isPending}
        error={mutation.error !== null ? t('admin.users.delete.error') : undefined}
        onConfirm={confirm}
        onCancel={close}
      />
    </>
  )
}
