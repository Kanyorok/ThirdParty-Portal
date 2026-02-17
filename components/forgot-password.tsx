"use client"

import { useState } from "react"
import { useForm, useWatch } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { AlertCircle, ArrowLeft, CheckCircle, Clock, Loader2, Mail, Shield } from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Alert, AlertDescription } from "@/components/common/alert"
import { ContactSection } from "@/components/signin/contact-section"
import { requestPasswordReset, type AuthResult } from "@/actions/auth-actions"

const forgotPasswordSchema = z.object({
    email: z.string().email("Please enter a valid email address"),
})

type ForgotPasswordValues = z.infer<typeof forgotPasswordSchema>

export function ForgotPasswordForm() {
    const [isResending, setIsResending] = useState(false)
    const [state, setState] = useState<{
        type: "idle" | "success" | "error" | "rate_limited"
        message: string
        email?: string
    }>({
        type: "idle",
        message: "",
    })

    const router = useRouter()

    const {
        register,
        handleSubmit,
        control,
        formState: { errors, isSubmitting, isValid },
    } = useForm<ForgotPasswordValues>({
        resolver: zodResolver(forgotPasswordSchema),
        mode: "onChange",
    })

    const watchedEmail = useWatch({ control, name: "email" })

    const submitResetRequest = async (email: string) => {
        setState({ type: "idle", message: "" })
        const result: AuthResult = await requestPasswordReset(email)

        if (result.success) {
            setState({
                type: "success",
                message: result.message || "Reset link sent successfully.",
                email,
            })
            return
        }

        if (result.error === "RATE_LIMIT") {
            setState({
                type: "rate_limited",
                message: result.message || "Too many attempts. Please try again later.",
            })
            return
        }

        setState({
            type: "error",
            message: result.message || "An error occurred. Please try again.",
        })
    }

    const onSubmit = async (data: ForgotPasswordValues) => {
        try {
            await submitResetRequest(data.email)
        } catch {
            setState({
                type: "error",
                message: "Network error. Please check your connection and try again.",
            })
        }
    }

    const onResend = async () => {
        if (!state.email) return
        setIsResending(true)
        try {
            await submitResetRequest(state.email)
        } finally {
            setIsResending(false)
        }
    }

    if (state.type === "success") {
        return (
            <div className="w-full max-w-md mx-auto">
                <div className="space-y-6">
                    <div className="text-center space-y-4">
                        <div className="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                            <CheckCircle className="w-8 h-8 text-green-600" />
                        </div>
                        <div className="space-y-2">
                            <h2 className="text-2xl font-semibold text-gray-900">Check your email</h2>
                            <p className="text-gray-600 text-sm leading-relaxed">
                                {state.message || "We've sent password reset instructions to"}{" "}
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
                        <Button onClick={onResend} variant="outline" className="w-full" disabled={isSubmitting || isResending}>
                            {isResending ? (
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
                        <Button onClick={() => router.push("/signin")} variant="ghost" className="w-full">
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
            <div className="space-y-6">
                <div className="text-center space-y-2">
                    <h2 className="text-2xl font-semibold text-gray-900">Forgot your password?</h2>
                    <p className="text-gray-600 text-sm leading-relaxed">
                        No worries! Enter your email address and we'll send you instructions to reset your password.
                    </p>
                </div>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="email">Email address</Label>
                        <div className="relative">
                            <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                id="email"
                                type="email"
                                placeholder="Enter your email address"
                                className={`pl-10 ${errors.email ? "border-red-300 bg-red-50" : "border-gray-200"}`}
                                {...register("email")}
                                disabled={isSubmitting}
                            />
                        </div>
                        {errors.email && (
                            <p className="text-red-600 text-sm flex items-center gap-1">
                                <AlertCircle className="h-3 w-3" />
                                {errors.email.message}
                            </p>
                        )}
                    </div>

                    {state.type === "error" && (
                        <Alert variant="destructive">
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>{state.message}</AlertDescription>
                        </Alert>
                    )}

                    {state.type === "rate_limited" && (
                        <Alert className="border-amber-200 bg-amber-50">
                            <Clock className="h-4 w-4 text-amber-600" />
                            <AlertDescription className="text-amber-800">{state.message}</AlertDescription>
                        </Alert>
                    )}

                    <Button type="submit" className="w-full" disabled={isSubmitting || !isValid || !watchedEmail?.trim()}>
                        {isSubmitting ? (
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
                        <Link href="/signin" className="text-sm text-gray-600 hover:text-gray-900 hover:underline inline-flex items-center gap-1">
                            <ArrowLeft className="w-3 h-3" />
                            Back to sign in
                        </Link>
                    </div>
                </form>

                <Alert className="border-gray-200 bg-gray-50">
                    <Shield className="h-4 w-4 text-gray-600" />
                    <AlertDescription className="text-gray-700">
                        <div className="space-y-1">
                            <p className="font-medium text-sm">Security Notice</p>
                            <p className="text-xs">
                                For security reasons, we'll send reset instructions regardless of whether the email exists. Links expire after 15 minutes.
                            </p>
                        </div>
                    </AlertDescription>
                </Alert>
            </div>
            <ContactSection />
        </div>
    )
}
