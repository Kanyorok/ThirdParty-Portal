"use client"

import { useState, useTransition } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Alert, AlertDescription } from "@/components/common/alert"


import { Mail, ArrowLeft, CheckCircle, AlertCircle, Clock, Shield, Loader2 } from "lucide-react"
import { ContactSection } from "./common/contact-us"
import { AuthHeader } from "./layout/auth-header"
import { requestPasswordReset } from "@/actions/auth-actions"


const forgotPasswordSchema = z.object({
    email: z
        .string()
        .min(1, "Email address is required")
        .email("Please enter a valid email address")
        .max(100, "Email address is too long")
        .toLowerCase()
        .trim(),
})

type ForgotPasswordInputs = z.infer<typeof forgotPasswordSchema>

interface ForgotPasswordState {
    type: "idle" | "loading" | "success" | "error" | "rate_limited"
    message?: string
    email?: string
}

export function ForgotPasswordForm() {
    const router = useRouter()
    const [isPending, startTransition] = useTransition()
    const [state, setState] = useState<ForgotPasswordState>({ type: "idle" })

    const {
        register,
        handleSubmit,
        formState: { errors, isValid },
        watch,
        setError,
    } = useForm<ForgotPasswordInputs>({
        resolver: zodResolver(forgotPasswordSchema),
        mode: "onChange",
        defaultValues: {
            email: "",
        },
    })

    const watchedEmail = watch("email")

    const onSubmit = async (data: ForgotPasswordInputs) => {
        startTransition(async () => {
            setState({ type: "loading" })

            try {
                const result = await requestPasswordReset(data.email)

                if (result.success) {
                    setState({
                        type: "success",
                        message: result.message,
                        email: data.email,
                    })
                } else {
                    if (result.error === "RATE_LIMITED") {
                        setState({
                            type: "rate_limited",
                            message: result.message,
                        })
                    } else if (result.error === "VALIDATION_ERROR") {
                        setError("email", {
                            type: "server",
                            message: result.message || "Invalid email address",
                        })
                        setState({ type: "idle" })
                    } else {
                        setState({
                            type: "error",
                            message: result.message || "Something went wrong. Please try again.",
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

    const handleResendEmail = () => {
        if (state.email) {
            onSubmit({ email: state.email })
        }
    }

    const handleBackToSignIn = () => {
        router.push("/signin")
    }

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
                            <h2 className="text-2xl font-semibold text-gray-900">Check your email</h2>
                            <p className="text-gray-600 text-sm leading-relaxed">
                                We've sent password reset instructions to{" "}
                                <span className="font-medium text-gray-900">{state.email}</span>
                            </p>
                        </div>
                    </div>

                    <Alert className="border-blue-200 bg-blue-50">
                        <Mail className="h-4 w-4 text-blue-600" />
                        <AlertDescription className="text-blue-800">
                            <div className="space-y-2">
                                <p className="font-medium">What's next?</p>
                                <ul className="text-sm space-y-1 ml-4">
                                    <li>• Check your email inbox (and spam folder)</li>
                                    <li>• Click the reset link within 15 minutes</li>
                                    <li>• Create a new secure password</li>
                                </ul>
                            </div>
                        </AlertDescription>
                    </Alert>

                    <div className="space-y-4">
                        <Button onClick={handleResendEmail} variant="outline" className="w-full" disabled={isPending}>
                            {isPending ? (
                                <>
                                    <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                    Sending...
                                </>
                            ) : (
                                <>
                                    <Mail className="w-4 h-4 mr-2" />
                                    Resend email
                                </>
                            )}
                        </Button>

                        <Button onClick={handleBackToSignIn} variant="ghost" className="w-full">
                            <ArrowLeft className="w-4 h-4 mr-2" />
                            Back to sign in
                        </Button>
                    </div>
                </div>

                <ContactSection />
            </div>
        )
    }

    return (
        <div className="w-full max-w-md mx-auto">
            <AuthHeader />

            <div className="space-y-6">
                <div className="text-center space-y-2">
                    <h2 className="text-2xl font-semibold text-gray-900">Forgot your password?</h2>
                    <p className="text-gray-600 text-sm leading-relaxed">
                        No worries! Enter your email address and we'll send you instructions to reset your password.
                    </p>
                </div>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="email" className="text-sm font-medium text-gray-700">
                            Email address
                        </Label>
                        <div className="relative">
                            <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                id="email"
                                type="email"
                                placeholder="Enter your email address"
                                className={`pl-10 ${errors.email
                                    ? "border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50"
                                    : "border-gray-200 focus:border-blue-400 focus:ring-blue-400/20"
                                    }`}
                                {...register("email")}
                                disabled={isPending}
                            />
                        </div>
                        {errors.email && (
                            <p className="text-red-600 text-sm flex items-center gap-1">
                                <AlertCircle className="h-3 w-3 flex-shrink-0" />
                                {errors.email.message}
                            </p>
                        )}
                    </div>

                    {/* Error States */}
                    {state.type === "error" && (
                        <Alert className="border-red-200 bg-red-50">
                            <AlertCircle className="h-4 w-4 text-red-600" />
                            <AlertDescription className="text-red-800">{state.message}</AlertDescription>
                        </Alert>
                    )}

                    {state.type === "rate_limited" && (
                        <Alert className="border-amber-200 bg-amber-50">
                            <Clock className="h-4 w-4 text-amber-600" />
                            <AlertDescription className="text-amber-800">
                                <div className="space-y-2">
                                    <p className="font-medium">Too many requests</p>
                                    <p className="text-sm">{state.message}</p>
                                </div>
                            </AlertDescription>
                        </Alert>
                    )}

                    <Button type="submit" className="w-full" disabled={isPending || !isValid || !watchedEmail.trim()}>
                        {isPending ? (
                            <>
                                <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                                Sending reset link...
                            </>
                        ) : (
                            <>
                                <Shield className="w-4 h-4 mr-2" />
                                Send reset link
                            </>
                        )}
                    </Button>

                    <div className="text-center">
                        <Link
                            href="/signin"
                            className="text-sm text-gray-600 hover:text-gray-900 hover:underline inline-flex items-center gap-1"
                        >
                            <ArrowLeft className="w-3 h-3" />
                            Back to sign in
                        </Link>
                    </div>
                </form>

                {/* Security Notice */}
                <Alert className="border-gray-200 bg-gray-50">
                    <Shield className="h-4 w-4 text-gray-600" />
                    <AlertDescription className="text-gray-700">
                        <div className="space-y-1">
                            <p className="font-medium text-sm">Security Notice</p>
                            <p className="text-xs">
                                For security reasons, we'll send reset instructions regardless of whether the email exists in our
                                system. Reset links expire after 15 minutes.
                            </p>
                        </div>
                    </AlertDescription>
                </Alert>
            </div>

            <ContactSection />
        </div>
    )
}
