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

export interface PageParams {
  limit: number
  offset: number
}

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
