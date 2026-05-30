import { z } from 'zod'

export const ENCODINGS = ['auto', 'utf-8', 'shift_jis'] as const
export const DELIMITERS = ['auto', 'comma', 'tab'] as const
export const TRANSFORMS = [
  'trim',
  'date_ymd_slash',
  'date_ymd_dash',
  'date_ymd_compact',
  'amount_yen_to_cents',
  'debit_credit_to_signed_cents',
  'single_column_signed_cents',
  'regex_extract',
] as const
export const STANDARD_FIELDS = [
  'transaction_date',
  'value_date',
  'amount',
  'description',
  'counterparty',
  'balance',
] as const

const columnSchema = z.object({
  field: z.enum(STANDARD_FIELDS),
  source: z.string(),
  transform: z.enum(TRANSFORMS),
  optional: z.boolean(),
})

/** Shared schema for the create and edit preset forms (same definition shape). */
export const presetSchema = z.object({
  name: z.string().min(1),
  bankLabel: z.string().min(1),
  encoding: z.enum(ENCODINGS),
  delimiter: z.enum(DELIMITERS),
  headerRowIndex: z.number().int().min(0),
  yearPivot: z.number().int().min(0).max(99),
  columns: z.array(columnSchema),
})

export type PresetFormValues = z.infer<typeof presetSchema>

export const defaultPresetValues: PresetFormValues = {
  name: '',
  bankLabel: '',
  encoding: 'auto',
  delimiter: 'auto',
  headerRowIndex: 0,
  yearPivot: 50,
  columns: STANDARD_FIELDS.map((field) => ({
    field,
    source: '',
    transform: 'trim',
    optional: false,
  })),
}
