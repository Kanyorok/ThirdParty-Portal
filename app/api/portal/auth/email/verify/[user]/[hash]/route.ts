import { NextResponse } from "next/server"
import { resolvePortalAuthApiBaseUrl } from "@/lib/portal-auth-api"

const unavailable = () => NextResponse.json({ success: false, error: "VERIFICATION_UNAVAILABLE" }, { status: 503, headers: { "Cache-Control": "no-store" } })

export async function GET(request: Request, context: { params: Promise<{ user: string; hash: string }> }) {
    const { user, hash } = await context.params
    const token = new URL(request.url).searchParams.get("token")
    if (!/^[a-zA-Z0-9_-]+$/.test(user) || !/^[a-fA-F0-9]{40}$/.test(hash) || !token || token.length > 512) {
        return NextResponse.json({ success: false, error: "INVALID_LINK" }, { status: 403 })
    }
    const base = resolvePortalAuthApiBaseUrl()
    if (!base) return unavailable()
    try {
        const query = new URLSearchParams({ token })
        const response = await fetch(`${base}/api/v1/portal/auth/email/verify/${user}/${hash}?${query}`, {
            headers: { Accept: "application/json" }, cache: "no-store", redirect: "manual", signal: AbortSignal.timeout(15000),
        })
        const body = await response.json().catch(() => null)
        if (!body || typeof body.success !== "boolean" || ![200, 403, 404, 409, 410, 429].includes(response.status)) return unavailable()
        return NextResponse.json(body, { status: response.status, headers: { "Cache-Control": "no-store" } })
    } catch {
        return unavailable()
    }
}