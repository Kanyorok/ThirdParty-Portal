"use server"

import { headers } from "next/headers"

import { getBaseUrl } from "@/lib/api-base"

export interface AuthResult {
    success: boolean
    message?: string
    error?: string
    redirect?: string
    route?: string
    data?: unknown
    errors?: Record<string, string[]>
}

export interface ValidationResult {
    valid: boolean
    error?: string
    message?: string
}

type ApiPayload = {
    success?: boolean
    status?: boolean
    message?: string
    route?: string
    data?: unknown
    redirect?: string
    errors?: Record<string, string[]>
}

function getApiBaseUrl() {
    return getBaseUrl()
}

type FrontendRequestContext = {
    origin?: string
    host?: string
    protocol?: string
    port?: string
}

async function getFrontendRequestContext(): Promise<FrontendRequestContext> {
    const headerStore = await headers()
    const forwardedHost = headerStore.get("x-forwarded-host")?.trim()
    const host = forwardedHost || headerStore.get("host")?.trim() || undefined
    const protocol = headerStore.get("x-forwarded-proto")?.trim() || undefined
    const explicitOrigin = headerStore.get("origin")?.trim()
    const requestUrl = headerStore.get("x-url")?.trim() || headerStore.get("referer")?.trim() || undefined

    const parseOrigin = (value?: string | null) => {
        if (!value) return undefined
        try {
            return new URL(value).origin
        } catch {
            return undefined
        }
    }

    const requestOrigin = parseOrigin(requestUrl)
    const origin = parseOrigin(explicitOrigin) || requestOrigin || (host && protocol ? `${protocol}://${host}` : undefined)

    let port: string | undefined
    try {
        if (origin) {
            const parsed = new URL(origin)
            port = parsed.port || undefined
        }
    } catch {
        port = undefined
    }

    return {
        origin,
        host,
        protocol,
        port,
    }
}

function withFrontendHeaders(baseHeaders: Record<string, string>, context: FrontendRequestContext, path: string) {
    const nextHeaders = { ...baseHeaders }

    if (context.origin) {
        nextHeaders.Origin = context.origin
        nextHeaders.Referer = `${context.origin}${path.startsWith("/") ? path : `/${path}`}`
        nextHeaders["X-Frontend-Origin"] = context.origin
    }

    if (context.host) {
        nextHeaders["X-Forwarded-Host"] = context.host
    }

    if (context.protocol) {
        nextHeaders["X-Forwarded-Proto"] = context.protocol
    }

    if (context.port) {
        nextHeaders["X-Forwarded-Port"] = context.port
    }

    return nextHeaders
}

async function parsePayload(response: Response): Promise<ApiPayload> {
    const text = await response.text().catch(() => "")
    if (!text) return {}
    try {
        return JSON.parse(text) as ApiPayload
    } catch {
        return { message: text }
    }
}

function firstFieldError(errors?: Record<string, string[]>) {
    if (!errors) return undefined
    for (const value of Object.values(errors)) {
        if (Array.isArray(value) && value.length > 0 && value[0]) return value[0]
    }
    return undefined
}

export async function requestPasswordReset(email: string): Promise<AuthResult> {
    const baseUrl = getApiBaseUrl()
    if (!baseUrl) {
        return {
            success: false,
            error: "CONFIG_ERROR",
            message: "URL not configured.",
        }
    }

    try {
        const frontendRequestContext = await getFrontendRequestContext()
        const response = await fetch(`${baseUrl}/api/v1/portal/auth/password/forgot`, {
            method: 'POST',
            headers: withFrontendHeaders({
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }, frontendRequestContext, '/forgot-password'),
            body: JSON.stringify({ email }),
            cache: 'no-store'
        });

        const payload = await parsePayload(response)

        if (response.ok || response.status === 202) {
            return {
                success: true,
                message: payload.message || "Password reset request received.",
                route: payload.route,
                data: payload.data,
            };
        }

        if (response.status === 429) {
            return {
                success: false,
                error: "RATE_LIMIT",
                message: payload.message || "Too many attempts. Onyi Tulia!",
                errors: payload.errors,
            };
        }

        if (response.status === 422) {
            const validationMessage = firstFieldError(payload.errors)
            return {
                success: false,
                error: "VALIDATION_ERROR",
                message: validationMessage || payload.message || "Please provide a valid email address.",
                errors: payload.errors,
            };
        }

        return {
            success: false,
            error: "API_ERROR",
            message: payload.message || "Failed to send reset link.",
            errors: payload.errors,
        };
    } catch {
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Something went wrong. Please try again later.",
        };
    }
}

export async function validateResetToken(token: string): Promise<ValidationResult> {
    if (!token || !token.trim()) {
        return {
            valid: false,
            error: "INVALID",
            message: "Reset token is required.",
        };
    }
    return { valid: true };
}

export async function resetPassword(token: string, newPassword: string, email: string): Promise<AuthResult> {
    const baseUrl = getApiBaseUrl()
    if (!baseUrl) {
        return {
            success: false,
            error: "CONFIG_ERROR",
            message: "URL is not configured.",
        }
    }

    try {
        const frontendRequestContext = await getFrontendRequestContext()
        const response = await fetch(`${baseUrl}/api/v1/portal/auth/password/reset`, {
            method: 'POST',
            headers: withFrontendHeaders({
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }, frontendRequestContext, '/reset-password'),
            body: JSON.stringify({
                token: token,
                email: email,
                password: newPassword,
                password_confirmation: newPassword,
            }),
            cache: 'no-store'
        });

        const payload = await parsePayload(response)

        if (response.status === 422) {
            const validationMessage = firstFieldError(payload.errors)
            return {
                success: false,
                error: "VALIDATION_ERROR",
                message: validationMessage || payload.message || "Validation failed.",
                errors: payload.errors,
            };
        }

        if (response.ok) {
            return {
                success: true,
                message: payload.message || "Password successfully reset.",
                redirect: payload.redirect
            };
        }

        return {
            success: false,
            error: "API_ERROR",
            message: payload.message || "An unexpected error occurred.",
            errors: payload.errors,
        };

    } catch {
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Connection lost. Reload.",
        };
    }
}

