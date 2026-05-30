import { Button } from '@/shared/ui/primitives/Button'
import { Text } from '@/shared/ui/primitives/Text'

export interface PaginationProps {
  /** Pre-rendered summary, e.g. "Showing 1–20 of 57" (interpolated by caller). */
  summary: string
  prevLabel: string
  nextLabel: string
  canPrev: boolean
  canNext: boolean
  onPrev: () => void
  onNext: () => void
}

/** Presentational prev/next pager with a summary line. Offset math is the caller's. */
export function Pagination({
  summary,
  prevLabel,
  nextLabel,
  canPrev,
  canNext,
  onPrev,
  onNext,
}: PaginationProps) {
  return (
    <div className="flex items-center justify-between">
      <Text variant="caption" tone="muted">
        {summary}
      </Text>
      <div className="flex gap-inline-sm">
        <Button variant="secondary" size="sm" disabled={!canPrev} onClick={onPrev}>
          {prevLabel}
        </Button>
        <Button variant="secondary" size="sm" disabled={!canNext} onClick={onNext}>
          {nextLabel}
        </Button>
      </div>
    </div>
  )
}
