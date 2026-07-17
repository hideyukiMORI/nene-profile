import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import { useUpdateUser, type User } from '@/entities/user'

// Password is optional; when present it must be at least 8 chars. An empty
// string means "leave unchanged" and is stripped before the request.
const schema = z.object({
  role: z.enum(['superadmin', 'admin', 'member', 'viewer']),
  status: z.enum(['active', 'invited']),
  password: z.union([z.literal(''), z.string().min(8)]),
})

type EditUserFormValues = z.infer<typeof schema>

type EditUserError = ReturnType<typeof useUpdateUser>['error']

interface UseEditUserForm {
  form: UseFormReturn<EditUserFormValues>
  submit: (values: EditUserFormValues) => void
  isSubmitting: boolean
  error: EditUserError
}

/** Edit-user feature hook: pre-fills from the user, drives the PATCH mutation. */
export function useEditUserForm(user: User, onSuccess: () => void): UseEditUserForm {
  const mutation = useUpdateUser()

  const form = useForm<EditUserFormValues>({
    resolver: zodResolver(schema),
    defaultValues: { role: user.role, status: user.status, password: '' },
  })

  const submit = (values: EditUserFormValues): void => {
    mutation.mutate(
      { id: user.id, role: values.role, status: values.status, password: values.password },
      {
        onSuccess: () => {
          onSuccess()
        },
      },
    )
  }

  return { form, submit, isSubmitting: mutation.isPending, error: mutation.error }
}
