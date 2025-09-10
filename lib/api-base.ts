export function getBaseUrl() {
    if (typeof window !== "undefined") {
        return process.env.NEXT_PUBLIC_EXTERNAL_API_URL || ""
    }
    return process.env.API_BASE_URL || process.env.NEXT_PUBLIC_EXTERNAL_API_URL
}

export async function apiFetch<T>(path: string, options?: RequestInit & { accessToken?: string; allowError?: boolean }): Promise<T> {
    const baseUrl = getBaseUrl()
    const isAbsolute = /^https?:\/\//i.test(path)
    const url = isAbsolute ? path : `${baseUrl}${path}`
    const { accessToken, allowError, ...fetchOptions } = options || {}
    const headers: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        ...(fetchOptions?.headers as Record<string, string> || {})
    }
    if (accessToken) headers["Authorization"] = `Bearer ${accessToken}`
    const res = await fetch(url, { ...fetchOptions, headers, cache: "no-store" })
    const text = await res.text()
    if (!res.ok && !allowError) throw new Error(`API request failed: ${res.status} ${res.statusText} - ${text.slice(0, 200)}`)
    try { return JSON.parse(text) } catch { throw new Error(`Invalid JSON from API at ${url}. Received: ${text.slice(0, 200)}`) }
}

export async function getRounds<T = unknown>(q?: Record<string, string | undefined>, accessToken?: string): Promise<T> {
    const params = new URLSearchParams()
    if (q) Object.entries(q).forEach(([k, v]) => { if (v) params.append(k, v) })
    const queryString = params.toString()
    return apiFetch<T>(`/api/procurement/prequalification/rounds${queryString ? `?${queryString}` : ""}`, { accessToken })
}

export async function getSupplierCategories<T = unknown>(accessToken?: string): Promise<T> {
    return apiFetch<T>("/api/procurement/supplier-cat", { accessToken })
}

export async function submitApplication(roundId: number, categoryIds: number[], accessToken: string) {
    return apiFetch(`/api/procurement/prequalification/applications`, {
        method: "POST",
        accessToken,
        body: JSON.stringify({ round_id: roundId, category_ids: categoryIds }),
    })
}

// Safer variant that returns status info without throwing on known handled errors
export async function submitApplicationSafe(roundId: number, categoryIds: number[], accessToken: string) {
    const baseUrl = getBaseUrl()
    const url = `${baseUrl}/api/procurement/prequalification/applications`
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${accessToken}`
        },
        body: JSON.stringify({ round_id: roundId, category_ids: categoryIds }),
        cache: 'no-store'
    })
    const text = await res.text()
    let json: any = null
    try { json = text ? JSON.parse(text) : null } catch { /* ignore */ }
    return { status: res.status, ok: res.ok, data: json, raw: text }
}
