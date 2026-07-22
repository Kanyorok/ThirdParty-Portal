import NextAuth from 'next-auth'
import type { NextRequest } from 'next/server'

import { authOptions } from '@/lib/auth-options'

type AuthRouteContext = {
  params: Promise<{ nextauth: string[] }>
}

type AuthRouteHandler = (request: NextRequest, context: AuthRouteContext) => Promise<Response>

const nextAuthHandler = NextAuth(authOptions) as unknown as AuthRouteHandler

function stripSensitiveSessionFields(body: unknown): unknown {
  if (!body || typeof body !== 'object' || Array.isArray(body)) return body

  const {
    accessToken: _accessToken,
    tokenType: _tokenType,
    access_token: _accessTokenSnake,
    ...safe
  } = body as Record<string, unknown>

  return safe
}

export async function GET(request: NextRequest, context: AuthRouteContext) {
  const response = await nextAuthHandler(request, context)

  if (!request.nextUrl.pathname.endsWith('/session') || !response.ok) {
    return response
  }

  try {
    const session = await response.clone().json()
    const headers = new Headers(response.headers)
    headers.set('Content-Type', 'application/json')

    return new Response(JSON.stringify(stripSensitiveSessionFields(session)), {
      status: response.status,
      statusText: response.statusText,
      headers,
    })
  } catch {
    return response
  }
}

export async function POST(request: NextRequest, context: AuthRouteContext) {
  return nextAuthHandler(request, context)
}
