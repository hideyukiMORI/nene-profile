import type { ReactNode } from 'react'

export interface Column<T> {
  /** Stable column id; also used as the React key for header/cells. */
  id: string
  header: string
  render: (row: T) => ReactNode
  align?: 'start' | 'end'
}

export interface DataTableProps<T> {
  columns: readonly Column<T>[]
  rows: readonly T[]
  rowKey: (row: T) => string | number
  emptyLabel: string
  /** Optional toolbar (filters/count) rendered above the table inside the card. */
  toolbar?: ReactNode
  /** Optional footer (e.g. Pagination) rendered below the table inside the card. */
  footer?: ReactNode
}

/** Maps a column to its design-system cell class (numeric / actions). */
function cellClass(column: Column<unknown>): string {
  if (column.id === 'actions') return 'actions'
  if (column.align === 'end') return 'num'
  return ''
}

/**
 * Generic, presentational data table bound to the design-system `.table-card` /
 * `table.data` styles. Cells carry `data-label` so the stylesheet can reflow the
 * table into stacked cards on mobile (no horizontal scroll). Holds no
 * data-fetching or domain logic — callers pass resolved rows and a column spec.
 */
export function DataTable<T>({
  columns,
  rows,
  rowKey,
  emptyLabel,
  toolbar,
  footer,
}: DataTableProps<T>) {
  return (
    <div className="table-card">
      {toolbar !== undefined ? <div className="table-toolbar">{toolbar}</div> : null}
      {rows.length === 0 ? (
        <div className="empty-block">{emptyLabel}</div>
      ) : (
        <div className="table-wrap">
          <table className="data">
            <thead>
              <tr>
                {columns.map((column) => (
                  <th key={column.id} scope="col" className={cellClass(column)}>
                    {column.header}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {rows.map((row) => (
                <tr key={rowKey(row)}>
                  {columns.map((column) => {
                    const klass = cellClass(column)
                    return (
                      <td key={column.id} className={klass} data-label={column.header}>
                        {column.id === 'actions' ? (
                          <span className="cell-actions">{column.render(row)}</span>
                        ) : (
                          column.render(row)
                        )}
                      </td>
                    )
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      {footer !== undefined ? footer : null}
    </div>
  )
}
