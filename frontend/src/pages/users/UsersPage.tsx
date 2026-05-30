import { useState } from 'react'
import { useUsers, type User, type UserRole, type UserStatus } from '@/entities/user'
import { CreateUserForm, DeleteUserAction, EditUserForm } from '@/features/users'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { AsyncBoundary, Button, DataTable, Pagination, Stack, Text, type Column } from '@/shared/ui'

const PAGE_SIZE = 20

const roleLabelKey: Record<UserRole, MessageKey> = {
  superadmin: 'admin.users.role.superadmin',
  admin: 'admin.users.role.admin',
  member: 'admin.users.role.member',
  viewer: 'admin.users.role.viewer',
}
const statusLabelKey: Record<UserStatus, MessageKey> = {
  active: 'admin.users.status.active',
  invited: 'admin.users.status.invited',
}

/** Users admin screen: paginated list + create + edit + delete (admin). */
export function UsersPage() {
  const { t } = useTranslation()
  const [offset, setOffset] = useState(0)
  const [creating, setCreating] = useState(false)
  const [editing, setEditing] = useState<User | null>(null)

  const query = useUsers({ limit: PAGE_SIZE, offset })
  const total = query.data?.total ?? 0
  const rows = query.data?.items ?? []

  const columns: readonly Column<User>[] = [
    { id: 'email', header: t('admin.users.col.email'), render: (u) => u.email },
    { id: 'role', header: t('admin.users.col.role'), render: (u) => t(roleLabelKey[u.role]) },
    {
      id: 'status',
      header: t('admin.users.col.status'),
      render: (u) => t(statusLabelKey[u.status]),
    },
    {
      id: 'actions',
      header: t('admin.users.col.actions'),
      align: 'end',
      render: (u) => (
        <span className="inline-flex gap-inline-sm">
          <Button
            variant="ghost"
            size="sm"
            onClick={() => {
              setCreating(false)
              setEditing(u)
            }}
          >
            {t('common.actions.edit')}
          </Button>
          <DeleteUserAction user={u} />
        </span>
      ),
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <Stack gap="lg">
      <div className="flex items-center justify-between">
        <Text as="h1" variant="display">
          {t('admin.users.title')}
        </Text>
        {!creating && editing === null ? (
          <Button
            size="sm"
            onClick={() => {
              setCreating(true)
            }}
          >
            {t('admin.users.newButton')}
          </Button>
        ) : null}
      </div>

      {creating ? (
        <div className="rounded-md border border-border bg-surface p-inline-lg">
          <CreateUserForm
            onCreated={() => {
              setCreating(false)
            }}
            onCancel={() => {
              setCreating(false)
            }}
          />
        </div>
      ) : null}

      {editing !== null ? (
        <div className="rounded-md border border-border bg-surface p-inline-lg">
          <EditUserForm
            user={editing}
            onSaved={() => {
              setEditing(null)
            }}
            onCancel={() => {
              setEditing(null)
            }}
          />
        </div>
      ) : null}

      <AsyncBoundary
        isLoading={query.isPending}
        isError={query.isError}
        loadingLabel={t('common.state.loading')}
        errorLabel={t('admin.users.error')}
        retryLabel={t('common.actions.retry')}
        onRetry={() => {
          void query.refetch()
        }}
      >
        <Stack gap="md">
          <DataTable
            columns={columns}
            rows={rows}
            rowKey={(u) => u.id}
            emptyLabel={t('admin.users.empty')}
          />
          {total > 0 ? (
            <Pagination
              summary={t('common.pagination.summary', { from, to, total })}
              prevLabel={t('common.pagination.prev')}
              nextLabel={t('common.pagination.next')}
              canPrev={offset > 0}
              canNext={offset + PAGE_SIZE < total}
              onPrev={() => {
                setOffset((current) => Math.max(0, current - PAGE_SIZE))
              }}
              onNext={() => {
                setOffset((current) => current + PAGE_SIZE)
              }}
            />
          ) : null}
        </Stack>
      </AsyncBoundary>
    </Stack>
  )
}
