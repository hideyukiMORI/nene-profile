/** Enums mirror the API (terminology.md / ADR 0003). */
export type Encoding = 'auto' | 'utf-8' | 'shift_jis'
export type Delimiter = 'auto' | 'comma' | 'tab'
export type Transform =
  | 'trim'
  | 'date_ymd_slash'
  | 'date_ymd_dash'
  | 'date_ymd_compact'
  | 'amount_yen_to_cents'
  | 'debit_credit_to_signed_cents'
  | 'single_column_signed_cents'
  | 'regex_extract'

/** StandardTransaction target fields a source column maps to. */
export type StandardField =
  | 'transaction_date'
  | 'value_date'
  | 'amount'
  | 'description'
  | 'counterparty'
  | 'balance'

export interface MappingColumnDto {
  source: string | string[]
  transform: Transform
  optional?: boolean
}

export interface MappingDefinitionDto {
  encoding: Encoding
  delimiter: Delimiter
  header_row_index: number
  year_pivot?: number
  columns: Record<string, MappingColumnDto>
  skip_rows_matching?: string[]
  line_identity?: string[]
}

export interface MappingPresetDto {
  id: number
  name: string
  bank_label: string
  current_version_id: number
  version_number: number
  definition?: MappingDefinitionDto
  is_deleted: boolean
  created_at: string
  updated_at: string
}

export interface MappingPresetListDto {
  items: MappingPresetDto[]
  total: number
  limit: number
  offset: number
}

export interface CreateMappingPresetDto {
  name: string
  bank_label: string
  definition: MappingDefinitionDto
}
