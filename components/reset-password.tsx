"use client"

import { useState, useTransition, useEffect } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Alert, AlertDescription } from "@/components/common/alert"


import { Lock, Eye, EyeOff, CheckCircle, AlertCircle, Shield, Loader2, Check, X } from "lucide-react"
import { ContactSection } from "./common/contact-us"
import { AuthHeader } from "./layout/auth-header"
import { resetPassword, validateResetToken } from "@/actions/auth-actions"


const resetPasswordSchema = z
    .object({
        password: z
            .string()
            .min(8, "Password must be at least 8 characters")
            .max(128, "Password cannot exceed 128 characters")
            .regex(/[A-Z]/, "Password must contain at least one uppercase letter")
            .regex(/[a-z]/, "Password must contain at least one lowercase letter")
            .regex(/[0-9]/, "Password must contain at least one number")
            .regex(/[^A-Za-z0-9]/, "Password must contain at least one special character"),
        confirmPassword: z.string().min(1, "Please confirm your password"),
    })
    .refine((data) => data.password === data.confirmPassword, {
        message: "Passwords do not match",
        path: ["confirmPassword"],
    })

type ResetPasswordInputs = z.infer<typeof resetPasswordSchema>

interface ResetPasswordState {
    type: "idle" | "loading" | "success" | "error" | "invalid_token" | "expired_token"
    message?: string
}

interface ResetPasswordFormProps {
    token: string
}

