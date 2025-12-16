import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"
import { getToken } from "next-auth/jwt"

const AUTH_SIGN_IN_PATH = "/signin"

const PUBLIC_API_ROUTES_PREFIXES = [
  "/api/v1/countries",
  "/api/third-party-details",
  "/api/currencies",
  "/api/portal/auth",
]

function createRedirect(req: NextRequest, path: string, params?: Record<string, string>): NextResponse {
  const url = req.nextUrl.clone()
  url.pathname = path

  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      url.searchParams.set(key, value)
    })
  }

  return NextResponse.redirect(url)
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
  const isAuth = !!token
  const isDashboard = pathname.startsWith("/dashboard")
  const isApiRoute = pathname.startsWith("/api")
  const isSignInPage = pathname === AUTH_SIGN_IN_PATH

  if (isAuth && isSignInPage) {
    return createRedirect(req, "/dashboard")
  }

  if (!isAuth && !isSignInPage && (isDashboard || (isApiRoute && pathname !== "/api/health"))) {
    if (isDashboard) {
      return createRedirect(req, AUTH_SIGN_IN_PATH, {
        callbackUrl: req.nextUrl.pathname + req.nextUrl.search
      })
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
