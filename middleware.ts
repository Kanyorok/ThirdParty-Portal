import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"
import { getToken } from "next-auth/jwt"

const AUTH_PAGES = new Set(["/signin", "/signup", "/forgot-password", "/reset-password"]) 
const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || "http://127.0.0.1:8000"

// Cache for token validation to avoid excessive backend calls
const tokenValidationCache = new Map<string, { valid: boolean; timestamp: number }>()
const CACHE_DURATION = 5 * 60 * 1000 // 5 minutes

async function validateTokenWithBackend(accessToken: string): Promise<boolean> {
  // Check cache first
  const cached = tokenValidationCache.get(accessToken)
  if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
    return cached.valid
  }

  try {
    const response = await fetch(`${EXTERNAL_API_BASE}/api/auth/validate-token`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      cache: 'no-store'
    })

    const isValid = response.ok
    
    // Cache the result
    tokenValidationCache.set(accessToken, {
      valid: isValid,
      timestamp: Date.now()
    })

    // Clean old cache entries periodically
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
    console.warn('Backend token validation unavailable, skipping validation:', error.message)
    // If backend is unavailable, assume token is valid to avoid blocking users
    // This allows the application to work even when backend validation endpoint doesn't exist
    tokenValidationCache.set(accessToken, {
      valid: true,
      timestamp: Date.now()
    })
    return true
  }
}

export async function middleware(req: NextRequest) {
  const { pathname } = req.nextUrl

  // Skip for static files and Next internals
  if (
    pathname.startsWith("/_next") ||
    pathname.startsWith("/static") ||
    pathname.startsWith("/images") ||
    pathname.startsWith("/favicon") ||
    pathname.startsWith("/robots.txt") ||
    pathname.startsWith("/sitemap.xml")
  ) {
    return NextResponse.next()
  }

  // Skip for auth API routes and public API routes
  if (pathname.startsWith("/api/auth")) {
    return NextResponse.next()
  }

  // Allow certain API endpoints without authentication (for registration process)
  const publicApiRoutes = [
    "/api/v1/countries",           // Countries dropdown for registration form
    "/api/third-party-details",   // Third party registration endpoint
    "/api/currencies",            // Currencies for forms
    "/api/third-party-auth",      // Authentication endpoints for third parties
  ]
  
  if (publicApiRoutes.some(route => pathname.startsWith(route))) {
    return NextResponse.next()
  }

  const token = await getToken({ req, secret: process.env.NEXTAUTH_SECRET })
  let isAuth = !!token
  const isAuthPage = AUTH_PAGES.has(pathname)
  const isDashboard = pathname.startsWith("/dashboard")
  const isApiRoute = pathname.startsWith("/api")

  // Enhanced authentication check for dashboard and API routes
  // Temporarily disabled backend validation until backend endpoint is implemented
  /*
  if (isAuth && token?.accessToken && (isDashboard || isApiRoute)) {
    const isValidToken = await validateTokenWithBackend(token.accessToken as string)
    
    if (!isValidToken) {
      console.log('Invalid token detected, clearing session')
      isAuth = false
      
      // For dashboard routes, redirect to signin
      if (isDashboard) {
        const url = req.nextUrl.clone()
        url.pathname = "/signin"
        url.searchParams.set("callbackUrl", req.nextUrl.pathname + req.nextUrl.search)
        url.searchParams.set("error", "SessionExpired")
        return NextResponse.redirect(url)
      }
      
      // For API routes, return 401
      if (isApiRoute) {
        return new NextResponse(
          JSON.stringify({ 
            error: 'Unauthorized', 
            message: 'Invalid or expired token' 
          }),
          { 
            status: 401,
            headers: { 'Content-Type': 'application/json' }
          }
        )
      }
    }
  }
  */

  // Allow signup/forgot/reset even if authenticated; only redirect authenticated users away from /signin
  if (isAuth && pathname === "/signin") {
    const url = req.nextUrl.clone()
    url.pathname = "/dashboard"
    return NextResponse.redirect(url)
  }

  // If not authenticated and trying to access protected routes
  if (!isAuth && (isDashboard || isApiRoute)) {
    if (isDashboard) {
      const url = req.nextUrl.clone()
      url.pathname = "/signin"
      url.searchParams.set("callbackUrl", req.nextUrl.pathname + req.nextUrl.search)
      return NextResponse.redirect(url)
    }
    
    if (isApiRoute) {
      return new NextResponse(
        JSON.stringify({ 
          error: 'Unauthorized', 
          message: 'Authentication required' 
        }),
        { 
          status: 401,
          headers: { 'Content-Type': 'application/json' }
        }
      )
    }
  }

  return NextResponse.next()
}

export const config = {
  matcher: [
    "/dashboard/:path*",
    "/api/((?!auth).*)", // Protect all API routes except auth (public routes handled in middleware)
    "/signin",
    "/signup",
    "/forgot-password",
    "/reset-password",
    "/"
  ],
}
