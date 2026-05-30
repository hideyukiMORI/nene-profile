import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { ImportJobDto } from './api-types'
import { importJobKeys } from './queries'
import { toImportJob, type CreateImportJobInput, type ImportJob } from './model'

/** Upload a CSV to start an import job (multipart). Invalidates the list. */
export function useCreateImportJob(): UseMutationResult<ImportJob, AppError, CreateImportJobInput> {
  const queryClient = useQueryClient()
  return useMutation<ImportJob, AppError, CreateImportJobInput>({
    mutationFn: async (input) => {
      const formData = new FormData()
      formData.append('file', input.file)
      formData.append('preset_id', String(input.presetId))
      const dto = await apiClient.upload<ImportJobDto>('/admin/import-jobs', formData)
      return toImportJob(dto)
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: importJobKeys.lists() })
    },
  })
}

/** Trigger an authenticated download of a job's normalized export. */
export function exportImportJob(id: number, format: 'json' | 'csv'): Promise<void> {
  return apiClient.download(
    `/admin/import-jobs/${String(id)}/export.${format}`,
    `import-job-${String(id)}.${format}`,
  )
}
