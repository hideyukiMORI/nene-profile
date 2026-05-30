import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import {
  useUpdateOrganizationSettings,
  type OrganizationSettings,
} from '@/entities/organization-settings'

const ENCODINGS = ['auto', 'utf-8', 'shift_jis'] as const

const schema = z.object({
  defaultEncoding: z.enum(ENCODINGS),
  maxFileSizeBytes: z.number().int().min(1),
  clearBearerToken: z.string(),
})

type SettingsFormValues = z.infer<typeof schema>

type SettingsError = ReturnType<typeof useUpdateOrganizationSettings>['error']

interface UseSettingsForm {
  form: UseFormReturn<SettingsFormValues>
  submit: (values: SettingsFormValues) => void
  isSubmitting: boolean
  isSaved: boolean
  error: SettingsError
}

/** Settings feature hook: pre-fills from the loaded settings, drives the PATCH. */
export function useSettingsForm(settings: OrganizationSettings): UseSettingsForm {
  const mutation = useUpdateOrganizationSettings()

  const form = useForm<SettingsFormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      defaultEncoding: settings.defaultEncoding,
      maxFileSizeBytes: settings.maxFileSizeBytes,
      clearBearerToken: '',
    },
  })

  const submit = (values: SettingsFormValues): void => {
    mutation.mutate({
      defaultEncoding: values.defaultEncoding,
      maxFileSizeBytes: values.maxFileSizeBytes,
      clearBearerToken: values.clearBearerToken,
    })
  }

  return {
    form,
    submit,
    isSubmitting: mutation.isPending,
    isSaved: mutation.isSuccess,
    error: mutation.error,
  }
}

export { ENCODINGS }
