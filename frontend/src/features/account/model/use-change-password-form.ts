import { zodResolver } from '@hookform/resolvers/zod'
import { useForm, type UseFormReturn } from 'react-hook-form'
import { z } from 'zod'
import { useChangeOwnPassword } from '@/entities/auth'

const schema = z
  .object({
    currentPassword: z.string().min(1),
    newPassword: z.string().min(8),
    confirmPassword: z.string().min(1),
  })
  .refine((data) => data.newPassword === data.confirmPassword, {
    path: ['confirmPassword'],
    message: 'mismatch',
  })

type ChangePasswordValues = z.infer<typeof schema>

interface UseChangePasswordForm {
  form: UseFormReturn<ChangePasswordValues>
  submit: (values: ChangePasswordValues) => void
  isSubmitting: boolean
  isSuccess: boolean
  isInvalidCurrentPassword: boolean
  isGenericError: boolean
}

export function useChangePasswordForm(): UseChangePasswordForm {
  const mutation = useChangeOwnPassword()

  const form = useForm<ChangePasswordValues>({
    resolver: zodResolver(schema),
    defaultValues: { currentPassword: '', newPassword: '', confirmPassword: '' },
  })

  const submit = (values: ChangePasswordValues): void => {
    mutation.mutate(
      { current_password: values.currentPassword, new_password: values.newPassword },
      {
        onSuccess: () => {
          form.reset()
        },
      },
    )
  }

  const errorType = mutation.error?.type ?? null
  const isInvalidCurrentPassword = errorType === 'invalid-current-password'
  const isGenericError = mutation.isError && !isInvalidCurrentPassword

  return {
    form,
    submit,
    isSubmitting: mutation.isPending,
    isSuccess: mutation.isSuccess,
    isInvalidCurrentPassword,
    isGenericError,
  }
}
