import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"
import { getToken } from "next-auth/jwt"
import type { JWT } from "next-auth/jwt"

type AppToken = JWT & {
    user_id?: number
    third_party_id?: number
    is_supplier?: boolean
    isSupplier?: boolean
    is_tenant?: boolean
    isTenant?: boolean
    is_customer?: boolean
    isCustomer?: boolean
    approval_status?: string
    approvalStatus?: string
    third_party?: {
        isSupplier?: boolean
        isTenant?: boolean
        approvalStatus?: string
    }
}

const AUTH_SIGN_IN_PATH = "/signin"
const AUTH_SIGN_UP_PATH = "/signup"
const AUTH_REGISTER_PATH = "/register"
const DEFAULT_AUTH_REDIRECT = "/dashboard"

const PUBLIC_ROUTES = [
    "/api/auth",
    "/api/countries",
    "/api/v1/countries",
    "/api/third-party-details",
    "/api/currencies",
    "/api/portal/auth",
    "/api/v1/portal/auth",
    "/api/health",
    "/register",
    "/forgot-password",
    "/reset-password",
    "/_next",
    "/__nextjs_original-stack-frames"
]

function redirect(
    req: NextRequest,
    pathname: string,
    params?: Record<string, string>
) {
    const url = req.nextUrl.clone()
    url.pathname = pathname
    if (params) {
        for (const [k, v] of Object.entries(params)) {
            url.searchParams.set(k, v)
        }
    }
    return NextResponse.redirect(url)
}

export async function proxy(req: NextRequest) {
    const { pathname, search } = req.nextUrl

    if (PUBLIC_ROUTES.some(r => pathname.startsWith(r))) {
        return NextResponse.next()
    }

    const token = (await getToken({
        req,
        secret: process.env.NEXTAUTH_SECRET
    })) as AppToken | null

    const isAuthenticated = Boolean(token)

    const isAuthPage =
        pathname === AUTH_SIGN_IN_PATH ||
        pathname === AUTH_SIGN_UP_PATH ||
        pathname === AUTH_REGISTER_PATH

    if (isAuthenticated && isAuthPage) {
        return redirect(req, DEFAULT_AUTH_REDIRECT)
    }

    if (!isAuthenticated && pathname.startsWith("/dashboard")) {
        return redirect(req, AUTH_SIGN_IN_PATH, {
            callbackUrl: pathname + search
        })
    }

    if (!isAuthenticated && pathname.startsWith("/api")) {
        return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    if (isAuthenticated && token && pathname.startsWith("/dashboard/prequalification")) {
        const isSupplier = Boolean(
            token.is_supplier ??
            token.isSupplier ??
            token.third_party?.isSupplier
        )

        const approvalStatus =
            token.approval_status ??
            token.approvalStatus ??
            token.third_party?.approvalStatus

        const isApproved =
            approvalStatus === "A" ||
            approvalStatus === "APPROVED" ||
            approvalStatus === "approved"

        if (!isSupplier) {
            return redirect(req, "/dashboard", { error: "AccessDenied" })
        }

        if (!isApproved && pathname !== "/dashboard/prequalification/onboarding") {
            return redirect(
                req,
                "/dashboard/prequalification/onboarding",
                { error: "AccountNotApproved" }
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
        "/register",
        "/signup",
        "/forgot-password",
        "/reset-password"
    ]
}
