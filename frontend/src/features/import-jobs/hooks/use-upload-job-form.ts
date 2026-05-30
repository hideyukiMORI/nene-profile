import { useEffect, useState } from 'react'
import { useCreateImportJob, type ImportJob } from '@/entities/import-job'
import { useMappingPresets, type MappingPreset } from '@/entities/mapping-preset'

interface UseUploadJobForm {
  presets: readonly MappingPreset[]
  file: File | null
  setFile: (file: File | null) => void
  presetId: number | null
  setPresetId: (id: number | null) => void
  submit: () => void
  isSubmitting: boolean
  error: ReturnType<typeof useCreateImportJob>['error']
  showFileError: boolean
  showPresetError: boolean
}

/**
 * Upload-job feature hook: loads presets for the selector, owns the file +
 * preset selection, and drives the multipart upload mutation.
 */
export function useUploadJobForm(onUploaded: (job: ImportJob) => void): UseUploadJobForm {
  const presetsQuery = useMappingPresets({ limit: 100, offset: 0 })
  const mutation = useCreateImportJob()
  const [file, setFile] = useState<File | null>(null)
  const [presetId, setPresetId] = useState<number | null>(null)
  const [submitted, setSubmitted] = useState(false)

  const presets = presetsQuery.data?.items ?? []
  const firstPresetId = presets[0]?.id ?? null

  // Default to the first preset once the list resolves.
  useEffect(() => {
    if (presetId === null && firstPresetId !== null) {
      setPresetId(firstPresetId)
    }
  }, [presetId, firstPresetId])

  const submit = (): void => {
    setSubmitted(true)
    if (file === null || presetId === null) return
    mutation.mutate(
      { file, presetId },
      {
        onSuccess: (job) => {
          onUploaded(job)
        },
      },
    )
  }

  return {
    presets,
    file,
    setFile,
    presetId,
    setPresetId,
    submit,
    isSubmitting: mutation.isPending,
    error: mutation.error,
    showFileError: submitted && file === null,
    showPresetError: submitted && presetId === null,
  }
}