export function ResetPasswordForm({ token }: ResetPasswordFormProps) {
    const router = useRouter()
    const [isPending, startTransition] = useTransition()
    const [state, setState] = useState<ResetPasswordState>({ type: "idle" })
    const [showPassword, setShowPassword] = useState(false)
    const [showConfirmPassword, setShowConfirmPassword] = useState(false)
    const [tokenValid, setTokenValid] = useState<boolean | null>(null)

    const {
        register,
        handleSubmit,
        formState: { errors, isValid },
        watch,
        setError,
    } = useForm<ResetPasswordInputs>({
        resolver: zodResolver(resetPasswordSchema),
        mode: "onChange",
        defaultValues: {
            password: "",
            confirmPassword: "",
        },
    })

    const watchedPassword = watch("password", "")
    const watchedConfirmPassword = watch("confirmPassword", "")

    useEffect(() => {
        const checkToken = async () => {
            try {
                const result = await validateResetToken(token)
                setTokenValid(result.valid)

                if (!result.valid) {
                    setState({
                        type: result.error === "EXPIRED" ? "expired_token" : "invalid_token",
                        message: result.message,
                    })
                }
            } catch (error) {
                setTokenValid(false)
                setState({
                    type: "invalid_token",
                    message: "Unable to validate reset token. Please try again.",
                })
            }
        }

        if (token) {
            checkToken()
        }
    }, [token])

    const getPasswordStrength = (password: string) => {
        const requirements = [
            { test: password.length >= 8, label: "At least 8 characters" },
            { test: /[A-Z]/.test(password), label: "One uppercase letter" },
            { test: /[a-z]/.test(password), label: "One lowercase letter" },
            { test: /[0-9]/.test(password), label: "One number" },
            { test: /[^A-Za-z0-9]/.test(password), label: "One special character" },
        ]

        const score = requirements.filter((req) => req.test).length
        const strength = score < 2 ? "weak" : score < 4 ? "medium" : "strong"

        return { requirements, score, strength }
    }

    const passwordStrength = getPasswordStrength(watchedPassword)

    const onSubmit = async (data: ResetPasswordInputs) => {
        startTransition(async () => {
            setState({ type: "loading" })

            try {
                const result = await resetPassword(token, data.password)

                if (result.success) {
                    setState({
                        type: "success",
                        message: result.message,
                    })
                } else {
                    if (result.error === "INVALID_TOKEN") {
                        setState({
                            type: "invalid_token",
                            message: result.message,
                        })
                    } else if (result.error === "EXPIRED_TOKEN") {
                        setState({
                            type: "expired_token",
                            message: result.message,
                        })
                    } else {
                        setState({
                            type: "error",
                            message: result.message || "Failed to reset password. Please try again.",
                        })
                    }
                }
            } catch (error) {
                setState({
                    type: "error",
                    message: "Network error. Please check your connection and try again.",
                })
            }
        })
    }

    // Success state
    if (state.type === "success") {
        return (
            <div className="w-full max-w-md mx-auto">
                <AuthHeader />

                <div className="space-y-6">
                    <div className="text-center space-y-4">
                        <div className="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                            <CheckCircle className="w-8 h-8 text-green-600" />
                        </div>

                        <div className="space-y-2">
                            <h2 className="text-2xl font-semibold text-gray-900">Password reset successful!</h2>
                            <p className="text-gray-600 text-sm leading-relaxed">
                                Your password has been successfully updated. You can now sign in with your new password.
                            </p>
                        </div>
                    </div>

                    <Button onClick={() => router.push("/signin")} className="w-full">
                        <Shield className="w-4 h-4 mr-2" />
                        Continue to sign in
                    </Button>
                </div>

                <ContactSection />
            </div>
        )
    }

    // Invalid or expired token states
    if (tokenValid === false || state.type === "invalid_token" || state.type === "expired_token") {
        return (
            <div className="w-full max-w-md mx-auto">
                <AuthHeader />

                <div className="space-y-6">
                    <div className="text-center space-y-4">
                        <div className="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <AlertCircle className="w-8 h-8 text-red-600" />
                        </div>

                        <div className="space-y-2">
                            <h2 className="text-2xl font-semibold text-gray-900">
                                {state.type === "expired_token" ? "Reset link expired" : "Invalid reset link"}
                            </h2>
                            <p className="text-gray-600 text-sm leading-relaxed">
                                {state.message || "This password reset link is no longer valid."}
                            </p>
                        </div>
                    </div>

                    <div className="space-y-3">
                        <Button onClick={() => router.push("/forgot-password")} className="w-full">
                            Request new reset link
                        </Button>

                        <Button onClick={() => router.push("/signin")} variant="outline" className="w-full">
                            Back to sign in
                        </Button>
                    </div>
                </div>

                <ContactSection />
            </div>
        )
    }

    // Loading state while validating token
    if (tokenValid === null) {
        return (
            <div className="w-full max-w-md mx-auto">
                <AuthHeader />

                <div className="flex items-center justify-center py-12">
                    <div className="text-center space-y-4">
                        <Loader2 className="w-8 h-8 animate-spin mx-auto text-blue-600" />
                        <p className="text-gray-600">Validating reset link...</p>
                    </div>
                </div>
            </div>
        )
    }

    return (
        <div className="w-full max-w-md mx-auto">
            <AuthHeader />

            <div className="space-y-6">
                <div className="text-center space-y-2">
                    <h2 className="text-2xl font-semibold text-gray-900">Create new password</h2>
                    <p className="text-gray-600 text-sm leading-relaxed">Choose a strong password to secure your account.</p>
                </div>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="password" className="text-sm font-medium text-gray-700">
                            New password
                        </Label>
                        <div className="relative">
                            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                id="password"
                                type={showPassword ? "text" : "password"}
                                placeholder="Create a strong password"
                                className={`pl-10 pr-10 ${errors.password
                                    ? "border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50"
                                    : "border-gray-200 focus:border-blue-400 focus:ring-blue-400/20"
                                    }`}
                                {...register("password")}
                                disabled={isPending}
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                disabled={isPending}
                            >
                                {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                            </button>
                        </div>

                        {watchedPassword && (
                            <div className="mt-4 space-y-3">
                                {/* Password Strength Indicator */}
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-gray-700 font-medium">Password strength</span>
                                        <span
                                            className={`font-bold text-xs px-2 py-1 rounded-full ${passwordStrength.strength === "weak"
                                                ? "text-red-700 bg-red-100"
                                                : passwordStrength.strength === "medium"
                                                    ? "text-amber-700 bg-amber-100"
                                                    : "text-green-700 bg-green-100"
                                                }`}
                                        >
                                            {passwordStrength.strength === "weak"
                                                ? "Weak"
                                                : passwordStrength.strength === "medium"
                                                    ? "Good"
                                                    : "Strong"}
                                        </span>
                                    </div>
                                    <div className="flex gap-1">
                                        {[1, 2, 3, 4, 5].map((level) => (
                                            <div
                                                key={level}
                                                className={`h-2 flex-1 rounded-full transition-all duration-300 ${passwordStrength.score >= level
                                                    ? passwordStrength.strength === "weak"
                                                        ? "bg-red-400"
                                                        : passwordStrength.strength === "medium"
                                                            ? "bg-amber-400"
                                                            : "bg-green-400"
                                                    : "bg-gray-200"
                                                    }`}
                                            />
                                        ))}
                                    </div>
                                </div>

                                {/* Requirements Checklist */}
                                <div className="grid grid-cols-1 gap-2 p-3 bg-gray-50 rounded-lg border">
                                    {passwordStrength.requirements.map((req, index) => (
                                        <div
                                            key={index}
                                            className={`flex items-center gap-2 text-sm ${req.test ? "text-green-700" : "text-gray-600"}`}
                                        >
                                            <div
                                                className={`
                        flex items-center justify-center w-4 h-4 rounded-full text-xs
                        ${req.test ? "bg-green-100 text-green-600" : "bg-gray-100 text-gray-400"}
                      `}
                                            >
                                                {req.test ? <Check className="h-3 w-3" /> : <X className="h-3 w-3" />}
                                            </div>
                                            <span className={req.test ? "font-medium" : ""}>{req.label}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {errors.password && (
                            <p className="text-red-600 text-sm flex items-center gap-1">
                                <AlertCircle className="h-3 w-3 flex-shrink-0" />
                                {errors.password.message}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="confirmPassword" className="text-sm font-medium text-gray-700">
                            Confirm new password
                        </Label>
                        <div className="relative">
                            <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                id="confirmPassword"
                                type={showConfirmPassword ? "text" : "password"}
                                placeholder="Confirm your new password"
                                className={`pl-10 pr-10 ${errors.confirmPassword
                                    ? "border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50"
                                    : watchedConfirmPassword && watchedConfirmPassword === watchedPassword
                                        ? "border-green-300 focus:border-green-500 focus:ring-green-500/20 bg-green-50"
                                        : "border-gray-200 focus:border-blue-400 focus:ring-blue-400/20"
                                    }`}
                                {...register("confirmPassword")}
                                disabled={isPending}
                            />
                            <button
                                type="button"
                                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                disabled={isPending}
                            >
                                {showConfirmPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                            </button>
                        </div>

                        {watchedConfirmPassword && (
                            <div className="mt-2">
                                {watchedConfirmPassword === watchedPassword ? (
                                    <div className="flex items-center gap-2 text-green-700 bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                                        <Check className="h-4 w-4" />
                                        <span className="text-sm font-medium">Passwords match!</span>
                                    </div>
                                ) : (
                                    <div className="flex items-center gap-2 text-red-700 bg-red-50 px-3 py-2 rounded-lg border border-red-200">
                                        <X className="h-4 w-4" />
                                        <span className="text-sm font-medium">Passwords do not match</span>
                                    </div>
                                )}
                            </div>
                        )}

                        {errors.confirmPassword && (
                            <p className="text-red-600 text-sm flex items-center gap-1">
                                <AlertCircle className="h-3 w-3 flex-shrink-0" />
                                {errors.confirmPassword.message}
                            </p>
                        )}
                    </div>

                    {/* Error State */}
                    {state.type === "error" && (
                        <Alert className="border-red-200 bg-red-50">
                            <AlertCircle className="h-4 w-4 text-red-600" />
                            <AlertDescription className="text-red-800">{state.message}</AlertDescription>
                        </Alert>
                    )}

                    <Button type="submit" className="w-full" disabled={isPending || !isValid}>
                        {isPending ? (
                            <>
                                <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                Updating password...
                            </>
                        ) : (
                            <>
                                <Shield className="w-4 h-4 mr-2" />
                                Update password
                            </>
                        )}
                    </Button>

                    <div className="text-center">
                        <Link href="/signin" className="text-sm text-gray-600 hover:text-gray-900 hover:underline">
                            Back to sign in
                        </Link>
                    </div>
                </form>
            </div>

            <ContactSection />
        </div>
    )
}
