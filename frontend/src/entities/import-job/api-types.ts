/** Status mirrors the API (terminology.md / ADR 0003). */
export type ImportJobStatus =
  | 'pending'
  | 'running'
  | 'completed'
  | 'completed_with_errors'
  | 'failed'

export interface ImportJobDto {
  id: number
  organization_id: number
  preset_version_id: number
  original_filename: string
  original_file_hash: string
  status: ImportJobStatus
  row_count: number
  error_count: number
  started_at: string | null
  completed_at: string | null
  created_at: string
}

export interface ImportJobListDto {
  items: ImportJobDto[]
  total: number
  limit: number
  offset: number
}

export interface ImportJobErrorDto {
  id: number
  import_job_id: number
  raw_row_number: number
  message: string
  raw_snippet: string | null
}

export interface ImportJobErrorListDto {
  items: ImportJobErrorDto[]
  total: number
  limit: number
  offset: number
}
