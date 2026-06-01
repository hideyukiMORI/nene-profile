import { Button } from '@/shared/ui/primitives/Button'

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

/**
 * Presentational prev/next pager (design-system `.pager`). Rendered as the
 * footer of a DataTable card. Offset math is the caller's.
 */
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
    <div className="pager">
      <div className="count">{summary}</div>
      <div className="nav">
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
