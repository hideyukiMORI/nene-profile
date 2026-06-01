import { useState } from 'react'
import { useOrganizations, type Organization } from '@/entities/organization'
import {
  CreateOrganizationForm,
  DeleteOrganizationAction,
  EditOrganizationForm,
} from '@/features/organizations'
import { useTranslation } from '@/shared/i18n'
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

/** Organizations admin screen: paginated list + create + edit + delete (superadmin). */
export function OrganizationsPage() {
  const { t } = useTranslation()
  const [offset, setOffset] = useState(0)
  const [creating, setCreating] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)

  const query = useOrganizations({ limit: PAGE_SIZE, offset })
  const total = query.data?.total ?? 0
  const rows = query.data?.items ?? []

  const columns: readonly Column<Organization>[] = [
    {
      id: 'name',
      header: t('admin.organizations.col.name'),
      render: (o) => (
        <span style={{ minWidth: 0, display: 'block' }}>
          <span className="primary-cell">{o.name}</span>
          <span className="muted" style={{ display: 'block', fontSize: 11, fontWeight: 500 }}>
            <span className="mono">{o.slug}</span>
            {o.customDomain !== null ? (
              <>
                {' ・ '}
                <span className="mono">{o.customDomain}</span>
              </>
            ) : null}
          </span>
        </span>
      ),
    },
    {
      id: 'status',
      header: t('admin.organizations.col.status'),
      render: (o) =>
        o.isActive ? (
          <span className="badge badge--ok">
            <span className="dot" />
            {t('admin.organizations.status.active')}
          </span>
        ) : (
          <span className="badge badge--neutral">
            <span className="dot" />
            {t('admin.organizations.status.inactive')}
          </span>
        ),
    },
    {
      id: 'actions',
      header: t('admin.organizations.col.actions'),
      align: 'end',
      render: (o) => (
        <>
          <Button
            variant="link"
            onClick={() => {
              setEditingId(o.id)
            }}
            data-testid={`org-edit-${String(o.id)}`}
          >
            {t('admin.organizations.editButton')}
          </Button>
          <DeleteOrganizationAction organization={o} />
        </>
      ),
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)
  const editingOrg = editingId !== null ? (rows.find((o) => o.id === editingId) ?? null) : null

  return (
    <>
      <PageHeader
        title={t('admin.organizations.title')}
        actions={
          !creating && editingId === null ? (
            <Button
              onClick={() => {
                setCreating(true)
              }}
            >
              <Icon name="plus" />
              {t('admin.organizations.newButton')}
            </Button>
          ) : undefined
        }
      />

      {creating ? (
        <div style={{ maxWidth: '680px', marginBottom: 20 }}>
          <CreateOrganizationForm
            onCreated={() => {
              setCreating(false)
            }}
            onCancel={() => {
              setCreating(false)
            }}
          />
        </div>
      ) : null}

      {editingOrg !== null ? (
        <div style={{ maxWidth: '680px', marginBottom: 20 }}>
          <EditOrganizationForm
            organization={editingOrg}
            onSaved={() => {
              setEditingId(null)
            }}
            onCancel={() => {
              setEditingId(null)
            }}
          />
        </div>
      ) : null}

      <AsyncBoundary
        isLoading={query.isPending}
        isError={query.isError}
        loadingLabel={t('common.state.loading')}
        errorLabel={t('admin.organizations.error')}
        retryLabel={t('common.actions.retry')}
        onRetry={() => {
          void query.refetch()
        }}
      >
        <DataTable
          columns={columns}
          rows={rows}
          rowKey={(o) => o.id}
          emptyLabel={t('admin.organizations.empty')}
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
