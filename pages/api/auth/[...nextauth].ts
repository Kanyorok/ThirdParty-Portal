import NextAuth from "next-auth"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"
import type { NextApiRequest, NextApiResponse } from "next"

const handler = NextAuth(authOptions)

function stripSensitive(body: unknown): unknown {
    if (body && typeof body === "object" && !Buffer.isBuffer(body)) {
        const { accessToken: _accessToken, tokenType: _tokenType, access_token: _access_token, ...safe } = body as Record<string, unknown>
        return safe
    }
    if (typeof body === "string") {
        try {
            const parsed = JSON.parse(body)
            if (parsed && typeof parsed === "object" && "accessToken" in parsed) {
                const { accessToken: _accessToken, tokenType: _tokenType, access_token: _access_token, ...safe } = parsed
                return JSON.stringify(safe)
            }
        } catch {
            // not JSON — pass through
        }
    }
    return body
}

/**
 * Wraps the NextAuth handler to strip sensitive fields (accessToken)
 * from the /api/auth/session GET response sent to the browser.
 * Server-side code using getServerSession() still receives the full session.
 */
export default async function authHandler(req: NextApiRequest, res: NextApiResponse) {
    const isSessionGet =
        req.method === "GET" &&
        Array.isArray(req.query.nextauth) &&
        req.query.nextauth.includes("session")

    if (!isSessionGet) {
        return handler(req, res)
    }

    try {
        const session = await getServerSession(req, res, authOptions)
        return res.status(200).json(stripSensitive(session ?? {}))
    } catch {
        return res.status(200).json({})
    }
}
