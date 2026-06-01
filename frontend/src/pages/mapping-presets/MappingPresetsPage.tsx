import { useState } from 'react'
import { useMappingPreset, useMappingPresets, type MappingPreset } from '@/entities/mapping-preset'
import { CreatePresetForm, DeletePresetAction, EditPresetForm } from '@/features/mapping-presets'
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

/** Mapping-presets admin screen: paginated list + create + edit (new version) + delete. */
export function MappingPresetsPage() {
  const { t } = useTranslation()
  const [offset, setOffset] = useState(0)
  const [creating, setCreating] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)

  const query = useMappingPresets({ limit: PAGE_SIZE, offset })
  const editQuery = useMappingPreset(editingId)
  const total = query.data?.total ?? 0
  const rows = query.data?.items ?? []

  const columns: readonly Column<MappingPreset>[] = [
    {
      id: 'name',
      header: t('admin.mappingPresets.col.name'),
      render: (p) => (
        <span className="row">
          <span
            className="stat__ic ic-tint-blue"
            aria-hidden="true"
            style={{ width: 28, height: 28, borderRadius: 7 }}
          >
            <Icon name="sliders" />
          </span>
          <span className="primary-cell">{p.name}</span>
        </span>
      ),
    },
    {
      id: 'bankLabel',
      header: t('admin.mappingPresets.col.bankLabel'),
      render: (p) => p.bankLabel,
    },
    {
      id: 'version',
      header: t('admin.mappingPresets.col.version'),
      render: (p) => <span className="tag">{`v${String(p.versionNumber)}`}</span>,
    },
    {
      id: 'actions',
      header: t('admin.mappingPresets.col.actions'),
      align: 'end',
      render: (p) => (
        <>
          <Button
            variant="link"
            onClick={() => {
              setCreating(false)
              setEditingId(p.id)
            }}
          >
            {t('common.actions.edit')}
          </Button>
          <DeletePresetAction preset={p} />
        </>
      ),
    },
  ]

  const from = total === 0 ? 0 : offset + 1
  const to = Math.min(offset + PAGE_SIZE, total)

  return (
    <>
      <PageHeader
        title={t('admin.mappingPresets.title')}
        actions={
          !creating && editingId === null ? (
            <Button
              onClick={() => {
                setCreating(true)
              }}
            >
              <Icon name="plus" />
              {t('admin.mappingPresets.newButton')}
            </Button>
          ) : undefined
        }
      />

      {editingId !== null ? (
        <div style={{ maxWidth: 860, marginBottom: 20 }}>
          <AsyncBoundary
            isLoading={editQuery.isPending}
            isError={editQuery.isError}
            loadingLabel={t('common.state.loading')}
            errorLabel={t('admin.mappingPresets.error')}
            retryLabel={t('common.actions.retry')}
            onRetry={() => {
              void editQuery.refetch()
            }}
          >
            {editQuery.data !== undefined ? (
              <EditPresetForm
                preset={editQuery.data}
                onSaved={() => {
                  setEditingId(null)
                }}
                onCancel={() => {
                  setEditingId(null)
                }}
              />
            ) : null}
          </AsyncBoundary>
        </div>
      ) : null}

      {creating ? (
        <div style={{ maxWidth: 860, marginBottom: 20 }}>
          <CreatePresetForm
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
        errorLabel={t('admin.mappingPresets.error')}
        retryLabel={t('common.actions.retry')}
        onRetry={() => {
          void query.refetch()
        }}
      >
        <DataTable
          columns={columns}
          rows={rows}
          rowKey={(p) => p.id}
          emptyLabel={t('admin.mappingPresets.empty')}
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
