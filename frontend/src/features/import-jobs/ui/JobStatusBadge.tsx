import type { ImportJobStatus } from '@/entities/import-job'

type Tone = 'ok' | 'warn' | 'danger' | 'info' | 'neutral'

const statusTone: Record<ImportJobStatus, Tone> = {
  pending: 'neutral',
  running: 'info',
  completed: 'ok',
  completed_with_errors: 'warn',
  failed: 'danger',
}

interface JobStatusBadgeProps {
  status: ImportJobStatus
  /** Localised label (caller resolves from the i18n catalog). */
  label: string
}

/** Coloured status pill for an import job (design-system `.badge`). */
export function JobStatusBadge({ status, label }: JobStatusBadgeProps) {
  return (
    <span className={`badge badge--${statusTone[status]}`}>
      <span className="dot" />
      {label}
    </span>
  )
}
