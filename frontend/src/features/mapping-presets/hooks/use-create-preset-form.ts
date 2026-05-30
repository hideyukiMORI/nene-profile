import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import { useCreateMappingPreset } from '@/entities/mapping-preset'

const ENCODINGS = ['auto', 'utf-8', 'shift_jis'] as const
const DELIMITERS = ['auto', 'comma', 'tab'] as const
const TRANSFORMS = [
  'trim',
  'date_ymd_slash',
  'date_ymd_dash',
  'date_ymd_compact',
  'amount_yen_to_cents',
  'debit_credit_to_signed_cents',
  'single_column_signed_cents',
  'regex_extract',
] as const
const STANDARD_FIELDS = [
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

const schema = z.object({
  name: z.string().min(1),
  bankLabel: z.string().min(1),
  encoding: z.enum(ENCODINGS),
  delimiter: z.enum(DELIMITERS),
  headerRowIndex: z.number().int().min(0),
  yearPivot: z.number().int().min(0).max(99),
  columns: z.array(columnSchema),
})

type CreatePresetFormValues = z.infer<typeof schema>

type CreatePresetError = ReturnType<typeof useCreateMappingPreset>['error']

interface UseCreatePresetForm {
  form: UseFormReturn<CreatePresetFormValues>
  submit: (values: CreatePresetFormValues) => void
  isSubmitting: boolean
  error: CreatePresetError
}

const defaultColumns: CreatePresetFormValues['columns'] = STANDARD_FIELDS.map((field) => ({
  field,
  source: '',
  transform: 'trim',
  optional: false,
}))

/** Create-preset feature hook: RHF + Zod over the full mapping definition. */
export function useCreatePresetForm(onSuccess: () => void): UseCreatePresetForm {
  const mutation = useCreateMappingPreset()

  const form = useForm<CreatePresetFormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: '',
      bankLabel: '',
      encoding: 'auto',
      delimiter: 'auto',
      headerRowIndex: 0,
      yearPivot: 50,
      columns: defaultColumns,
    },
  })

  const submit = (values: CreatePresetFormValues): void => {
    mutation.mutate(values, {
      onSuccess: () => {
        onSuccess()
      },
    })
  }

  return { form, submit, isSubmitting: mutation.isPending, error: mutation.error }
}

export { ENCODINGS, DELIMITERS, TRANSFORMS, STANDARD_FIELDS }
