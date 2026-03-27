import NextAuth from "next-auth"
import { authOptions } from "@/lib/auth-options"
import type { NextApiRequest, NextApiResponse } from "next"

const handler = NextAuth(authOptions)

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

    // Strip accessToken from any response body (object or serialized JSON string)
    const stripSensitive = (body: unknown): unknown => {
        if (body && typeof body === "object" && !Buffer.isBuffer(body)) {
            const { accessToken, tokenType, access_token, ...safe } = body as Record<string, unknown>
            return safe
        }
        if (typeof body === "string") {
            try {
                const parsed = JSON.parse(body)
                if (parsed && typeof parsed === "object" && "accessToken" in parsed) {
                    const { accessToken, tokenType, access_token, ...safe } = parsed
                    return JSON.stringify(safe)
                }
            } catch {
                // not JSON — pass through
            }
        }
        return body
    }

    const originalJson = res.json.bind(res)
    const originalSend = res.send.bind(res)
    const originalEnd = res.end.bind(res)

    res.json = (body: unknown) => originalJson(stripSensitive(body))
    res.send = (body: unknown) => originalSend(stripSensitive(body))
    res.end = ((...args: unknown[]) => {
        if (args.length > 0 && typeof args[0] === "string") {
            args[0] = stripSensitive(args[0]) as string
        }
        return originalEnd(...(args as Parameters<typeof res.end>))
    }) as typeof res.end

    return handler(req, res)
}
