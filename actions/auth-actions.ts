"use server"
import crypto from "crypto"

interface AuthResult {
    success: boolean
    message?: string
    error?: string
    data?: any
}

interface ResetTokenData {
    email: string
    token: string
    expiresAt: Date
    createdAt: Date
    used: boolean
}

const resetTokens = new Map<string, ResetTokenData>()
const rateLimitMap = new Map<string, { count: number; resetTime: number }>()

const RATE_LIMIT = {
    maxAttempts: 3,
    windowMs: 15 * 60 * 1000,
    blockDurationMs: 60 * 60 * 1000,
}

const TOKEN_CONFIG = {
    expirationMs: 15 * 60 * 1000,
    length: 32,
}

function checkRateLimit(identifier: string): { allowed: boolean; message?: string } {
    const now = Date.now()
    const rateLimitData = rateLimitMap.get(identifier)

    if (!rateLimitData) {
        rateLimitMap.set(identifier, { count: 1, resetTime: now + RATE_LIMIT.windowMs })
        return { allowed: true }
    }

    if (now > rateLimitData.resetTime) {
        rateLimitMap.set(identifier, { count: 1, resetTime: now + RATE_LIMIT.windowMs })
        return { allowed: true }
    }

    if (rateLimitData.count >= RATE_LIMIT.maxAttempts) {
        const remainingTime = Math.ceil((rateLimitData.resetTime - now) / 60000)
        return {
            allowed: false,
            message: `Too many password reset attempts. Please try again in ${remainingTime} minutes.`,
        }
    }

    rateLimitData.count++
    return { allowed: true }
}

function generateSecureToken(): string {
    return crypto.randomBytes(TOKEN_CONFIG.length).toString("hex")
}

async function sendPasswordResetEmail(email: string, token: string): Promise<boolean> {
    await new Promise((resolve) => setTimeout(resolve, 1000))


    console.log(`
    📧 Password Reset Email (Demo)
    To: ${email}
    Reset Link: ${process.env.NEXT_PUBLIC_APP_URL}/reset-password?token=${token}
    Expires: ${new Date(Date.now() + TOKEN_CONFIG.expirationMs).toLocaleString()}
  `)

    return true
}

function validateEmail(email: string): { valid: boolean; message?: string } {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

    if (!emailRegex.test(email)) {
        return { valid: false, message: "Invalid email format" }
    }

    return { valid: true }
}

export async function requestPasswordReset(email: string): Promise<AuthResult> {
    try {
        const emailValidation = validateEmail(email)
        if (!emailValidation.valid) {
            return {
                success: false,
                error: "VALIDATION_ERROR",
                message: emailValidation.message,
            }
        }

        const rateLimitCheck = checkRateLimit(email)
        if (!rateLimitCheck.allowed) {
            return {
                success: false,
                error: "RATE_LIMITED",
                message: rateLimitCheck.message,
            }
        }

        const token = generateSecureToken()
        const expiresAt = new Date(Date.now() + TOKEN_CONFIG.expirationMs)

        resetTokens.set(token, {
            email,
            token,
            expiresAt,
            createdAt: new Date(),
            used: false,
        })

        try {
            await sendPasswordResetEmail(email, token)
        } catch (emailError) {
            console.error("Failed to send password reset email:", emailError)
        }

        return {
            success: true,
            message: "If an account with that email exists, we've sent password reset instructions.",
        }
    } catch (error) {
        console.error("Password reset request error:", error)
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Something went wrong. Please try again later.",
        }
    }
}

export async function validateResetToken(token: string): Promise<{ valid: boolean; error?: string; message?: string }> {
    try {
        if (!token) {
            return {
                valid: false,
                error: "INVALID",
                message: "Reset token is required.",
            }
        }

        const tokenData = resetTokens.get(token)

        if (!tokenData) {
            return {
                valid: false,
                error: "INVALID",
                message: "Invalid or expired reset link.",
            }
        }

        if (tokenData.used) {
            return {
                valid: false,
                error: "USED",
                message: "This reset link has already been used.",
            }
        }

        if (new Date() > tokenData.expiresAt) {
            return {
                valid: false,
                error: "EXPIRED",
                message: "This reset link has expired. Please request a new one.",
            }
        }

        return { valid: true }
    } catch (error) {
        console.error("Token validation error:", error)
        return {
            valid: false,
            error: "INTERNAL_ERROR",
            message: "Unable to validate reset token.",
        }
    }
}

export async function resetPassword(token: string, newPassword: string): Promise<AuthResult> {
    try {
        const tokenValidation = await validateResetToken(token)
        if (!tokenValidation.valid) {
            return {
                success: false,
                error: tokenValidation.error === "EXPIRED" ? "EXPIRED_TOKEN" : "INVALID_TOKEN",
                message: tokenValidation.message,
            }
        }

        const tokenData = resetTokens.get(token)
        if (!tokenData) {
            return {
                success: false,
                error: "INVALID_TOKEN",
                message: "Invalid reset token.",
            }
        }

        if (newPassword.length < 8) {
            return {
                success: false,
                error: "WEAK_PASSWORD",
                message: "Password must be at least 8 characters long.",
            }
        }

        tokenData.used = true
        console.log(`
      🔐 Password Reset Successful (Demo)
      Email: ${tokenData.email}
      New password set at: ${new Date().toLocaleString()}
    `)

        cleanupExpiredTokens()

        return {
            success: true,
            message: "Your password has been successfully reset.",
        }
    } catch (error) {
        console.error("Password reset error:", error)
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Failed to reset password. Please try again.",
        }
    }
}

function cleanupExpiredTokens(): void {
    const now = new Date()
    for (const [token, data] of resetTokens.entries()) {
        if (now > data.expiresAt || data.used) {
            resetTokens.delete(token)
        }
    }
}

export async function getResetTokenInfo(token: string): Promise<ResetTokenData | null> {
    return resetTokens.get(token) || null
}
