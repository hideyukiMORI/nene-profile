import { render, screen, within } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { DataTable, type Column } from './DataTable'

interface Row {
  id: number
  name: string
}

const columns: readonly Column<Row>[] = [
  { id: 'name', header: 'Name', render: (r) => r.name },
  { id: 'id', header: 'ID', align: 'end', render: (r) => r.id },
]

describe('DataTable', () => {
  it('renders the empty label when there are no rows', () => {
    render(<DataTable columns={columns} rows={[]} rowKey={(r) => r.id} emptyLabel="No data" />)

    expect(screen.getByText('No data')).toBeInTheDocument()
    expect(screen.queryByRole('table')).not.toBeInTheDocument()
  })

  it('renders headers and a row per item', () => {
    render(
      <DataTable
        columns={columns}
        rows={[
          { id: 1, name: 'Alice' },
          { id: 2, name: 'Bob' },
        ]}
        rowKey={(r) => r.id}
        emptyLabel="No data"
      />,
    )

    expect(screen.getByRole('columnheader', { name: 'Name' })).toBeInTheDocument()
    const rows = screen.getAllByRole('row')
    // header + 2 body rows
    expect(rows).toHaveLength(3)
    expect(within(rows[1] as HTMLElement).getByText('Alice')).toBeInTheDocument()
    expect(within(rows[2] as HTMLElement).getByText('Bob')).toBeInTheDocument()
  })
})
