"use client"

type ApiOptions = {
    token?: string | null
    baseUrl?: string
    headers?: Record<string, string>
    cache?: RequestCache
    next?: NextFetchRequestConfig
}

async function handle<T>(res: Response): Promise<T> {
    const contentType = res.headers.get("content-type") || ""
    const isJson = contentType.includes("application/json")
    const body = isJson ? await res.json() : await res.text()
    if (!res.ok) {
        const err = new Error(typeof body === "string" ? body : body?.message || "Request failed")
            ; (err as any).status = res.status
            ; (err as any).data = body
        throw err
    }
    return body as T
}

function authHeaders(token?: string | null): HeadersInit {
    const h: HeadersInit = {}
    if (token) h["Authorization"] = `Bearer ${token}`
    return h
}

export async function apiGet<T>(path: string, opts: ApiOptions = {}) {
    const url = `${opts.baseUrl ?? ""}${path}`
    const res = await fetch(url, {
        method: "GET",
        headers: {
            ...authHeaders(opts.token),
            ...(opts.headers || {}),
        },
        cache: opts.cache ?? "no-store",
        next: opts.next,
    })
    return handle<T>(res)
}

export async function apiPostJson<T>(path: string, data: unknown, opts: ApiOptions = {}) {
    const url = `${opts.baseUrl ?? ""}${path}`
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            ...authHeaders(opts.token),
            ...(opts.headers || {}),
        },
        body: JSON.stringify(data),
    })
    return handle<T>(res)
}

export async function apiPostForm<T>(path: string, form: FormData, opts: ApiOptions = {}) {
    const url = `${opts.baseUrl ?? ""}${path}`
    const res = await fetch(url, {
        method: "POST",
        headers: {
            ...authHeaders(opts.token),
            ...(opts.headers || {}),
        },
        body: form,
    })
    return handle<T>(res)
}
