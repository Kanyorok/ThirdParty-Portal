"use server"

export interface AuthResult {
    success: boolean
    message?: string
    error?: string
    redirect?: string
}

export interface ValidationResult {
    valid: boolean
    error?: string
    message?: string
}

export async function requestPasswordReset(email: string): Promise<AuthResult> {
    try {
        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/password/forgot`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ email }),
            cache: 'no-store'
        });

        const data = await response.json();

        if (response.ok) {
            return {
                success: true,
                message: data.message || "Reset link sent.",
            };
        } else {
            return {
                success: false,
                error: response.status === 429 ? "RATE_LIMIT" : "API_ERROR",
                message: data.message || "Failed to send reset link.",
            };
        }
    } catch {
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Something went wrong. Please try again later.",
        };
    }
}

export async function validateResetToken(token: string): Promise<ValidationResult> {
    if (!token) {
        return {
            valid: false,
            error: "INVALID",
            message: "Token is required.",
        };
    }
    return { valid: true };
}

export async function resetPassword(token: string, newPassword: string, email: string): Promise<AuthResult> {
    try {
        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/password/reset`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                token: token,
                email: email,
                password: newPassword,
                password_confirmation: newPassword,
            }),
            cache: 'no-store'
        });

        const data = await response.json();

        if (response.status === 422) {
            return {
                success: false,
                error: "VALIDATION_ERROR",
                message: data.message || data.errors?.email?.[0] || data.errors?.password?.[0] || "Validation failed.",
            };
        }

        if (response.ok) {
            return {
                success: true,
                message: data.message || "Password successfully reset.",
                redirect: data.redirect
            };
        }

        return {
            success: false,
            error: "API_ERROR",
            message: data.message || "An unexpected error occurred.",
        };

    } catch {
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Unable to connect to the authentication server.",
        };
    }
}
