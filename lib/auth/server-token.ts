import { getToken } from "next-auth/jwt"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import type { NextRequest } from "next/server"

/**
 * Normalizes a raw access token string: trims whitespace,
 * rejects "undefined"/"null" literals, strips "Bearer " prefix.
 */
export function normalizeAccessToken(accessToken?: string | null): string | null {
    const raw = String(accessToken ?? "").trim()
    if (!raw) return null

    const lowered = raw.toLowerCase()
    if (lowered === "undefined" || lowered === "null") return null

    const withoutBearer = raw.replace(/^bearer\s+/i, "").trim()
    if (!withoutBearer) return null

    return withoutBearer
}

/**
 * Resolves the access token from a session-like object by checking
 * common property paths (session.accessToken, session.user.accessToken, etc).
 */
export function resolveSessionAccessToken(session: Record<string, any> | null | undefined): string {
    if (!session) return ""

    const candidates = [
        session.accessToken,
        session.access_token,
        session.token,
        session.user?.accessToken,
        session.user?.access_token,
        session.user?.token,
        session.user?.apiToken,
        session.user?.bearerToken,
    ]

    for (const value of candidates) {
        const token = normalizeAccessToken(
            typeof value === "string" || typeof value === "number"
                ? String(value)
                : null
        )
        if (token) return token
    }

    return ""
}

/**
 * Reads the access token from the JWT (server-side only).
 * Use in API route handlers where the NextRequest object is available.
 */
export async function getServerAccessToken(req: NextRequest): Promise<string | null> {
    const jwt = await getToken({ req, secret: process.env.NEXTAUTH_SECRET })
    const token = jwt?.accessToken
    return typeof token === "string" && token.trim() ? token.trim() : null
}

/**
 * Reads the access token from the server session (server components / actions).
 */
export async function getSessionAccessToken(): Promise<string | null> {
    const session = await getServerSession(authOptions)
    const token = (session as any)?.accessToken
    return typeof token === "string" && token.trim() ? token.trim() : null
}
