"use server"

import { requestPasswordReset } from "./auth-actions"

export async function recoverPassword(email: string) {
    return requestPasswordReset(email)
}

export async function registerUser(data: any) {
    try {
        if (!process.env.NEXT_PUBLIC_API_URL) {
            return { success: false, error: "CONFIG_ERROR", message: "NEXT_PUBLIC_API_URL is not configured" }
        }

        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
            body: JSON.stringify(data),
            cache: "no-store",
        })

        const body = await response.json().catch(() => null)

        if (!response.ok) {
            return {
                success: false,
                error: body?.message || "Registration failed",
                message: body?.message || "Registration failed",
                data: body ?? undefined,
            }
        }

        return { success: true, data: body, message: body?.message || "Registration successful" }
    } catch {
        return {
            success: false,
            error: "Registration failed",
            message: "Registration failed",
        };
    }
}
