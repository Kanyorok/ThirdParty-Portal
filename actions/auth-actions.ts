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

function normalizeFrontendOrigin(value?: string | null) {
    if (!value) return undefined
    try {
        const parsed = new URL(value)
        if (parsed.protocol !== "http:" && parsed.protocol !== "https:") return undefined
        return parsed.origin
    } catch {
        return undefined
    }
}

async function getFrontendRequestContext(): Promise<FrontendRequestContext> {
    const headerStore = await headers()
    const forwardedHost = headerStore.get("x-forwarded-host")?.trim()
    const host = forwardedHost || headerStore.get("host")?.trim() || undefined
    const protocol = headerStore.get("x-forwarded-proto")?.trim() || undefined
    const explicitOrigin = headerStore.get("origin")?.trim()
    const requestUrl = headerStore.get("x-url")?.trim() || headerStore.get("referer")?.trim() || undefined

    const requestOrigin = normalizeFrontendOrigin(requestUrl)
    const origin = normalizeFrontendOrigin(explicitOrigin) || requestOrigin || (host && protocol ? `${protocol}://${host}` : undefined)

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
        nextHeaders["X-Origin"] = context.origin
    }

    if (context.host) {
        nextHeaders["X-Forwarded-Host"] = context.host
        nextHeaders["X-Original-Host"] = context.host
    }

    if (context.protocol) {
        nextHeaders["X-Forwarded-Proto"] = context.protocol
        nextHeaders["X-Original-Proto"] = context.protocol
    }

    if (context.port) {
        nextHeaders["X-Forwarded-Port"] = context.port
        nextHeaders["X-Original-Port"] = context.port
    }

    if (context.host || context.protocol) {
        const forwardedSegments = [
            context.protocol ? `proto=${context.protocol}` : null,
            context.host ? `host=${context.host}` : null,
        ].filter(Boolean)

        if (forwardedSegments.length > 0) {
            nextHeaders.Forwarded = forwardedSegments.join(";")
        }
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

export async function requestPasswordReset(email: string, frontendOrigin?: string | null): Promise<AuthResult> {
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
        const resolvedFrontendOrigin = normalizeFrontendOrigin(frontendOrigin) || frontendRequestContext.origin
        const resetUrl = resolvedFrontendOrigin ? `${resolvedFrontendOrigin}/reset-password` : undefined
        const response = await fetch(`${baseUrl}/api/v1/portal/auth/password/forgot`, {
            method: 'POST',
            headers: withFrontendHeaders({
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }, {
                ...frontendRequestContext,
                origin: resolvedFrontendOrigin,
                host: resolvedFrontendOrigin ? new URL(resolvedFrontendOrigin).host : frontendRequestContext.host,
                protocol: resolvedFrontendOrigin ? new URL(resolvedFrontendOrigin).protocol.replace(/:$/, "") : frontendRequestContext.protocol,
                port: resolvedFrontendOrigin ? new URL(resolvedFrontendOrigin).port || undefined : frontendRequestContext.port,
            }, '/forgot-password'),
            body: JSON.stringify({
                email,
                origin: resolvedFrontendOrigin,
                app_url: resolvedFrontendOrigin,
                callback_url: resetUrl,
                redirect_url: resetUrl,
                frontend_url: resolvedFrontendOrigin,
                reset_url: resetUrl,
            }),
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

export async function resendVerificationEmail(email: string, frontendOrigin?: string | null): Promise<AuthResult> {
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
        const resolvedFrontendOrigin = normalizeFrontendOrigin(frontendOrigin) || frontendRequestContext.origin
        const response = await fetch(`${baseUrl}/api/v1/portal/auth/email/resend`, {
            method: 'POST',
            headers: withFrontendHeaders({
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }, {
                ...frontendRequestContext,
                origin: resolvedFrontendOrigin,
                host: resolvedFrontendOrigin ? new URL(resolvedFrontendOrigin).host : frontendRequestContext.host,
                protocol: resolvedFrontendOrigin ? new URL(resolvedFrontendOrigin).protocol.replace(/:$/, "") : frontendRequestContext.protocol,
                port: resolvedFrontendOrigin ? new URL(resolvedFrontendOrigin).port || undefined : frontendRequestContext.port,
            }, '/verify-email/expired'),
            body: JSON.stringify({ email }),
            cache: 'no-store'
        });

        const payload = await parsePayload(response)

        if (response.ok || response.status === 202) {
            return {
                success: true,
                message: payload.message || "Verification email sent.",
                route: payload.route,
                data: payload.data,
            };
        }

        if (response.status === 429) {
            return {
                success: false,
                error: "RATE_LIMIT",
                message: payload.message || "Too many attempts. Please wait before trying again.",
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
            message: payload.message || "Failed to resend verification email.",
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

