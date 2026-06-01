import type { ReactNode } from 'react'

export interface PageHeaderProps {
  title: string
  /** Optional muted subtitle under the title. */
  sub?: string
  /** Optional right-aligned actions (buttons). */
  actions?: ReactNode
}

/**
 * Page header (design-system `.page-head`). The title is a real `<h1>` so it
 * carries the heading role consumed by screen-readers and tests.
 */
export function PageHeader({ title, sub, actions }: PageHeaderProps) {
  return (
    <div className="page-head">
      <div>
        <h1 className="page-title">{title}</h1>
        {sub !== undefined && sub !== '' ? <p className="page-sub">{sub}</p> : null}
      </div>
      {actions !== undefined ? <div className="page-actions">{actions}</div> : null}
    </div>
  )
}
