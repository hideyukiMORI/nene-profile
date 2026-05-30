import { describe, expect, it } from 'vitest'
import type { ImportJobDto, ImportJobErrorListDto } from './api-types'
import { toImportJob, toImportJobErrors } from './model'

const dto: ImportJobDto = {
  id: 7,
  organization_id: 1,
  preset_version_id: 9,
  original_filename: 'bank.csv',
  original_file_hash: 'abc',
  status: 'completed_with_errors',
  row_count: 10,
  error_count: 2,
  started_at: null,
  completed_at: '2026-05-31T00:00:00Z',
  created_at: '2026-05-30T00:00:00Z',
}

describe('import-job mappers', () => {
  it('maps the job DTO to the domain model', () => {
    expect(toImportJob(dto)).toEqual({
      id: 7,
      organizationId: 1,
      presetVersionId: 9,
      originalFilename: 'bank.csv',
      originalFileHash: 'abc',
      status: 'completed_with_errors',
      rowCount: 10,
      errorCount: 2,
      startedAt: null,
      completedAt: '2026-05-31T00:00:00Z',
      createdAt: '2026-05-30T00:00:00Z',
    })
  })

  it('maps the error list, preserving null snippets', () => {
    const list: ImportJobErrorListDto = {
      items: [
        { id: 1, import_job_id: 7, raw_row_number: 3, message: 'bad date', raw_snippet: 'xx' },
        { id: 2, import_job_id: 7, raw_row_number: 5, message: 'bad amount', raw_snippet: null },
      ],
      total: 2,
      limit: 20,
      offset: 0,
    }

    const errors = toImportJobErrors(list)

    expect(errors).toHaveLength(2)
    expect(errors[0]).toEqual({
      id: 1,
      importJobId: 7,
      rawRowNumber: 3,
      message: 'bad date',
      rawSnippet: 'xx',
    })
    expect(errors[1]?.rawSnippet).toBeNull()
  })
})
