import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import { useUpdateOrganization, type Organization } from '@/entities/organization'

const schema = z.object({
  name: z.string().min(1),
  slug: z.string().regex(/^[a-z0-9-]+$/, 'slug_format'),
  isActive: z.boolean(),
  customDomain: z.string(),
})

type EditOrgValues = z.infer<typeof schema>

interface UseEditOrganizationForm {
  form: UseFormReturn<EditOrgValues>
  submit: (values: EditOrgValues) => void
  isSubmitting: boolean
  isSaved: boolean
  isSlugConflict: boolean
  isGenericError: boolean
}

export function useEditOrganizationForm(
  org: Organization,
  onSaved: () => void,
): UseEditOrganizationForm {
  const mutation = useUpdateOrganization()

  const form = useForm<EditOrgValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      name: org.name,
      slug: org.slug,
      isActive: org.isActive,
      customDomain: org.customDomain ?? '',
    },
  })

  const submit = (values: EditOrgValues): void => {
    mutation.mutate(
      {
        id: org.id,
        name: values.name,
        slug: values.slug,
        isActive: values.isActive,
        customDomain: values.customDomain !== '' ? values.customDomain : null,
      },
      { onSuccess: onSaved },
    )
  }

  const errorType = mutation.error?.type ?? null
  const isSlugConflict = errorType === 'slug-conflict'
  const isGenericError = mutation.isError && !isSlugConflict

  return {
    form,
    submit,
    isSubmitting: mutation.isPending,
    isSaved: mutation.isSuccess,
    isSlugConflict,
    isGenericError,
  }
}
