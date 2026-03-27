export function getBaseUrl() {
    if (typeof window !== "undefined") {
        // Prefer runtime-injected environment (window.__ENV__) for dynamic
        // deployments (allows changing API host without rebuilding).
        // Fallbacks: API_BASE_URL, NEXT_PUBLIC_API_URL, EXTERNAL_API_URL.
        try {
            const win: any = window as any
            const runtime = win.__ENV__ || {}
            const runtimeUrl = (runtime.API_BASE_URL || runtime.NEXT_PUBLIC_API_URL || runtime.EXTERNAL_API_URL) as string | undefined
            if (runtimeUrl && runtimeUrl !== '') {
                // normalize (remove trailing slash)
                return runtimeUrl.replace(/\/+$/, '')
            }
        } catch {
            // ignore and fall back to default behaviour
        }

        // Default client behaviour: use relative paths so requests go through
        // the frontend host (and any reverse-proxy) unless a runtime URL is present.
        return ""
    }

    return process.env.API_BASE_URL || process.env.NEXT_PUBLIC_API_URL || process.env.EXTERNAL_API_URL || ''
}

export async function apiFetch<T>(path: string, options?: RequestInit & { allowError?: boolean }): Promise<T> {
    const baseUrl = getBaseUrl()
    const isAbsolute = /^https?:\/\//i.test(path)
    const url = isAbsolute ? path : `${baseUrl}${path}`
    const { allowError, ...fetchOptions } = options || {}
    const headers: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        ...(fetchOptions?.headers as Record<string, string> || {})
    }
    const res = await fetch(url, { ...fetchOptions, headers, credentials: "same-origin", cache: "no-store" })
    const text = await res.text()
    if (!res.ok && !allowError) throw new Error(`API request failed: ${res.status} ${res.statusText} - ${text.slice(0, 200)}`)
    try { return JSON.parse(text) } catch { throw new Error(`Invalid JSON at ${url}. Received: ${text.slice(0, 200)}`) }
}

export async function getRounds<T = unknown>(q?: Record<string, string | undefined>): Promise<T> {
    const params = new URLSearchParams()
    if (q) Object.entries(q).forEach(([k, v]) => { if (v) params.append(k, v) })
    const queryString = params.toString()
    return apiFetch<T>(`/api/prequalification/rounds${queryString ? `?${queryString}` : ""}`)
}

export async function getSupplierCategories<T = unknown>(): Promise<T> {
    return apiFetch<T>("/api/procurement/supplier-cat")
}

export async function submitApplication(roundId: number, categoryIds: number[]) {
    const result = await submitApplicationSafe(roundId, categoryIds)
    if (!result.ok) {
        const errorMessage =
            result.data?.message ||
            result.data?.error ||
            `API request failed: ${result.status}`
        throw new Error(errorMessage)
    }
    return result.data
}

export async function submitApplicationSafe(roundId: number, categoryIds: number[]) {
    const payload = {
        round_id: roundId,
        category_ids: categoryIds,
    }

    const url = "/api/prequalification/applications"
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
        cache: 'no-store'
    })
    const text = await res.text()
    let json: any = null
    try { json = text ? JSON.parse(text) : null } catch { /* ignore */ }
    return { status: res.status, ok: res.ok, data: json, raw: text }
}
