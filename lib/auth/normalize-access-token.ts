export function normalizeAccessToken(accessToken?: string | null): string | null {
    const raw = String(accessToken ?? "").trim()
    if (!raw) return null

    const lowered = raw.toLowerCase()
    if (lowered === "undefined" || lowered === "null") return null

    const withoutBearer = raw.replace(/^bearer\s+/i, "").trim()
    if (!withoutBearer) return null

    return withoutBearer
}
