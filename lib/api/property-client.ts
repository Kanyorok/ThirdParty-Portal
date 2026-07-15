import { getBaseUrl } from "@/lib/api-base"
import { normalizeAccessToken } from "@/lib/auth/server-token"
import type { PaginatedResponse } from "@/types/property"

type QueryValue = string | number | boolean | null | undefined

type PropertyRequestOptions = {
    method?: "GET" | "POST" | "DELETE"
    accessToken?: string | null
    query?: Record<string, QueryValue>
    body?: unknown
    formData?: FormData
    headers?: Record<string, string>
    cache?: RequestCache
}

function withQuery(path: string, query?: Record<string, QueryValue>) {
    if (!query) return path

    const params = new URLSearchParams()
    Object.entries(query).forEach(([key, value]) => {
        if (value === null || value === undefined || value === "") return
        params.set(key, String(value))
    })

    const queryString = params.toString()
    return queryString ? `${path}?${queryString}` : path
}

function parseResponseBody(text: string, contentType: string) {
    if (!text) return undefined
    if (!contentType.toLowerCase().includes("application/json")) return text

    try {
        return JSON.parse(text)
    } catch {
        return text
    }
}

function resolveApiErrorMessage(payload: unknown, status: number, statusText: string) {
    if (typeof payload === "string" && payload.trim()) return payload

    if (payload && typeof payload === "object") {
        const node = payload as Record<string, unknown>
        const msg = node.message ?? node.error ?? node.detail
        if (typeof msg === "string" && msg.trim()) return msg

        const errors = node.errors
        if (errors && typeof errors === "object") {
            const pairs = Object.entries(errors as Record<string, unknown>)
            for (const [, value] of pairs) {
                if (Array.isArray(value) && value.length > 0 && typeof value[0] === "string") {
                    return value[0]
                }
                if (typeof value === "string" && value.trim()) return value
            }
        }
    }

    return `Property API request failed (${status} ${statusText})`
}

export async function propertyRequest<T>(
    path: string,
    options: PropertyRequestOptions = {}
): Promise<T> {
    const baseUrl = getBaseUrl()
    if (!baseUrl) throw new Error("API base URL is not defined")

    const token = normalizeAccessToken(options.accessToken)
    if (!token) throw new Error("Access token is required for property endpoints")

    const url = `${baseUrl}${withQuery(path, options.query)}`
    const method = options.method ?? "GET"
    const headers: Record<string, string> = {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
        ...(options.headers ?? {}),
    }

    const init: RequestInit = {
        method,
        cache: options.cache ?? "no-store",
        headers,
    }

    if (options.formData) {
        init.body = options.formData
    } else if (options.body !== undefined) {
        headers["Content-Type"] = "application/json"
        init.body = JSON.stringify(options.body)
    }

    const response = await fetch(url, init)
    const contentType = response.headers.get("content-type") ?? ""
    const text = await response.text()
    const payload = parseResponseBody(text, contentType)

    if (!response.ok) {
        throw new Error(resolveApiErrorMessage(payload, response.status, response.statusText))
    }

    return payload as T
}

function numberOr(value: unknown, fallback: number) {
    const parsed = Number(value)
    return Number.isFinite(parsed) ? parsed : fallback
}

export function normalizePaginatedResponse<T>(
    payload: unknown
): PaginatedResponse<T> {
    const root = payload && typeof payload === "object"
        ? (payload as Record<string, unknown>)
        : {}

    const data = Array.isArray(root.data) ? (root.data as T[]) : []

    const rawLinks =
        root.links && typeof root.links === "object"
            ? (root.links as Record<string, unknown>)
            : {}

    const rawMeta =
        root.meta && typeof root.meta === "object"
            ? (root.meta as Record<string, unknown>)
            : {}

    const links: PaginatedResponse<T>["links"] = {
        first: String(rawLinks.first ?? ""),
        last: String(rawLinks.last ?? ""),
        prev: rawLinks.prev == null ? null : String(rawLinks.prev),
        next: rawLinks.next == null ? null : String(rawLinks.next),
    }

    const meta: PaginatedResponse<T>["meta"] = {
        currentPage: numberOr(rawMeta.currentPage ?? rawMeta.current_page, 1),
        from: rawMeta.from == null ? null : numberOr(rawMeta.from, 0),
        lastPage: numberOr(rawMeta.lastPage ?? rawMeta.last_page, 1),
        links: Array.isArray(rawMeta.links)
            ? (rawMeta.links as PaginatedResponse<T>["meta"]["links"])
            : [],
        path: String(rawMeta.path ?? ""),
        perPage: numberOr(rawMeta.perPage ?? rawMeta.per_page, data.length || 1),
        to: rawMeta.to == null ? null : numberOr(rawMeta.to, 0),
        total: numberOr(rawMeta.total, data.length),
    }

    return {
        data,
        links,
        meta,
    }
}

export function requireQueryId(name: string, value: number | string | undefined | null) {
    if (value === null || value === undefined || String(value).trim() === "") {
        throw new Error(`${name} is required`)
    }
}
