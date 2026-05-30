import type {
  CreateMappingPresetDto,
  Delimiter,
  Encoding,
  MappingColumnDto,
  MappingPresetDto,
  MappingPresetListDto,
  StandardField,
  Transform,
} from './api-types'

/** Domain model (camelCase). The definition is not parsed for the list view. */
export interface MappingPreset {
  id: number
  name: string
  bankLabel: string
  currentVersionId: number
  versionNumber: number
  isDeleted: boolean
  createdAt: string
  updatedAt: string
}

export interface MappingPresetList {
  items: MappingPreset[]
  total: number
  limit: number
  offset: number
}

/** One row of the definition editor: a standard field ← source column + transform. */
export interface ColumnMappingInput {
  field: StandardField
  source: string
  transform: Transform
  optional: boolean
}

export interface CreateMappingPresetInput {
  name: string
  bankLabel: string
  encoding: Encoding
  delimiter: Delimiter
  headerRowIndex: number
  yearPivot: number
  columns: readonly ColumnMappingInput[]
}

export interface UpdateMappingPresetInput extends CreateMappingPresetInput {
  id: number
}

/** Full preset with the definition parsed into editable form values. */
export interface MappingPresetDetail extends MappingPreset {
  encoding: Encoding
  delimiter: Delimiter
  headerRowIndex: number
  yearPivot: number
  columns: ColumnMappingInput[]
}

export interface PageParams {
  limit: number
  offset: number
}

/** StandardTransaction fields, in display order (mirrors the API column keys). */
const STANDARD_FIELD_ORDER: readonly StandardField[] = [
  'transaction_date',
  'value_date',
  'amount',
  'description',
  'counterparty',
  'balance',
]

export function toMappingPreset(dto: MappingPresetDto): MappingPreset {
  return {
    id: dto.id,
    name: dto.name,
    bankLabel: dto.bank_label,
    currentVersionId: dto.current_version_id,
    versionNumber: dto.version_number,
    isDeleted: dto.is_deleted,
    createdAt: dto.created_at,
    updatedAt: dto.updated_at,
  }
}

export function toMappingPresetList(dto: MappingPresetListDto): MappingPresetList {
  return {
    items: dto.items.map(toMappingPreset),
    total: dto.total,
    limit: dto.limit,
    offset: dto.offset,
  }
}

function columnSource(source: MappingColumnDto['source']): string {
  return Array.isArray(source) ? (source[0] ?? '') : source
}

export function toMappingPresetDetail(dto: MappingPresetDto): MappingPresetDetail {
  const def = dto.definition
  const defColumns = def?.columns ?? {}
  const columns: ColumnMappingInput[] = STANDARD_FIELD_ORDER.map((field) => {
    const column = defColumns[field]
    return column !== undefined
      ? {
          field,
          source: columnSource(column.source),
          transform: column.transform,
          optional: column.optional ?? false,
        }
      : { field, source: '', transform: 'trim', optional: false }
  })

  return {
    ...toMappingPreset(dto),
    encoding: def?.encoding ?? 'auto',
    delimiter: def?.delimiter ?? 'auto',
    headerRowIndex: def?.header_row_index ?? 0,
    yearPivot: def?.year_pivot ?? 50,
    columns,
  }
}

export function toCreateMappingPresetDto(input: CreateMappingPresetInput): CreateMappingPresetDto {
  // Only mapped fields (non-empty source) become columns; the rest are excluded.
  const columns: Record<string, MappingColumnDto> = {}
  for (const column of input.columns) {
    if (column.source.trim() === '') continue
    columns[column.field] = {
      source: column.source.trim(),
      transform: column.transform,
      ...(column.optional ? { optional: true } : {}),
    }
  }

  return {
    name: input.name,
    bank_label: input.bankLabel,
    definition: {
      encoding: input.encoding,
      delimiter: input.delimiter,
      header_row_index: input.headerRowIndex,
      year_pivot: input.yearPivot,
      columns,
    },
  }
}
