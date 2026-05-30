import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { useUpdateMappingPreset, type MappingPresetDetail } from '@/entities/mapping-preset'
import { presetSchema, type PresetFormValues } from './preset-schema'

type EditPresetError = ReturnType<typeof useUpdateMappingPreset>['error']

interface UseEditPresetForm {
  form: UseFormReturn<PresetFormValues>
  submit: (values: PresetFormValues) => void
  isSubmitting: boolean
  error: EditPresetError
}

/** Edit-preset feature hook: pre-fills from a loaded preset, drives the PATCH. */
export function useEditPresetForm(
  preset: MappingPresetDetail,
  onSuccess: () => void,
): UseEditPresetForm {
  const mutation = useUpdateMappingPreset()

  const form = useForm<PresetFormValues>({
    resolver: zodResolver(presetSchema),
    defaultValues: {
      name: preset.name,
      bankLabel: preset.bankLabel,
      encoding: preset.encoding,
      delimiter: preset.delimiter,
      headerRowIndex: preset.headerRowIndex,
      yearPivot: preset.yearPivot,
      columns: preset.columns,
    },
  })

  const submit = (values: PresetFormValues): void => {
    mutation.mutate(
      { id: preset.id, ...values },
      {
        onSuccess: () => {
          onSuccess()
        },
      },
    )
  }

  return { form, submit, isSubmitting: mutation.isPending, error: mutation.error }
}
