import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import { useCreateUser } from '@/entities/user'

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(8),
  role: z.enum(['admin', 'member', 'viewer']),
})

type CreateUserFormValues = z.infer<typeof schema>

type CreateUserError = ReturnType<typeof useCreateUser>['error']

interface UseCreateUserForm {
  form: UseFormReturn<CreateUserFormValues>
  submit: (values: CreateUserFormValues) => void
  isSubmitting: boolean
  error: CreateUserError
}

/** Create-user feature hook: RHF + Zod, drives the entity mutation. */
export function useCreateUserForm(onSuccess: () => void): UseCreateUserForm {
  const mutation = useCreateUser()

  const form = useForm<CreateUserFormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: '', password: '', role: 'member' },
  })

  const submit = (values: CreateUserFormValues): void => {
    mutation.mutate(values, {
      onSuccess: () => {
        onSuccess()
      },
    })
  }

  return { form, submit, isSubmitting: mutation.isPending, error: mutation.error }
}
