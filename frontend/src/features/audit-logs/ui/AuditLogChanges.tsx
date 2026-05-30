import { useState } from 'react'
import { useTranslation } from '@/shared/i18n'
import { Button, Stack, Text } from '@/shared/ui'

interface AuditLogChangesProps {
  before: Record<string, unknown> | null
  after: Record<string, unknown> | null
}

function format(value: Record<string, unknown> | null): string {
  return value === null ? '—' : JSON.stringify(value, null, 2)
}

/** Expandable before/after snapshot viewer for a single audit entry. */
export function AuditLogChanges({ before, after }: AuditLogChangesProps) {
  const { t } = useTranslation()
  const [open, setOpen] = useState(false)

  if (before === null && after === null) {
    return (
      <Text variant="caption" tone="muted">
        —
      </Text>
    )
  }

  return (
    <Stack gap="xs">
      <div>
        <Button
          variant="ghost"
          size="sm"
          onClick={() => {
            setOpen((current) => !current)
          }}
        >
          {t('admin.auditLogs.viewDiff')}
        </Button>
      </div>
      {open ? (
        <div className="grid grid-cols-2 gap-inline-md">
          <Stack gap="xs">
            <Text variant="caption" tone="muted">
              {t('admin.auditLogs.changes.before')}
            </Text>
            <pre className="overflow-auto rounded-sm border border-border bg-surface-raised p-inline-sm text-caption">
              {format(before)}
            </pre>
          </Stack>
          <Stack gap="xs">
            <Text variant="caption" tone="muted">
              {t('admin.auditLogs.changes.after')}
            </Text>
            <pre className="overflow-auto rounded-sm border border-border bg-surface-raised p-inline-sm text-caption">
              {format(after)}
            </pre>
          </Stack>
        </div>
      ) : null}
    </Stack>
  )
}
