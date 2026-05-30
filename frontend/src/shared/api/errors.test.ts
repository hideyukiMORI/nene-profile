import { describe, expect, it } from 'vitest'
import { AppError, parseProblemDetails } from './errors'

describe('AppError.isRetryable', () => {
  it('is true for 5xx and 429, false otherwise', () => {
    const make = (status: number): AppError =>
      new AppError({ type: 'about:blank', title: 't', status })

    expect(make(500).isRetryable).toBe(true)
    expect(make(503).isRetryable).toBe(true)
    expect(make(429).isRetryable).toBe(true)
    expect(make(404).isRetryable).toBe(false)
    expect(make(422).isRetryable).toBe(false)
    expect(make(401).isRetryable).toBe(false)
  })
})

describe('parseProblemDetails', () => {
  it('parses an RFC 9457 problem body into an AppError', async () => {
    const response = new Response(
      JSON.stringify({
        type: 'x/validation-failed',
        title: 'Validation Failed',
        status: 422,
        detail: 'bad input',
        errors: [{ field: 'name', message: 'required', code: 'too_small' }],
      }),
      { status: 422, headers: { 'Content-Type': 'application/problem+json' } },
    )

    const error = await parseProblemDetails(response)

    expect(error).toBeInstanceOf(AppError)
    expect(error.status).toBe(422)
    expect(error.type).toBe('x/validation-failed')
    expect(error.title).toBe('Validation Failed')
    expect(error.detail).toBe('bad input')
    expect(error.errors?.[0]?.field).toBe('name')
  })

  it('falls back to a generic AppError when the body is not JSON', async () => {
    const response = new Response('<<not json>>', {
      status: 500,
      statusText: 'Server Error',
    })

    const error = await parseProblemDetails(response)

    expect(error.status).toBe(500)
    expect(error.type).toBe('about:blank')
    expect(error.title).toBe('Server Error')
    expect(error.detail).toBeUndefined()
  })
})
