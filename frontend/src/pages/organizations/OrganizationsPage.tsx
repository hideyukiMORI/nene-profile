import { useState } from 'react'
import { useOrganizations, type Organization } from '@/entities/organization'
import { CreateOrganizationForm, DeleteOrganizationAction } from '@/features/organizations'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, Button, DataTable, Pagination, Stack, Text, type Column } from '@/shared/ui'

const PAGE_SIZE = 20

/** Organizations admin screen: paginated list + create + delete (superadmin). */
export function OrganizationsPage() {
  const { t } = useTranslation()
  const [offset, setOffset] = useState(0)
  const [creating, setCreating] = useState(false)

  const query = useOrganizations({ limit: PAGE_SIZE, offset })
  const total = query.data?.total ?? 0
  const rows = query.data?.items ?? []

  const columns: readonly Column<Organization>[] = [
    { id: 'name', header: t('admin.organizations.col.name'), render: (o) => o.name },
    { id: 'slug', header: t('admin.organizations.col.slug'), render: (o) => o.slug },
    {
      id: 'status',
      header: t('admin.organizations.col.status'),
      render: (o) =>
        o.isActive
          ? t('admin.organizations.status.active')
          : t('admin.organizations.status.inactive'),
    },
    {
      id: 'customDomain',
      header: t('admin.organizations.col.customDomain'),
      render: (o) => o.customDomain ?? '—',
    },
    {
      id: 'actions',
      header: t('admin.organizations.col.actions'),
      align: 'end',
      render: (o) => <DeleteOrganizationAction organization={o} />,
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <Stack gap="lg">
      <div className="flex items-center justify-between">
        <Text as="h1" variant="display">
          {t('admin.organizations.title')}
        </Text>
        {!creating ? (
          <Button
            size="sm"
            onClick={() => {
              setCreating(true)
            }}
          >
            {t('admin.organizations.newButton')}
          </Button>
        ) : null}
      </div>

      {creating ? (
        <div className="rounded-md border border-border bg-surface p-inline-lg">
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
        <Stack gap="md">
          <DataTable
            columns={columns}
            rows={rows}
            rowKey={(o) => o.id}
            emptyLabel={t('admin.organizations.empty')}
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
