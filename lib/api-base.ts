declare global {
    interface Window {
        __ENV__?: {
            API_BASE_URL?: string
            NEXT_PUBLIC_API_URL?: string
            EXTERNAL_API_URL?: string
        }
    }
}

const ABSOLUTE_HTTP_URL_PATTERN = /^https?:\/\//i
const JSON_CONTENT_TYPE_PATTERN = /(^|\s|;)application\/json|\+json/i
const DEFAULT_TIMEOUT_MS = 30_000

type ApiErrorBody = {
    message?: string
    error?: string
}

export type ApiRequestResult<T = unknown> = {
    status: number
    ok: boolean
    data: T | null
    raw: string
}

type ApiFetchOptions = RequestInit & {
    allowError?: boolean
    timeoutMs?: number
}

function normalizeBaseUrl(value: string | null | undefined): string {
    const candidate = String(value ?? "").trim()
    if (!candidate) return ""

    const normalized = candidate.replace(/\/+$/, "")
    if (normalized.startsWith("/")) {
        return normalized
    }

    if (!ABSOLUTE_HTTP_URL_PATTERN.test(normalized)) {
        return ""
    }

    try {
        const parsed = new URL(normalized)
        return parsed.protocol === "http:" || parsed.protocol === "https:"
            ? normalized
            : ""
    } catch {
        return ""
    }
}

function getRuntimeBaseUrl(): string {
    if (typeof window === "undefined") {
        return ""
    }

    try {
        const runtime = window.__ENV__ ?? {}
        return normalizeBaseUrl(
            runtime.API_BASE_URL ??
            runtime.NEXT_PUBLIC_API_URL ??
            runtime.EXTERNAL_API_URL
        )
    } catch {
        return ""
    }
}

export function getBaseUrl() {
    return getRuntimeBaseUrl() || normalizeBaseUrl(
        process.env.API_BASE_URL ??
        process.env.NEXT_PUBLIC_API_URL ??
        process.env.EXTERNAL_API_URL
    )
}

function resolveRequestUrl(path: string, baseUrl = getBaseUrl()): string {
    const trimmedPath = String(path ?? "").trim()
    if (!trimmedPath) {
        throw new Error("API request path is required")
    }

    if (ABSOLUTE_HTTP_URL_PATTERN.test(trimmedPath)) {
        return trimmedPath
    }

    const normalizedPath = trimmedPath.startsWith("/") ? trimmedPath : `/${trimmedPath}`
    return baseUrl ? `${baseUrl}${normalizedPath}` : normalizedPath
}

function mergeHeaders(headers?: HeadersInit, body?: BodyInit | null): Headers {
    const mergedHeaders = new Headers(headers)
    if (!mergedHeaders.has("Accept")) {
        mergedHeaders.set("Accept", "application/json")
    }

    if (
        body &&
        typeof body === "string" &&
        !mergedHeaders.has("Content-Type")
    ) {
        mergedHeaders.set("Content-Type", "application/json")
    }

    return mergedHeaders
}

function createRequestSignal(timeoutMs: number, signal?: AbortSignal | null) {
    if ((!timeoutMs || timeoutMs <= 0) && !signal) {
        return { signal: undefined as AbortSignal | undefined, cleanup: () => undefined }
    }

    const controller = new AbortController()
    let timeoutId: ReturnType<typeof setTimeout> | undefined

    const abortWithReason = (reason?: unknown) => {
        if (!controller.signal.aborted) {
            controller.abort(reason)
        }
    }

    const onAbort = () => abortWithReason(signal?.reason)

    if (signal) {
        if (signal.aborted) {
            abortWithReason(signal.reason)
        } else {
            signal.addEventListener("abort", onAbort, { once: true })
        }
    }

    if (timeoutMs > 0) {
        timeoutId = setTimeout(() => {
            abortWithReason(new Error(`Request timed out after ${timeoutMs}ms`))
        }, timeoutMs)
    }

    return {
        signal: controller.signal,
        cleanup: () => {
            if (timeoutId) {
                clearTimeout(timeoutId)
            }
            if (signal) {
                signal.removeEventListener("abort", onAbort)
            }
        }
    }
}

function shouldParseJson(contentType: string, text: string): boolean {
    if (!text) return false
    if (JSON_CONTENT_TYPE_PATTERN.test(contentType)) return true

    const trimmed = text.trim()
    return trimmed.startsWith("{") || trimmed.startsWith("[")
}

