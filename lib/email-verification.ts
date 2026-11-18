/** Normalize current portal links and older backend links to the local proxy. */
export function normalizeVerifyEmailPath(raw?: string | null): string | null {
    if (!raw?.trim()) return null
    let url: URL
    try { url = new URL(raw.trim(), "http://portal.local") } catch { return null }
    if (!["http:", "https:"].includes(url.protocol)) return null
    const match = url.pathname.match(/^\/(?:api\/(?:v1\/)?)?portal\/auth\/email\/verify\/([a-zA-Z0-9_-]+)\/([a-fA-F0-9]{40})$/)
    const token = url.searchParams.get("token")
    if (!match || !token || token.length > 512) return null
    return `/api/portal/auth/email/verify/${match[1]}/${match[2]}?${new URLSearchParams({ token })}`
}