import { http, HttpResponse } from 'msw'
import { afterEach, describe, expect, it } from 'vitest'
import { server } from '@tests/msw/server'
import { apiClient, tokenStore } from './client'

afterEach(() => {
  tokenStore.clearToken()
})

describe('apiClient (nene2-client transport adapter)', () => {
  it('mirrors the bearer token onto both Authorization and X-Authorization on a JSON GET', async () => {
    tokenStore.setToken('jwt-token')
    let seen: { authorization: string | null; xAuthorization: string | null } | undefined

    server.use(
      http.get('/admin/users', ({ request }) => {
        seen = {
          authorization: request.headers.get('Authorization'),
          xAuthorization: request.headers.get('X-Authorization'),
        }
        return HttpResponse.json({ items: [], total: 0, limit: 20, offset: 0 })
      }),
    )

    await apiClient.get('/admin/users')

    expect(seen).toEqual({
      authorization: 'Bearer jwt-token',
      xAuthorization: 'Bearer jwt-token',
    })
  })

  it('mirrors the bearer token onto both Authorization and X-Authorization on a multipart upload', async () => {
    tokenStore.setToken('jwt-token')
    let seen: { authorization: string | null; xAuthorization: string | null } | undefined

    server.use(
      http.post('/admin/import-jobs', ({ request }) => {
        seen = {
          authorization: request.headers.get('Authorization'),
          xAuthorization: request.headers.get('X-Authorization'),
        }
        return HttpResponse.json({ id: 1 })
      }),
    )

    const formData = new FormData()
    formData.append('file', new Blob(['a,b'], { type: 'text/csv' }), 'a.csv')
    await apiClient.upload('/admin/import-jobs', formData)

    expect(seen).toEqual({
      authorization: 'Bearer jwt-token',
      xAuthorization: 'Bearer jwt-token',
    })
  })

  it('maps a Problem Details error response onto AppError', async () => {
    tokenStore.setToken('jwt-token')
    server.use(
      http.get('/admin/organizations', () =>
        HttpResponse.json(
          { type: 'about:blank', title: 'Not Found', status: 404 },
          { status: 404 },
        ),
      ),
    )

    await expect(apiClient.get('/admin/organizations')).rejects.toMatchObject({
      name: 'AppError',
      status: 404,
      title: 'Not Found',
    })
  })
})
