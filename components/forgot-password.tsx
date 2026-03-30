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
import { Spinner } from "@/components/common/spinner"
import { ContactSection } from "@/components/signin/contact-section"
import { requestPasswordReset, type AuthResult } from "@/actions/auth-actions"
import { cn } from "@/lib/utils"

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
                        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-emerald-500/25 bg-emerald-500/10">
                            <CheckCircle className="h-8 w-8 text-emerald-600" />
                        </div>
                        <div className="space-y-2">
                            <h2 className="text-3xl font-semibold tracking-tight text-foreground">Check your email</h2>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                {state.message || "We've sent password reset instructions to"}{" "}
                                <span className="font-semibold text-foreground">{state.email}</span>
                            </p>
                        </div>
                    </div>

                    <Alert className="border-primary/20 bg-primary/5">
                        <Mail className="h-4 w-4 text-primary" />
                        <AlertDescription className="text-foreground">
                            <div className="space-y-2">
                                <p className="text-sm font-semibold">What's next?</p>
                                <ul className="ml-4 space-y-1 text-sm text-muted-foreground">
                                    <li>• Check your email inbox (and spam folder)</li>
                                    <li>• Click the reset link within 15 minutes</li>
                                    <li>• Create a new secure password</li>
                                </ul>
                            </div>
                        </AlertDescription>
                    </Alert>

                    <div className="space-y-4">
                        <Button onClick={onResend} variant="outline" className="h-12 w-full rounded-xl text-sm font-semibold" disabled={isSubmitting || isResending}>
                            {isResending ? (
                                <>
                                    <Spinner className="mr-2 h-4 w-4" />
                                    Sending email
                                </>
                            ) : (
                                <>
                                    <Mail className="w-4 h-4 mr-2" />
                                    Resend email
                                </>
                            )}
                        </Button>
                        <Button onClick={() => router.push("/signin")} variant="ghost" className="h-12 w-full rounded-xl text-sm font-semibold">
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
            <div className="space-y-7">
                <div className="space-y-2 text-center">
                    <h2 className="text-3xl font-semibold tracking-tight text-foreground">Forgot your password?</h2>
                    <p className="text-sm leading-relaxed text-muted-foreground">
                        No worries! Enter your email address and we'll send you instructions to reset your password.
                    </p>
                </div>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
                    <div className="space-y-1.5">
                        <Label htmlFor="email" className="text-[11px] font-semibold tracking-wide text-slate-600">Email address</Label>
                        <div className="relative">
                            <Mail className={cn("pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2", errors.email ? "text-rose-500" : "text-muted-foreground")} />
                            <Input
                                id="email"
                                type="email"
                                placeholder="name@company.com"
                                className={cn("h-12 bg-background/95 pl-10", errors.email && "border-rose-400 focus-visible:border-rose-500 focus-visible:ring-rose-200")}
                                {...register("email")}
                                disabled={isSubmitting}
                            />
                        </div>
                        {errors.email && (
                            <p className="flex items-center gap-1 text-xs font-medium text-rose-600">
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
                        <Alert className="border-amber-500/25 bg-amber-500/10 text-amber-800 dark:text-amber-200">
                            <Clock className="h-4 w-4 text-amber-600 dark:text-amber-200" />
                            <AlertDescription className="text-amber-800 dark:text-amber-200">{state.message}</AlertDescription>
                        </Alert>
                    )}

                    <Button type="submit" className="h-12 w-full rounded-xl text-sm font-semibold" disabled={isSubmitting || !isValid || !watchedEmail?.trim()}>
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
                        <Link href="/signin" className="inline-flex items-center gap-1 text-sm font-medium text-muted-foreground hover:text-foreground hover:underline">
                            <ArrowLeft className="w-3 h-3" />
                            Back to sign in
                        </Link>
                    </div>
                </form>

                <Alert className="border-border/70 bg-secondary/55">
                    <Shield className="h-4 w-4 text-muted-foreground" />
                    <AlertDescription className="text-muted-foreground">
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">Security notice</p>
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
