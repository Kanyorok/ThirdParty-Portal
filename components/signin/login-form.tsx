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
import { Check, AlertCircle, Loader2 } from "lucide-react"
import { toast } from "sonner"
import { signIn } from "next-auth/react"
import { FormField } from "@/components/signin/form-fields/login-fields"
import { PasswordField } from "@/components/signin/form-fields/pwd"

const signInSchema = z.object({
    profile_type: z.enum(["Supplier", "Tenant", "Customer"], {
        required_error: "Please select a profile type.",
    }),
    email: z.string().email("Please enter a valid email address."),
    password: z.string().min(1, "Password is required."),
})

type SignInFormInputs = z.infer<typeof signInSchema>

function SignInFormComponent() {
    const router = useRouter()
    const searchParams = useSearchParams()
    const callbackUrl = searchParams.get("callbackUrl") || "/dashboard"
    const error = searchParams.get("error")

    const [showPassword, setShowPassword] = useState(false)
    const [showSuccessMessage, setShowSuccessMessage] = useState(false)
    const [authError, setAuthError] = useState<string | null>(null)

    useEffect(() => {
        if (error) {
            switch (error) {
                case 'SessionExpired':
                case 'SessionRequired':
                    setAuthError('Your session has expired or is required. Please sign in again.')
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
        setValue,
    } = useForm<SignInFormInputs>({
        resolver: zodResolver(signInSchema),
        mode: "onTouched",
        defaultValues: {
            profile_type: "Supplier",
        },
    })

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
        setAuthError(null)
        try {
            const result = await signIn("credentials", {
                redirect: false,
                email: data.email,
                password: data.password,
                profile_type: data.profile_type,
                callbackUrl: callbackUrl,
            })

            if (result?.error) {
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
                toast.success("Successfully Signed In", {
                    description: "Redirecting...",
                })

                // Allow toast to paint/session to settle before navigation
                // We fetch the session here to check if they need to complete their profile
                const { getSession } = await import("next-auth/react")
                const session = await getSession()

                setTimeout(() => {
                    if (session?.user && !session.user.thirdPartyId) {
                        // User has account but no Third Party Profile -> Redirect to Step 2
                        router.push(`/third-party-details?user_id=${session.user.id}`)
                    } else {
                        router.push(result.url || callbackUrl)
                    }
                }, 300)
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
            <div className="relative w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl rounded-xl p-8 sm:p-12 transition-all duration-300">
                <AuthHeader isRegistration={false} />
                <h1 className="text-3xl font-bold text-center text-gray-900 dark:text-white mt-6 mb-2">
                    Welcome Back
                </h1>
                <p className="text-center text-gray-500 dark:text-gray-400 mb-8">
                    Sign in to your account to continue.
                </p>

                {showSuccessMessage && (
                    <div role="alert" aria-live="polite" className="p-4 mb-6 bg-green-50 dark:bg-green-700/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg flex items-center shadow-md">
                        <Check className="h-5 w-5 mr-3 flex-shrink-0 text-green-600 dark:text-green-400" />
                        Registration successful! Please log in.
                    </div>
                )}
                {authError && (
                    <div role="alert" aria-live="polite" className="p-4 mb-6 bg-red-50 dark:bg-red-700/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg flex items-center shadow-md">
                        <AlertCircle className="h-5 w-5 mr-3 flex-shrink-0 text-red-600 dark:text-red-400" />
                        <span>{authError}</span>
                    </div>
                )}
                <form className="space-y-6 mt-8" onSubmit={handleSubmit(onSubmit)}>
                    <FormField
                        status={errors.profile_type ? "error" : "default"}
                        label="Profile Type"
                        required
                        error={errors.profile_type?.message}
                        id="profile_type"
                    >
                        <select
                            id="profile_type"
                            className={`w-full py-4 px-4 text-base border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white dark:bg-zinc-900 ${errors.profile_type
                                ? "border-red-300 dark:border-red-700"
                                : "border-gray-200 hover:border-gray-300 dark:border-zinc-700 dark:hover:border-zinc-600"
                                }`}
                            {...register("profile_type")}
                        >
                            <option value="Supplier">Supplier</option>
                            <option value="Tenant">Tenant</option>
                            <option value="Customer">Customer</option>
                        </select>
                    </FormField>
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
                            placeholder="you@company.com"
                            className={`w-full h-12 px-4 text-base border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-indigo-500 ${errors.email
                                ? "border-red-500 bg-red-50 dark:bg-red-900/20 dark:border-red-600"
                                : fieldStatuses.email === "success"
                                    ? "border-green-500 dark:border-green-600"
                                    : "border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                }`}
                            {...register("email")}
                            aria-invalid={!!errors.email}
                            aria-describedby={errors.email ? "email-error" : undefined}
                        />
                    </FormField>

                    <PasswordField
                        id="password"
                        label="Password"
                        placeholder="••••••••"
                        value={watchedPassword || ""}
                        error={errors.password?.message}
                        status={fieldStatuses.password}
                        showPassword={showPassword}
                        onTogglePassword={() => setShowPassword(!showPassword)}
                        register={register("password")}
                        inputClassName={`h-12 px-4 text-base border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-indigo-500 ${errors.password
                            ? "border-red-500 bg-red-50 dark:bg-red-900/20 dark:border-red-600"
                            : fieldStatuses.password === "success"
                                ? "border-green-500 dark:border-green-600"
                                : "border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                            }`}
                    />

                    <div className="flex items-center justify-between pt-1">
                        <span className="sr-only">Remember me (optional feature)</span>
                        <Link href="/forgot-password" className="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                            Forgot password?
                        </Link>
                    </div>

                    {errors.root && (
                        <div role="alert" aria-live="polite" className="p-3 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-lg shadow-sm">
                            <p className="text-red-800 dark:text-red-300 text-sm font-medium flex items-center">
                                <AlertCircle className="h-4 w-4 mr-2 flex-shrink-0" /> {errors.root.message}
                            </p>
                        </div>
                    )}

                    <Button type="submit" className="w-full h-12 text-lg font-semibold bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/50 transition-colors" disabled={isSubmitting}>
                        {isSubmitting ? (
                            <div className="flex items-center justify-center">
                                <Loader2 className="animate-spin h-5 w-5 mr-3" />
                                Signing In...
                            </div>
                        ) : (
                            "Sign In"
                        )}
                    </Button>

                    <div className="text-center pt-4 text-sm text-gray-500 dark:text-gray-400">
                        Don't have an account?{" "}
                        <Link
                            href="/signup"
                            className="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
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