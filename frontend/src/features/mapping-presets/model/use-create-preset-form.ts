import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { useCreateMappingPreset } from '@/entities/mapping-preset'
import { defaultPresetValues, presetSchema, type PresetFormValues } from './preset-schema'

type CreatePresetError = ReturnType<typeof useCreateMappingPreset>['error']

interface UseCreatePresetForm {
  form: UseFormReturn<PresetFormValues>
  submit: (values: PresetFormValues) => void
  isSubmitting: boolean
  error: CreatePresetError
}

/** Create-preset feature hook: RHF + Zod over the full mapping definition. */
export function useCreatePresetForm(onSuccess: () => void): UseCreatePresetForm {
  const mutation = useCreateMappingPreset()

  const form = useForm<PresetFormValues>({
    resolver: zodResolver(presetSchema),
    defaultValues: defaultPresetValues,
  })

  const submit = (values: PresetFormValues): void => {
    mutation.mutate(values, {
      onSuccess: () => {
        onSuccess()
      },
    })
  }

  return { form, submit, isSubmitting: mutation.isPending, error: mutation.error }
}
