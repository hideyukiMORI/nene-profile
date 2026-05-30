import { describe, expect, it } from 'vitest'
import type { MappingPresetDto } from './api-types'
import {
  toCreateMappingPresetDto,
  toMappingPreset,
  toMappingPresetDetail,
  type CreateMappingPresetInput,
} from './model'

const baseDto: MappingPresetDto = {
  id: 3,
  name: 'みずほ',
  bank_label: 'みずほ銀行',
  current_version_id: 9,
  version_number: 2,
  is_deleted: false,
  created_at: '2026-05-30T00:00:00Z',
  updated_at: '2026-05-31T00:00:00Z',
}

describe('mapping-preset list mapper', () => {
  it('maps list fields without parsing the definition', () => {
    expect(toMappingPreset(baseDto)).toEqual({
      id: 3,
      name: 'みずほ',
      bankLabel: 'みずほ銀行',
      currentVersionId: 9,
      versionNumber: 2,
      isDeleted: false,
      createdAt: '2026-05-30T00:00:00Z',
      updatedAt: '2026-05-31T00:00:00Z',
    })
  })
})

describe('toMappingPresetDetail', () => {
  it('parses the definition into ordered editable columns', () => {
    const detail = toMappingPresetDetail({
      ...baseDto,
      definition: {
        encoding: 'shift_jis',
        delimiter: 'comma',
        header_row_index: 1,
        year_pivot: 60,
        columns: {
          // array source -> first element; explicit optional preserved
          transaction_date: {
            source: ['日付', '予備'],
            transform: 'date_ymd_slash',
            optional: true,
          },
          amount: { source: '金額', transform: 'amount_yen_to_cents' },
        },
      },
    })

    expect(detail.encoding).toBe('shift_jis')
    expect(detail.delimiter).toBe('comma')
    expect(detail.headerRowIndex).toBe(1)
    expect(detail.yearPivot).toBe(60)

    // Always 6 rows in canonical field order.
    expect(detail.columns.map((c) => c.field)).toEqual([
      'transaction_date',
      'value_date',
      'amount',
      'description',
      'counterparty',
      'balance',
    ])

    const txn = detail.columns[0]
    expect(txn).toEqual({
      field: 'transaction_date',
      source: '日付',
      transform: 'date_ymd_slash',
      optional: true,
    })

    // Unmapped fields fall back to blank defaults.
    expect(detail.columns[1]).toEqual({
      field: 'value_date',
      source: '',
      transform: 'trim',
      optional: false,
    })
  })

  it('falls back to defaults when the definition is absent', () => {
    const detail = toMappingPresetDetail(baseDto)

    expect(detail.encoding).toBe('auto')
    expect(detail.delimiter).toBe('auto')
    expect(detail.headerRowIndex).toBe(0)
    expect(detail.yearPivot).toBe(50)
    expect(detail.columns.every((c) => c.source === '')).toBe(true)
  })
})

describe('toCreateMappingPresetDto', () => {
  const input: CreateMappingPresetInput = {
    name: 'みずほ',
    bankLabel: 'みずほ銀行',
    encoding: 'auto',
    delimiter: 'auto',
    headerRowIndex: 0,
    yearPivot: 50,
    columns: [
      { field: 'transaction_date', source: ' 日付 ', transform: 'date_ymd_slash', optional: false },
      { field: 'amount', source: '金額', transform: 'amount_yen_to_cents', optional: true },
      { field: 'description', source: '', transform: 'trim', optional: false },
    ],
  }

  it('excludes blank-source columns, trims source, and keeps optional only when true', () => {
    const dto = toCreateMappingPresetDto(input)

    expect(dto.name).toBe('みずほ')
    expect(dto.bank_label).toBe('みずほ銀行')
    expect(dto.definition.columns).toEqual({
      transaction_date: { source: '日付', transform: 'date_ymd_slash' },
      amount: { source: '金額', transform: 'amount_yen_to_cents', optional: true },
    })
    expect(dto.definition.columns).not.toHaveProperty('description')
  })
})
