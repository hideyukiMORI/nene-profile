import type { ReactNode } from 'react'
import { Text } from '@/shared/ui/primitives/Text'

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
}

const alignClass: Record<'start' | 'end', string> = {
  start: 'text-left',
  end: 'text-right',
}

/**
 * Generic, presentational data table. Holds no data-fetching or domain logic —
 * callers pass resolved rows and a column spec. Reused across every resource
 * list screen so table markup and a11y semantics stay consistent.
 */
export function DataTable<T>({ columns, rows, rowKey, emptyLabel }: DataTableProps<T>) {
  if (rows.length === 0) {
    return (
      <Text variant="body" tone="muted">
        {emptyLabel}
      </Text>
    )
  }

  return (
    <table className="w-full border-collapse text-body">
      <thead>
        <tr className="border-b border-border">
          {columns.map((column) => (
            <th
              key={column.id}
              scope="col"
              className={`px-inline-sm py-stack-sm font-semibold text-text-muted ${alignClass[column.align ?? 'start']}`}
            >
              {column.header}
            </th>
          ))}
        </tr>
      </thead>
      <tbody>
        {rows.map((row) => (
          <tr key={rowKey(row)} className="border-b border-border">
            {columns.map((column) => (
              <td
                key={column.id}
                className={`px-inline-sm py-stack-sm text-text-primary ${alignClass[column.align ?? 'start']}`}
              >
                {column.render(row)}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  )
}
