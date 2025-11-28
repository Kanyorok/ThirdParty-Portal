import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"
import { getToken } from "next-auth/jwt"

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || ""
const AUTH_SIGN_IN_PATH = "/signin"

const tokenValidationCache = new Map<string, { valid: boolean; timestamp: number }>()
const CACHE_DURATION = 5 * 60 * 1000

const PUBLIC_API_ROUTES_PREFIXES = [
  "/api/v1/countries",
  "/api/third-party-details",
  "/api/currencies",
  "/api/third-party-auth",
]

async function validateTokenWithBackend(accessToken: string): Promise<boolean> {
  const cached = tokenValidationCache.get(accessToken)
  if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
    return cached.valid
  }

  try {
    if (!EXTERNAL_API_BASE) return true
    const response = await fetch(`${EXTERNAL_API_BASE}/api/auth/validate-token`, {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${accessToken}`,
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      cache: "no-store",
    })

    const isValid = response.ok

    tokenValidationCache.set(accessToken, {
      valid: isValid,
      timestamp: Date.now(),
    })

    if (tokenValidationCache.size > 100) {
      const cutoff = Date.now() - CACHE_DURATION
      for (const [key, value] of tokenValidationCache.entries()) {
        if (value.timestamp < cutoff) {
          tokenValidationCache.delete(key)
        }
      }
    }

    return isValid
  } catch (error) {
    const msg = error instanceof Error ? error.message : String(error)
    console.warn("Backend token validation unavailable, skipping validation:", msg)
    tokenValidationCache.set(accessToken, {
      valid: true,
      timestamp: Date.now(),
    })
    return true
  }
}

export async function proxy(req: NextRequest) {
  const { pathname } = req.nextUrl

  if (pathname.startsWith("/api/auth")) {
    return NextResponse.next()
  }

  if (PUBLIC_API_ROUTES_PREFIXES.some(route => pathname.startsWith(route))) {
    return NextResponse.next()
  }

  const token = await getToken({ req, secret: process.env.NEXTAUTH_SECRET })
  let isAuth = !!token
  const isDashboard = pathname.startsWith("/dashboard")
  const isApiRoute = pathname.startsWith("/api")

  if (isAuth && token?.accessToken && (isDashboard || isApiRoute)) {
    const isValidToken = await validateTokenWithBackend(token.accessToken as string)

    if (!isValidToken) {
      console.log("Invalid token detected, clearing session")
      isAuth = false

      if (isDashboard) {
        const url = req.nextUrl.clone()
        url.pathname = AUTH_SIGN_IN_PATH
        url.searchParams.set("callbackUrl", req.nextUrl.pathname + req.nextUrl.search)
        url.searchParams.set("error", "SessionExpired")
        return NextResponse.redirect(url)
      }

      if (isApiRoute) {
        return new NextResponse(
          JSON.stringify({
            error: "Unauthorized",
            message: "Invalid or expired token",
          }),
          {
            status: 401,
            headers: { "Content-Type": "application/json" },
          }
        )
      }
    }
  }

  if (isAuth && pathname === AUTH_SIGN_IN_PATH) {
    const url = req.nextUrl.clone()
    url.pathname = "/dashboard"
    return NextResponse.redirect(url)
  }

  if (!isAuth && (isDashboard || (isApiRoute && pathname !== "/api/health"))) {
    if (isDashboard) {
      const url = req.nextUrl.clone()
      url.pathname = AUTH_SIGN_IN_PATH
      url.searchParams.set("callbackUrl", req.nextUrl.pathname + req.nextUrl.search)
      return NextResponse.redirect(url)
    }

    if (isApiRoute) {
      return new NextResponse(
        JSON.stringify({
          error: "Unauthorized",
          message: "Authentication required",
        }),
        {
          status: 401,
          headers: { "Content-Type": "application/json" },
        }
      )
    }
  }

  return NextResponse.next()
}

export const config = {
  matcher: [
    "/dashboard/:path*",
    "/api/:path*",
    "/signin",
    "/signup",
    "/forgot-password",
    "/reset-password",
  ],
}