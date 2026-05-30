import type {
  ImportJobDto,
  ImportJobErrorDto,
  ImportJobErrorListDto,
  ImportJobListDto,
  ImportJobStatus,
} from './api-types'

export interface ImportJob {
  id: number
  organizationId: number
  presetVersionId: number
  originalFilename: string
  originalFileHash: string
  status: ImportJobStatus
  rowCount: number
  errorCount: number
  startedAt: string | null
  completedAt: string | null
  createdAt: string
}

export interface ImportJobList {
  items: ImportJob[]
  total: number
  limit: number
  offset: number
}

export interface ImportJobError {
  id: number
  importJobId: number
  rawRowNumber: number
  message: string
  rawSnippet: string | null
}

export interface CreateImportJobInput {
  file: File
  presetId: number
}

export interface PageParams {
  limit: number
  offset: number
}

export function toImportJob(dto: ImportJobDto): ImportJob {
  return {
    id: dto.id,
    organizationId: dto.organization_id,
    presetVersionId: dto.preset_version_id,
    originalFilename: dto.original_filename,
    originalFileHash: dto.original_file_hash,
    status: dto.status,
    rowCount: dto.row_count,
    errorCount: dto.error_count,
    startedAt: dto.started_at,
    completedAt: dto.completed_at,
    createdAt: dto.created_at,
  }
}

export function toImportJobList(dto: ImportJobListDto): ImportJobList {
  return {
    items: dto.items.map(toImportJob),
    total: dto.total,
    limit: dto.limit,
    offset: dto.offset,
  }
}

function toImportJobError(dto: ImportJobErrorDto): ImportJobError {
  return {
    id: dto.id,
    importJobId: dto.import_job_id,
    rawRowNumber: dto.raw_row_number,
    message: dto.message,
    rawSnippet: dto.raw_snippet,
  }
}

export function toImportJobErrors(dto: ImportJobErrorListDto): ImportJobError[] {
  return dto.items.map(toImportJobError)
}
