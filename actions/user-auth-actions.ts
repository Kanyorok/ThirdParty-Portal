"use server"
import crypto from "crypto"
import bcrypt from "bcryptjs"

interface AuthResult {
    success: boolean
    message?: string
    error?: string
    data?: any
}

interface User {
    id: string
    email: string
    passwordHash: string
}

interface ResetTokenData {
    userId: string
    email: string
    token: string
    expiresAt: Date
    createdAt: Date
    used: boolean
}

const SALT_ROUNDS = 12
const TOKEN_CONFIG = {
    expirationMs: 15 * 60 * 1000,
    length: 32,
    rateLimitMs: 60 * 1000,
    maxAttempts: 1
}

const db = {
    users: {
        findByEmail: async (email: string): Promise<User | null> => {
            return null
        },
        updatePassword: async (userId: string, hashedPassword: string): Promise<void> => {
            return
        }
    },
    resetTokens: {
        save: async (data: ResetTokenData): Promise<void> => {
            return
        },
        findByToken: async (token: string): Promise<ResetTokenData | null> => {
            return null
        },
        markAsUsed: async (token: string): Promise<void> => {
            return
        }
    },
    rateLimit: {
        check: async (identifier: string): Promise<{ allowed: boolean; message?: string }> => {
            return { allowed: true }
        }
    }
}

function generateSecureToken(): string {
    return crypto.randomBytes(TOKEN_CONFIG.length).toString("hex")
}

async function sendPasswordResetEmail(email: string, token: string): Promise<boolean> {
    await new Promise((resolve) => setTimeout(resolve, 1000))
    if (process.env.NODE_ENV !== "production") {
        console.log(`[EMAIL SEND] To: ${email}, Link: /reset-password?token=${token}`)
    }
    return true
}

function validateEmail(email: string): { valid: boolean; message?: string } {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(email)) {
        return { valid: false, message: "Invalid email format." }
    }
    if (email.length > 254) {
        return { valid: false, message: "Email is too long." }
    }
    return { valid: true }
}

function validatePassword(password: string): { valid: boolean; message?: string } {
    if (password.length < 10) {
        return { valid: false, message: "Password must be at least 10 characters long." }
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

        const rateLimitCheck = await db.rateLimit.check(email)
        if (!rateLimitCheck.allowed) {
            return {
                success: false,
                error: "RATE_LIMITED",
                message: rateLimitCheck.message,
            }
        }

        const user = await db.users.findByEmail(email)

        if (!user) {
            await new Promise(resolve => setTimeout(resolve, 1500))
            return { success: true, message: "If an account with that email exists, we've sent password reset instructions." }
        }

        const token = generateSecureToken()
        const expiresAt = new Date(Date.now() + TOKEN_CONFIG.expirationMs)

        await db.resetTokens.save({
            userId: user.id,
            email: user.email,
            token,
            expiresAt,
            createdAt: new Date(),
            used: false,
        })

        await sendPasswordResetEmail(user.email, token)

        return { success: true, message: "If an account with that email exists, we've sent password reset instructions." }
    } catch (error) {
        console.error("Password reset request error:", error)
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "An unexpected error occurred. Please try again later.",
        }
    }
}

export async function validateResetToken(token: string): Promise<{ valid: boolean; error?: string; message?: string; data?: { email: string } }> {
    try {
        if (!token || token.length !== TOKEN_CONFIG.length * 2) {
            return {
                valid: false,
                error: "INVALID_FORMAT",
                message: "Invalid or malformed reset link.",
            }
        }

        const tokenData = await db.resetTokens.findByToken(token)

        if (!tokenData || tokenData.used) {
            return {
                valid: false,
                error: "INVALID_OR_USED",
                message: "Invalid or already used reset link.",
            }
        }

        if (new Date() > tokenData.expiresAt) {
            return {
                valid: false,
                error: "EXPIRED",
                message: "The reset link has expired.",
            }
        }

        return { valid: true, data: { email: tokenData.email } }
    } catch (error) {
        console.error("Token validation error:", error)
        return {
            valid: false,
            error: "INTERNAL_ERROR",
            message: "Unable to validate reset token due to an internal error.",
        }
    }
}

export async function resetPassword(token: string, newPassword: string): Promise<AuthResult> {
    try {
        const passwordValidation = validatePassword(newPassword)
        if (!passwordValidation.valid) {
            return {
                success: false,
                error: "WEAK_PASSWORD",
                message: passwordValidation.message,
            }
        }

        const tokenData = await db.resetTokens.findByToken(token)

        if (!tokenData || tokenData.used || new Date() > tokenData.expiresAt) {
            return {
                success: false,
                error: "INVALID_OR_EXPIRED",
                message: "Invalid or expired reset link. Please request a new one.",
            }
        }

        const hashedPassword = await bcrypt.hash(newPassword, SALT_ROUNDS)

        await db.users.updatePassword(tokenData.userId, hashedPassword)

        await db.resetTokens.markAsUsed(token)

        return {
            success: true,
            message: "Your password has been successfully reset. You can now log in.",
        }
    } catch (error) {
        console.error("Password reset error:", error)
        return {
            success: false,
            error: "INTERNAL_ERROR",
            message: "Failed to reset password due to an internal server error.",
        }
    }
}

export async function getResetTokenInfo(token: string): Promise<ResetTokenData | null> {
    return await db.resetTokens.findByToken(token)
}
