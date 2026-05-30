import { useState } from 'react'
import { useMappingPreset, useMappingPresets, type MappingPreset } from '@/entities/mapping-preset'
import { CreatePresetForm, DeletePresetAction, EditPresetForm } from '@/features/mapping-presets'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, Button, DataTable, Pagination, Stack, Text, type Column } from '@/shared/ui'

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
    { id: 'name', header: t('admin.mappingPresets.col.name'), render: (p) => p.name },
    {
      id: 'bankLabel',
      header: t('admin.mappingPresets.col.bankLabel'),
      render: (p) => p.bankLabel,
    },
    {
      id: 'version',
      header: t('admin.mappingPresets.col.version'),
      render: (p) => `v${String(p.versionNumber)}`,
    },
    {
      id: 'actions',
      header: t('admin.mappingPresets.col.actions'),
      align: 'end',
      render: (p) => (
        <span className="inline-flex gap-inline-sm">
          <Button
            variant="ghost"
            size="sm"
            onClick={() => {
              setCreating(false)
              setEditingId(p.id)
            }}
          >
            {t('common.actions.edit')}
          </Button>
          <DeletePresetAction preset={p} />
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
          {t('admin.mappingPresets.title')}
        </Text>
        {!creating && editingId === null ? (
          <Button
            size="sm"
            onClick={() => {
              setCreating(true)
            }}
          >
            {t('admin.mappingPresets.newButton')}
          </Button>
        ) : null}
      </div>

      {editingId !== null ? (
        <div className="rounded-md border border-border bg-surface p-inline-lg">
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
        <div className="rounded-md border border-border bg-surface p-inline-lg">
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
        <Stack gap="md">
          <DataTable
            columns={columns}
            rows={rows}
            rowKey={(p) => p.id}
            emptyLabel={t('admin.mappingPresets.empty')}
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
