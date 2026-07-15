"use client"

import { useState, useTransition, useEffect } from "react"
import { useRouter } from "next/navigation"
import { useForm, useWatch } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Alert, AlertDescription } from "@/components/common/alert"

import { Lock, Eye, EyeOff, CheckCircle, AlertCircle, Shield, Loader2, Check, X } from "lucide-react"
import { ContactSection } from "@/components/signin/contact-section"
import { resetPassword, validateResetToken } from "@/actions/auth-actions"
import { cn } from "@/lib/utils"

const resetPasswordSchema = z
    .object({
        password: z
            .string()
            .min(8, "Password must be at least 8 characters")
            .max(128, "Password cannot exceed 128 characters"),
        confirmPassword: z.string().min(1, "Please confirm your password"),
    })
    .refine((data) => data.password === data.confirmPassword, {
        message: "Passwords do not match",
        path: ["confirmPassword"],
    })

type ResetPasswordInputs = z.infer<typeof resetPasswordSchema>

interface ResetPasswordFormProps {
    token: string
    email: string
}

export function ResetPasswordForm({ token, email }: ResetPasswordFormProps) {
    const router = useRouter()
    const [isPending, startTransition] = useTransition()
    const [state, setState] = useState<{ type: string; message?: string }>({ type: "idle" })
    const [showPassword, setShowPassword] = useState(false)
    const [showConfirmPassword, setShowConfirmPassword] = useState(false)
    const [tokenValid, setTokenValid] = useState<boolean | null>(null)

    const {
        register,
        handleSubmit,
        setError,
        clearErrors,
        formState: { isValid, errors },
        control,
    } = useForm<ResetPasswordInputs>({
        resolver: zodResolver(resetPasswordSchema),
        mode: "onChange",
    })

    const watchedPassword = useWatch({ control, name: "password", defaultValue: "" })
    const watchedConfirmPassword = useWatch({ control, name: "confirmPassword", defaultValue: "" })

    useEffect(() => {
        const checkToken = async () => {
            const result = await validateResetToken(token)
            setTokenValid(result.valid)
            if (!result.valid) {
                setState({
                    type: result.error === "EXPIRED" ? "expired_token" : "invalid_token",
                    message: result.message,
                })
            }
        }
        if (token) checkToken()
    }, [token])

    const getStrength = (pw: string) => {
        const reqs = [
            { test: pw.length >= 8, label: "8+ characters" },
            { test: /[A-Za-z]/.test(pw), label: "Letter" },
            { test: /[0-9]/.test(pw), label: "Number" },
            { test: /[^A-Za-z0-9]/.test(pw), label: "Special char" },
        ]
        const score = reqs.filter(r => r.test).length
        return { reqs, score, strength: score < 2 ? "weak" : score < 4 ? "medium" : "strong" }
    }

    const strengthData = getStrength(watchedPassword)

    const onSubmit = async (data: ResetPasswordInputs) => {
        startTransition(async () => {
            try {
                clearErrors()
                const result = await resetPassword(token, data.password, email)
                if (result.success) {
                    setState({ type: "success", message: result.message })
                } else {
                    const backendErrors = result.errors ?? {}
                    const passwordError =
                        backendErrors.password?.[0] ??
                        backendErrors.new_password?.[0]
                    const confirmationError =
                        backendErrors.password_confirmation?.[0] ??
                        backendErrors.new_password_confirmation?.[0]

                    if (passwordError) {
                        setError("password", {
                            type: "server",
                            message: passwordError.includes("password_reuse_not_allowed")
                                ? "New password must be different from your previous password."
                                : passwordError,
                        })
                    }
                    if (confirmationError) {
                        setError("confirmPassword", { type: "server", message: confirmationError })
                    }

                    setState({ type: "error", message: result.message })
                }
            } catch {
                setState({ type: "error", message: "Network error. Please try again." })
            }
        })
    }

    if (state.type === "success") {
        return (
            <div className="mx-auto w-full max-w-md space-y-6">
                <div className="text-center space-y-4">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-emerald-500/25 bg-emerald-500/10">
                        <CheckCircle className="h-8 w-8 text-emerald-600" />
                    </div>
                    <h2 className="text-3xl font-semibold tracking-tight text-foreground">Password reset successful</h2>
                    <p className="text-sm text-muted-foreground">You can now sign in with your new password.</p>
                </div>
                <Button onClick={() => router.push("/signin")} className="h-12 w-full">
                    <Shield className="w-4 h-4 mr-2" />
                    Continue to sign in
                </Button>
                <ContactSection />
            </div>
        )
    }

    if (tokenValid === false) {
        return (
            <div className="mx-auto w-full max-w-md space-y-6 text-center">
                <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-rose-500/25 bg-rose-500/10">
                    <AlertCircle className="h-8 w-8 text-rose-600" />
                </div>
                <h2 className="text-3xl font-semibold tracking-tight text-foreground">Invalid or expired link</h2>
                <p className="text-sm text-muted-foreground">{state.message || "Request a new link to continue."}</p>
                <Button onClick={() => router.push("/forgot-password")} className="h-12 w-full">Request new link</Button>
                <ContactSection />
            </div>
        )
    }

    if (tokenValid === null) return <div className="py-12 text-center"><Loader2 className="mx-auto animate-spin text-primary" /></div>

    return (
        <div className="mx-auto w-full max-w-md space-y-7">
            <div className="text-center space-y-2">
                <h2 className="text-3xl font-semibold tracking-tight text-foreground">Create new password</h2>
                <p className="text-sm text-muted-foreground">Choose a strong password to secure your account.</p>
            </div>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
                <div className="space-y-1.5">
                    <Label htmlFor="password" className="text-[11px] font-semibold tracking-wide text-slate-600">New password</Label>
                    <div className="relative">
                        <Lock className={cn("pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2", errors.password ? "text-rose-500" : "text-muted-foreground")} />
                        <Input
                            id="password"
                            type={showPassword ? "text" : "password"}
                            className={cn("h-12 bg-background/95 pl-10 pr-10", errors.password && "border-rose-400 focus-visible:border-rose-500 focus-visible:ring-rose-200")}
                            {...register("password")}
                            disabled={isPending}
                        />
                        <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-muted-foreground hover:text-foreground" aria-label={showPassword ? "Hide password" : "Show password"}>
                            {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                        </button>
                    </div>
                    {watchedPassword && (
                        <div className="space-y-2 rounded-xl border border-border/70 bg-secondary/60 p-3">
                            <div className="flex gap-1">
                                {[1, 2, 3, 4].map(i => (
                                    <div key={i} className={cn("h-1.5 flex-1 rounded-full", strengthData.score >= i ? "bg-primary" : "bg-border")} />
                                ))}
                            </div>
                            <div className="grid grid-cols-2 gap-x-4 gap-y-1">
                                {strengthData.reqs.map((r, i) => (
                                    <div key={i} className={cn("flex items-center gap-1 text-xs", r.test ? "text-emerald-600 dark:text-emerald-300" : "text-muted-foreground")}>
                                        {r.test ? <Check className="h-3 w-3" /> : <X className="h-3 w-3" />} {r.label}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                    {errors.password && (
                        <p className="text-xs font-medium text-rose-600">{errors.password.message}</p>
                    )}
                </div>

                <div className="space-y-1.5">
                    <Label htmlFor="confirmPassword" className="text-[11px] font-semibold tracking-wide text-slate-600">Confirm password</Label>
                    <div className="relative">
                        <Lock className={cn("pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2", errors.confirmPassword ? "text-rose-500" : "text-muted-foreground")} />
                        <Input
                            id="confirmPassword"
                            type={showConfirmPassword ? "text" : "password"}
                            className={cn("h-12 bg-background/95 pl-10 pr-10", errors.confirmPassword && "border-rose-400 focus-visible:border-rose-500 focus-visible:ring-rose-200")}
                            {...register("confirmPassword")}
                            disabled={isPending}
                        />
                        <button type="button" onClick={() => setShowConfirmPassword(!showConfirmPassword)} className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-muted-foreground hover:text-foreground" aria-label={showConfirmPassword ? "Hide password" : "Show password"}>
                            {showConfirmPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                        </button>
                    </div>
                    {watchedConfirmPassword && watchedConfirmPassword === watchedPassword && (
                        <p className="flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-300"><Check className="h-3 w-3" /> Passwords match</p>
                    )}
                    {errors.confirmPassword && (
                        <p className="text-xs font-medium text-rose-600">{errors.confirmPassword.message}</p>
                    )}
                </div>

                {state.type === "error" && <Alert variant="destructive"><AlertDescription>{state.message}</AlertDescription></Alert>}

                <Button type="submit" className="h-12 w-full rounded-xl text-sm font-semibold" disabled={isPending || !isValid}>
                    {isPending ? <Loader2 className="mr-2 animate-spin" /> : <Shield className="mr-2 h-4 w-4" />} Update password
                </Button>
            </form>
            <ContactSection />
        </div>
    )
}
