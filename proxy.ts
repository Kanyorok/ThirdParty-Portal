import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"
import { getToken } from "next-auth/jwt"

const AUTH_SIGN_IN_PATH = "/signin"
const AUTH_REGISTER_PATH = "/register"
const DEFAULT_AUTH_REDIRECT = "/dashboard"

const PUBLIC_ROUTES = [
    "/api/auth",
    "/api/v1/countries",
    "/api/third-party-details",
    "/api/currencies",
    "/api/portal/auth",
    "/api/v1/portal/auth",
    "/api/health",
    "/register",
    "/forgot-password",
    "/reset-password"
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
    const { pathname, search } = req.nextUrl

    if (PUBLIC_ROUTES.some(route => pathname.startsWith(route))) {
        return NextResponse.next()
    }

    const token = await getToken({ req, secret: process.env.NEXTAUTH_SECRET })
    const isAuth = !!token
    const isAuthPage = pathname === AUTH_SIGN_IN_PATH || pathname === AUTH_REGISTER_PATH

    if (isAuth && isAuthPage) {
        return createRedirect(req, DEFAULT_AUTH_REDIRECT)
    }

    if (!isAuth && !isAuthPage) {
        if (pathname.startsWith("/dashboard")) {
            return createRedirect(req, AUTH_SIGN_IN_PATH, {
                callbackUrl: encodeURIComponent(pathname + search)
            })
        }
        if (pathname.startsWith("/api")) {
            return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
        }
    }

    if (isAuth && pathname.startsWith("/dashboard/prequalification")) {
        const isSupplier = token.is_supplier
        const isApproved = token.is_approved

        if (!isSupplier) {
            return createRedirect(req, "/dashboard", { error: "AccessDenied" })
        }

        if (!isApproved && pathname !== "/dashboard/prequalification/onboarding") {
            return createRedirect(req, "/dashboard/prequalification/onboarding")
        }
    }

    return NextResponse.next()
}

export const config = {
    matcher: [
        "/dashboard/:path*",
        "/api/:path*",
        "/signin",
        "/register",
        "/signup",
        "/forgot-password",
        "/reset-password",
    ],
}