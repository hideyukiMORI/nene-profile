export type Encoding = 'auto' | 'utf-8' | 'shift_jis'

export interface OrganizationSettingsDto {
  organization_id: number
  default_encoding: Encoding
  max_file_size_bytes: number
  clear_bearer_token_set: boolean
}

export interface UpdateOrganizationSettingsDto {
  default_encoding?: Encoding
  max_file_size_bytes?: number
  clear_bearer_token?: string | null
}
