import { normalizeAccessToken } from "@/lib/auth/normalize-access-token"

type SessionLike = Record<string, any> | null | undefined

export function resolveSessionAccessToken(session: SessionLike): string {
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
