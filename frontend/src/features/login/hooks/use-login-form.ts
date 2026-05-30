import { zodResolver } from '@hookform/resolvers/zod'
import { useNavigate } from 'react-router-dom'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import { useLogin } from '@/entities/auth'

const schema = z.object({
  email: z.string().min(1),
  password: z.string().min(1),
})

type LoginFormValues = z.infer<typeof schema>

/** Error type comes from the entity mutation — features never import shared/api. */
type LoginError = ReturnType<typeof useLogin>['error']

interface UseLoginForm {
  form: UseFormReturn<LoginFormValues>
  submit: (values: LoginFormValues) => void
  isSubmitting: boolean
  error: LoginError
}

/**
 * Login feature hook: owns form state (RHF + Zod), drives the auth mutation,
 * and navigates to the post-login destination. UI is prop-driven and stateless.
 */
export function useLoginForm(): UseLoginForm {
  const navigate = useNavigate()
  const mutation = useLogin()

  const form = useForm<LoginFormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: '', password: '' },
  })

  const submit = (values: LoginFormValues): void => {
    mutation.mutate(values, {
      onSuccess: () => {
        void navigate('/', { replace: true })
      },
    })
  }

  return {
    form,
    submit,
    isSubmitting: mutation.isPending,
    error: mutation.error,
  }
}
