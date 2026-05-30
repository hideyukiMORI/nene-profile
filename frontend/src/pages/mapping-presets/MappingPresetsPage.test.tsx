import { screen } from '@testing-library/react'
import { http, HttpResponse } from 'msw'
import { describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/renderWithProviders'
import { MappingPresetsPage } from './MappingPresetsPage'

const preset = {
  id: 3,
  name: 'みずほ',
  bank_label: 'みずほ銀行',
  current_version_id: 9,
  version_number: 2,
  is_deleted: false,
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-30T00:00:00Z',
}

describe('MappingPresetsPage', () => {
  it('renders presets with their current version', async () => {
    server.use(
      http.get('/admin/mapping-presets', () =>
        HttpResponse.json({ items: [preset], total: 1, limit: 20, offset: 0 }),
      ),
    )

    renderWithProviders(<MappingPresetsPage />)

    expect(await screen.findByText('みずほ')).toBeInTheDocument()
    expect(screen.getByText('みずほ銀行')).toBeInTheDocument()
    expect(screen.getByText('v2')).toBeInTheDocument()
  })

  it('shows the empty state when there are no presets', async () => {
    server.use(
      http.get('/admin/mapping-presets', () =>
        HttpResponse.json({ items: [], total: 0, limit: 20, offset: 0 }),
      ),
    )

    renderWithProviders(<MappingPresetsPage />)

    expect(await screen.findByText('プリセットがまだありません。')).toBeInTheDocument()
  })
})
