"use server"

const API_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

interface AuthResult {
    success: boolean
    message?: string
    error?: string
    data?: any
}

export async function requestPasswordReset(email: string): Promise<AuthResult> {
    try {
        const response = await fetch(`${API_URL}/api/third-party-auth/forgot-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
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
            console.error("Forgot password failed:", data);
            return {
                success: false,
                error: "API_ERROR",
                message: data.message || "Failed to send reset link.",
            };
        }
    } catch (error) {
        console.error("Password reset request error:", error);
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Something went wrong. Please try again later.",
        };
    }
}

export async function validateResetToken(token: string): Promise<{ valid: boolean; error?: string; message?: string }> {
    // Optimistic validation: We assume token is valid if present. 
    // Real validation happens on submit. The backend doesn't have a verify-token-only endpoint exposed easily.
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
        const response = await fetch(`${API_URL}/api/third-party-auth/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                token,
                email,
                password: newPassword,
                password_confirmation: newPassword,
            }),
            cache: 'no-store'
        });

        const data = await response.json();

        if (response.ok) {
            return {
                success: true,
                message: data.message || "Password successfully reset.",
            };
        } else {
            console.error("Reset password failed:", data);

            // Map backend errors to frontend expected error codes
            let errorCode = "API_ERROR";
            if (data.message && data.message.includes("expired")) errorCode = "EXPIRED_TOKEN";
            if (data.message && data.message.includes("invalid")) errorCode = "INVALID_TOKEN";

            return {
                success: false,
                error: errorCode,
                message: data.message || "Failed to reset password.",
            };
        }
    } catch (error) {
        console.error("Password reset error:", error);
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Failed to reset password. Please try again.",
        };
    }
}
