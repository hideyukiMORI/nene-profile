import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import { useCreateOrganization } from '@/entities/organization'

const schema = z.object({
  name: z.string().min(1),
  // min(1) → "required"; failing the pattern → "invalid" (distinguished in the UI
  // via the error's `type`, keeping all copy in the typed i18n catalog).
  slug: z
    .string()
    .min(1)
    .regex(/^[a-z0-9-]+$/),
  customDomain: z.string(),
})

type CreateOrganizationFormValues = z.infer<typeof schema>

/** Error type comes from the entity mutation — features never import shared/api. */
type CreateOrganizationError = ReturnType<typeof useCreateOrganization>['error']

interface UseCreateOrganizationForm {
  form: UseFormReturn<CreateOrganizationFormValues>
  submit: (values: CreateOrganizationFormValues) => void
  isSubmitting: boolean
  error: CreateOrganizationError
}

/**
 * Create-organization feature hook: owns form state (RHF + Zod), drives the
 * entity mutation, and notifies the caller on success so it can close/refresh.
 */
export function useCreateOrganizationForm(onSuccess: () => void): UseCreateOrganizationForm {
  const mutation = useCreateOrganization()

  const form = useForm<CreateOrganizationFormValues>({
    resolver: zodResolver(schema),
    defaultValues: { name: '', slug: '', customDomain: '' },
  })

  const submit = (values: CreateOrganizationFormValues): void => {
    mutation.mutate(
      { name: values.name, slug: values.slug, customDomain: values.customDomain },
      {
        onSuccess: () => {
          onSuccess()
        },
      },
    )
  }

  return {
    form,
    submit,
    isSubmitting: mutation.isPending,
    error: mutation.error,
  }
}