function parseResponseBody<T>(text: string, contentType: string, url: string, allowInvalidJson: boolean): T | string | null {
    if (!text) {
        return null
    }

    if (!shouldParseJson(contentType, text)) {
        return text
    }

    try {
        return JSON.parse(text) as T
    } catch {
        if (allowInvalidJson) {
            return text
        }

        throw new Error(`Invalid JSON at ${url}. Received: ${text.slice(0, 200)}`)
    }
}

function getDefaultCredentials(url: string, explicitCredentials?: RequestCredentials): RequestCredentials | undefined {
    if (explicitCredentials) {
        return explicitCredentials
    }

    if (!ABSOLUTE_HTTP_URL_PATTERN.test(url)) {
        return "same-origin"
    }

    if (typeof window === "undefined") {
        return "same-origin"
    }

    try {
        const target = new URL(url, window.location.origin)
        return target.origin === window.location.origin ? "same-origin" : undefined
    } catch {
        return "same-origin"
    }
}

function extractErrorMessage(body: unknown, status: number, statusText: string): string {
    if (body && typeof body === "object") {
        const errorBody = body as ApiErrorBody
        const message = errorBody.message?.trim() || errorBody.error?.trim()
        if (message) {
            return message
        }
    }

    if (typeof body === "string" && body.trim()) {
        return body.trim().slice(0, 200)
    }

    return `API request failed: ${status}${statusText ? ` ${statusText}` : ""}`
}

async function executeApiRequest<T>(path: string, options?: ApiFetchOptions): Promise<ApiRequestResult<T>> {
    const { allowError = false, timeoutMs = DEFAULT_TIMEOUT_MS, signal, headers, body, ...fetchOptions } = options || {}
    const url = resolveRequestUrl(path)
    const mergedHeaders = mergeHeaders(headers, body)
    const { signal: requestSignal, cleanup } = createRequestSignal(timeoutMs, signal)

    try {
        const response = await fetch(url, {
            ...fetchOptions,
            body,
            headers: mergedHeaders,
            signal: requestSignal,
            credentials: getDefaultCredentials(url, fetchOptions.credentials),
            cache: fetchOptions.cache ?? "no-store",
        })

        const raw = await response.text()
        const contentType = response.headers.get("content-type") ?? ""
        const data = parseResponseBody<T>(raw, contentType, url, allowError && !response.ok)

        if (!response.ok && !allowError) {
            throw new Error(extractErrorMessage(data, response.status, response.statusText))
        }

        return {
            status: response.status,
            ok: response.ok,
            data: (data ?? null) as T | null,
            raw,
        }
    } catch (error) {
        if (error instanceof Error && error.name === "AbortError") {
            throw new Error(`Request aborted: ${path}`)
        }

        if (error instanceof Error && /timed out/i.test(error.message)) {
            throw new Error(`Request timed out: ${path}`)
        }

        throw error
    } finally {
        cleanup()
    }
}

export async function apiFetch<T>(path: string, options?: ApiFetchOptions): Promise<T> {
    const result = await executeApiRequest<T>(path, options)
    return result.data as T
}

export async function getRounds<T = unknown>(q?: Record<string, string | undefined>): Promise<T> {
    const params = new URLSearchParams()
    if (q) {
        Object.entries(q).forEach(([key, value]) => {
            if (value != null && value !== "") {
                params.append(key, value)
            }
        })
    }

    const queryString = params.toString()
    return apiFetch<T>(`/api/prequalification/rounds${queryString ? `?${queryString}` : ""}`)
}

export async function getSupplierCategories<T = unknown>(): Promise<T> {
    return apiFetch<T>("/portal/metadata/supplier-categories")
}

function validateApplicationPayload(roundId: number, categoryIds: number[]) {
    if (!Number.isInteger(roundId) || roundId <= 0) {
        throw new Error("A valid round ID is required")
    }

    const normalizedCategoryIds = Array.from(new Set(
        categoryIds.filter((categoryId) => Number.isInteger(categoryId) && categoryId > 0)
    ))

    if (normalizedCategoryIds.length === 0) {
        throw new Error("At least one valid category must be selected")
    }

    return {
        round_id: roundId,
        category_ids: normalizedCategoryIds,
    }
}

export async function submitApplication(roundId: number, categoryIds: number[]) {
    const result = await submitApplicationSafe(roundId, categoryIds)
    if (!result.ok) {
        throw new Error(extractErrorMessage(result.data, result.status, ""))
    }

    return result.data
}

export async function submitApplicationSafe(roundId: number, categoryIds: number[]) {
    const payload = validateApplicationPayload(roundId, categoryIds)

    return executeApiRequest<Record<string, unknown>>("/api/prequalification/applications", {
        method: "POST",
        body: JSON.stringify(payload),
        allowError: true,
    })
}
