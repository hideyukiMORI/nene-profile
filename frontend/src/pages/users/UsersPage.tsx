import { useState } from 'react'
import { useUsers, type User, type UserRole, type UserStatus } from '@/entities/user'
import { CreateUserForm, DeleteUserAction, EditUserForm } from '@/features/users'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import {
  AsyncBoundary,
  Button,
  DataTable,
  Icon,
  PageHeader,
  Pagination,
  type Column,
} from '@/shared/ui'

const PAGE_SIZE = 20

const roleLabelKey: Record<UserRole, MessageKey> = {
  superadmin: 'admin.users.role.superadmin',
  admin: 'admin.users.role.admin',
  member: 'admin.users.role.member',
  viewer: 'admin.users.role.viewer',
}
const roleClass: Record<UserRole, string> = {
  superadmin: 'role--admin',
  admin: 'role--admin',
  member: 'role--member',
  viewer: 'role--viewer',
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
    {
      id: 'email',
      header: t('admin.users.col.email'),
      render: (u) => <span className="primary-cell mono">{u.email}</span>,
    },
    {
      id: 'role',
      header: t('admin.users.col.role'),
      render: (u) => <span className={`role ${roleClass[u.role]}`}>{t(roleLabelKey[u.role])}</span>,
    },
    {
      id: 'status',
      header: t('admin.users.col.status'),
      render: (u) => (
        <span className={`badge badge--${u.status === 'active' ? 'ok' : 'neutral'}`}>
          <span className="dot" />
          {t(statusLabelKey[u.status])}
        </span>
      ),
    },
    {
      id: 'actions',
      header: t('admin.users.col.actions'),
      align: 'end',
      render: (u) => (
        <>
          <Button
            variant="link"
            onClick={() => {
              setCreating(false)
              setEditing(u)
            }}
          >
            {t('common.actions.edit')}
          </Button>
          <DeleteUserAction user={u} />
        </>
      ),
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <>
      <PageHeader
        title={t('admin.users.title')}
        actions={
          !creating && editing === null ? (
            <Button
              onClick={() => {
                setCreating(true)
              }}
            >
              <Icon name="plus" />
              {t('admin.users.newButton')}
            </Button>
          ) : undefined
        }
      />

      {creating ? (
        <div className="card" style={{ marginBottom: 20 }}>
          <div className="card__body">
            <CreateUserForm
              onCreated={() => {
                setCreating(false)
              }}
              onCancel={() => {
                setCreating(false)
              }}
            />
          </div>
        </div>
      ) : null}

      {editing !== null ? (
        <div className="card" style={{ marginBottom: 20 }}>
          <div className="card__body">
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
        <DataTable
          columns={columns}
          rows={rows}
          rowKey={(u) => u.id}
          emptyLabel={t('admin.users.empty')}
          footer={
            total > 0 ? (
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
            ) : undefined
          }
        />
      </AsyncBoundary>
    </>
  )
}
