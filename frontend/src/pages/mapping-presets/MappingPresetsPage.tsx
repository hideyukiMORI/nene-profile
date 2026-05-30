import { useState } from 'react'
import { useMappingPresets, type MappingPreset } from '@/entities/mapping-preset'
import { CreatePresetForm, DeletePresetAction } from '@/features/mapping-presets'
import { useTranslation } from '@/shared/i18n'
import { AsyncBoundary, Button, DataTable, Pagination, Stack, Text, type Column } from '@/shared/ui'

const PAGE_SIZE = 20

/** Mapping-presets admin screen: paginated list + create (definition editor) + delete. */
export function MappingPresetsPage() {
  const { t } = useTranslation()
  const [offset, setOffset] = useState(0)
  const [creating, setCreating] = useState(false)

  const query = useMappingPresets({ limit: PAGE_SIZE, offset })
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
      render: (p) => <DeletePresetAction preset={p} />,
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
        {!creating ? (
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
