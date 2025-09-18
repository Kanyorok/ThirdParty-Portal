"use client"

import Link from "next/link"
import { useRouter, useSearchParams } from "next/navigation"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { AuthHeader } from "@/components/layout/auth-header"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import React, { useEffect, useState, Suspense } from "react"
import { Check, AlertCircle, Loader2, Sun, Moon } from "lucide-react"
import { toast } from "sonner"
import { signIn } from "next-auth/react"
import { FormField } from "@/components/signin/form-fields/login-fields"
import { PasswordField } from "@/components/signin/form-fields/pwd"
import { useTheme } from "next-themes"

// Zod schema for form validation
const signInSchema = z.object({
    email: z.string().email("Please enter a valid email address."),
    password: z.string().min(1, "Password is required."),
})

type SignInFormInputs = z.infer<typeof signInSchema>

// Theme Toggle Component
function ThemeToggle() {
    const { theme, setTheme } = useTheme()
    const isDark = theme === "dark"

    const toggleTheme = () => {
        setTheme(isDark ? "light" : "dark")
    }

    return (
        <button
            type="button"
            onClick={toggleTheme}
            aria-label="Toggle theme"
            className="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
        >
            {isDark ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
        </button>
    )
}

function SignInFormComponent() {
    const router = useRouter()
    const searchParams = useSearchParams()
    const callbackUrl = searchParams.get("callbackUrl") || "/dashboard"
    const error = searchParams.get("error")

    const [showPassword, setShowPassword] = useState(false)
    const [showSuccessMessage, setShowSuccessMessage] = useState(false)
    const [authError, setAuthError] = useState<string | null>(null)

    // Handle authentication errors from URL params
    useEffect(() => {
        if (error) {
            switch (error) {
                case 'SessionExpired':
                    setAuthError('Your session has expired. Please sign in again.')
                    break
                case 'SessionRequired':
                    setAuthError('Please sign in to access this page.')
                    break
                case 'AccountNotApproved':
                    setAuthError('Your account is not approved or active. Please contact support.')
                    break
                case 'NoAccessToken':
                    setAuthError('Authentication error. Please sign in again.')
                    break
                case 'Configuration':
                    setAuthError('Authentication configuration error. Please try again.')
                    break
                case 'CredentialsSignin':
                    setAuthError('Invalid email or password. Please try again.')
                    break
                default:
                    setAuthError('Authentication error. Please try again.')
            }
        }
    }, [error])

    const {
        register,
        handleSubmit,
        formState: { errors, isSubmitting, touchedFields },
        setError,
        watch,
    } = useForm<SignInFormInputs>({
        resolver: zodResolver(signInSchema),
        mode: "onTouched",
    })

    // Clear auth error when user starts typing
    const watchedFields = watch(['email', 'password'])
    useEffect(() => {
        if (authError && (watchedFields[0] || watchedFields[1])) {
            setAuthError(null)
        }
    }, [watchedFields, authError])

    const watchedPassword = watch("password")

    useEffect(() => {
        if (searchParams.get("registrationSuccess") === "true") {
            setShowSuccessMessage(true)
            const timer = setTimeout(() => setShowSuccessMessage(false), 5000)
            return () => clearTimeout(timer)
        }
    }, [searchParams])

    const onSubmit = async (data: SignInFormInputs) => {
        setAuthError(null) // Clear any previous auth errors
        try {
            const result = await signIn("credentials", {
                redirect: false,
                email: data.email,
                password: data.password,
                callbackUrl: callbackUrl,
            })

            if (result?.error) {
                // Expect tagged format CODE: message
                const raw = result.error
                let code = "UNKNOWN"
                let message = raw
                const idx = raw.indexOf(":")
                if (idx > -1) {
                    code = raw.slice(0, idx).trim()
                    message = raw.slice(idx + 1).trim()
                } else if (raw === "CredentialsSignin") {
                    code = "INVALID_CREDENTIALS"
                    message = "Invalid email or password"
                }
                const friendly = (() => {
                    switch (code) {
                        case "INVALID_CREDENTIALS":
                            return "Invalid email or password. Please try again."
                        case "ACCOUNT_NOT_APPROVED":
                            return message || "Your account is pending approval."
                        case "VALIDATION":
                            return message || "Please correct the highlighted fields."
                        case "NETWORK":
                            return "Network issue. Please retry."
                        case "SERVER_ERROR":
                            return message || "Server error. Please try again later."
                        case "MISSING_FIELDS":
                            return message || "Email and password are required."
                        default:
                            return message || "An unexpected error occurred. Please try again."
                    }
                })()
                setError("root", { type: code, message: friendly })
            } else if (result?.ok) {
                // Show toast then navigate
                toast.success("Successfully Signed In", {
                    description: "Redirecting to dashboard...",
                })
                // Allow toast to paint before navigation
                setTimeout(() => router.push(result.url || callbackUrl), 300)
            }
        } catch (error) {
            setError("root", {
                type: "manual",
                message: "A network error occurred. Please check your connection.",
            })
        }
    }

    const fieldStatuses = {
        email: errors.email ? "error" : touchedFields.email ? "success" : "default",
        password: errors.password ? "error" : touchedFields.password ? "success" : "default",
    }

    return (
        <div className="flex min-h-screen items-center justify-center p-4">
            <div className="relative w-full max-w-xl bg-gray-50 dark:bg-zinc-900 rounded-xl p-8 sm:p-10 border border-gray-100 dark:border-zinc-800">
                <AuthHeader isRegistration={false} />
                {showSuccessMessage && (
                    <div role="alert" aria-live="polite" className="p-4 mb-6 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg flex items-center justify-center">
                        <Check className="h-5 w-5 mr-2" />
                        Registration successful! Please log in.
                    </div>
                )}
                {authError && (
                    <div role="alert" aria-live="polite" className="p-4 mb-6 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg flex items-center">
                        <AlertCircle className="h-5 w-5 mr-2 flex-shrink-0" />
                        <span>{authError}</span>
                    </div>
                )}
                <form className="space-y-6 mt-8" onSubmit={handleSubmit(onSubmit)}>
                    <FormField
                        status={fieldStatuses.email}
                        label="Email Address"
                        required
                        error={errors.email?.message}
                        id="email"
                    >
                        <Input
                            id="email"
                            type="email"
                            placeholder="john.doe@example.com"
                            className={`w-full py-4 px-4 text-base border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 ${errors.email
                                ? "border-red-300 bg-red-50 dark:bg-red-900 dark:border-red-700"
                                : fieldStatuses.email === "success"
                                    ? "border-green-300 bg-green-50 dark:bg-green-900 dark:border-green-700"
                                    : "border-gray-200 hover:border-gray-300 dark:border-zinc-700 dark:hover:border-zinc-600"
                                }`}
                            {...register("email")}
                            aria-invalid={!!errors.email}
                            aria-describedby={errors.email ? "email-error" : undefined}
                        />
                    </FormField>

                    <PasswordField
                        id="password"
                        label="Password"
                        placeholder="Enter your password"
                        value={watchedPassword || ""}
                        error={errors.password?.message}
                        status={fieldStatuses.password}
                        showPassword={showPassword}
                        onTogglePassword={() => setShowPassword(!showPassword)}
                        register={register("password")}
                    />

                    <div className="flex items-center justify-end">
                        <Link href="/forgot-password" className="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-600">
                            Forgot password?
                        </Link>
                    </div>
                    {errors.root && (
                        <div role="alert" aria-live="polite" className="p-3 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg">
                            <p className="text-red-800 dark:text-red-200 text-sm font-medium flex items-center">
                                <AlertCircle className="h-4 w-4 mr-2" /> {errors.root.message}
                            </p>
                        </div>
                    )}
                    <Button type="submit" className="w-full" disabled={isSubmitting}>
                        {isSubmitting ? (
                            <div className="flex items-center justify-center">
                                <Loader2 className="animate-spin h-5 w-5 text-white mr-3" />
                                Signing In...
                            </div>
                        ) : (
                            "Sign In"
                        )}
                    </Button>
                    <div className="text-center pt-4 text-base text-gray-600 dark:text-gray-400">
                        Don't have an account?{" "}
                        <Link
                            href="/signup"
                            className="text-blue-600 dark:text-blue-400 hover:underline font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded"
                        >
                            Create Account
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    )
}

export default function SignInPage() {
    return (
        <Suspense>
            <SignInFormComponent />
        </Suspense>
    )
}