interface ValidationFieldError {
  field: string
  message: string
  code: string
}

interface ProblemDetails {
  type: string
  title: string
  status: number
  detail?: string
  instance?: string
  errors?: readonly ValidationFieldError[]
}

/**
 * Single typed error for all API failures. Carries the RFC 9457 Problem Details
 * fields so features can branch on `status` / `type` and surface field errors.
 */
export class AppError extends Error {
  readonly status: number
  readonly type: string
  readonly title: string
  readonly detail?: string
  readonly instance?: string
  readonly errors?: readonly ValidationFieldError[]

  constructor(problem: ProblemDetails) {
    super(problem.title)
    this.name = 'AppError'
    this.status = problem.status
    this.type = problem.type
    this.title = problem.title
    if (problem.detail !== undefined) this.detail = problem.detail
    if (problem.instance !== undefined) this.instance = problem.instance
    if (problem.errors !== undefined) this.errors = problem.errors
  }

  get isRetryable(): boolean {
    return this.status >= 500 || this.status === 429
  }
}

/** Parse a non-OK Response (application/problem+json) into an AppError. */
export async function parseProblemDetails(response: Response): Promise<AppError> {
  try {
    const body = (await response.json()) as ProblemDetails
    return new AppError({
      type: body.type,
      title: body.title,
      status: body.status,
      ...(body.detail !== undefined ? { detail: body.detail } : {}),
      ...(body.instance !== undefined ? { instance: body.instance } : {}),
      ...(body.errors !== undefined ? { errors: body.errors } : {}),
    })
  } catch {
    return new AppError({
      type: 'about:blank',
      title: response.statusText || 'Request failed',
      status: response.status,
    })
  }
}
