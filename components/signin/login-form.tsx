"use client"

import * as React from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useForm, useWatch } from "react-hook-form"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { Mail, Lock, Loader2, AlertCircle, Eye, X, EyeClosed } from "lucide-react"
import { signIn } from "next-auth/react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { cn } from "@/lib/utils"

const schema = z.object({
    email: z.string().min(1, "Email is required").email("Enter a valid Email address"),
    password: z.string().min(1, "Password is required"),
})

type FormValues = z.infer<typeof schema>

export default function LoginPage() {
    const router = useRouter()
    const [authError, setAuthError] = React.useState<string | null>(null)
    const [showPassword, setShowPassword] = React.useState(false)

    const {
        register,
        handleSubmit,
        setValue,
        control,
        formState: {
            errors,
            touchedFields,
            isSubmitted,
            isSubmitting
        },
    } = useForm<FormValues>({
        resolver: zodResolver(schema),
        mode: "onBlur",
    })

    const emailValue = useWatch({ control, name: "email" })
    const showError = (field: keyof FormValues) =>
        (touchedFields[field] || isSubmitted) && errors[field]

    const onSubmit = async (data: FormValues) => {
        setAuthError(null)

        try {
            const result = await signIn("credentials", {
                email: data.email,
                password: data.password,
                redirect: false,
            })

            if (result?.error) {
                if (result.error === "EMAIL_NOT_VERIFIED") {
                    router.push(`/verify-email/expired?email=${encodeURIComponent(data.email)}`)
                    return
                }

                setAuthError("Email not verified!")
                return
            }

            router.push("/dashboard")
        } catch {
            setAuthError("An unexpected error occurred. Refresh or try again later.")
        }
    }

    return (
        <main className="min-h-[100dvh] px-4 sm:px-6 lg:px-8">
            <div className="mx-auto flex min-h-[100dvh] w-full max-w-7xl items-center justify-center py-8 sm:py-10">
                <div className="mx-auto w-full max-w-[620px] space-y-8">
                    <header className="space-y-3 text-center">
                        <h1 className="text-4xl font-semibold tracking-tight text-foreground sm:text-5xl">Welcome back</h1>
                        <p className="text-sm font-medium text-muted-foreground">Sign in to continue to your portal.</p>
                    </header>

                    <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
                        {authError && (
                            <div className="flex items-start gap-2 rounded-xl border border-rose-300/70 bg-rose-50/90 px-3.5 py-3 text-rose-700">
                                <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
                                <p className="text-sm font-medium">{authError}</p>
                            </div>
                        )}

                        <div className="space-y-1.5">
                            <label htmlFor="email" className="text-[11px] font-semibold tracking-wide text-slate-600">Email</label>
                            <div className="relative">
                                <Mail className={cn("pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2", showError("email") ? "text-rose-500" : "text-muted-foreground")} />
                                <Input
                                    id="email"
                                    type="email"
                                    {...register("email")}
                                    placeholder="youremail@gmail.com"
                                    disabled={isSubmitting}
                                    className={cn("h-12 bg-transparent pl-10 pr-10", showError("email") && "border-rose-400 focus-visible:border-rose-500 focus-visible:ring-rose-200")}
                                />
                                {emailValue && (
                                    <button
                                        type="button"
                                        onClick={() => setValue("email", "", { shouldValidate: true })}
                                        className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-muted-foreground hover:text-foreground"
                                        aria-label="Clear email"
                                    >
                                        <X className="h-4 w-4" />
                                    </button>
                                )}
                            </div>
                            {showError("email") && <p className="text-xs font-medium text-rose-600">{errors.email?.message}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <label htmlFor="password" className="text-[11px] font-semibold tracking-wide text-slate-600">Password</label>
                            <div className="relative">
                                <Lock className={cn("pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2", showError("password") ? "text-rose-500" : "text-muted-foreground")} />
                                <Input
                                    id="password"
                                    type={showPassword ? "text" : "password"}
                                    {...register("password")}
                                    placeholder="Enter your password"
                                    disabled={isSubmitting}
                                    className={cn("h-12 bg-transparent pl-10 pr-10", showError("password") && "border-rose-400 focus-visible:border-rose-500 focus-visible:ring-rose-200")}
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-muted-foreground hover:text-foreground"
                                    aria-label={showPassword ? "Hide password" : "Show password"}
                                >
                                    {showPassword ? <EyeClosed className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                </button>
                            </div>
                            {showError("password") && <p className="text-xs font-medium text-rose-600">{errors.password?.message}</p>}
                        </div>

                        <Button type="submit" disabled={isSubmitting} className="h-12 w-full rounded-xl text-sm font-semibold">
                            {isSubmitting ? (
                                <>
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                    Signing in...
                                </>
                            ) : (
                                "Sign in"
                            )}
                        </Button>
                    </form>

                    <div className="space-y-4 text-center">
                        <Link href="/forgot-password" className="text-sm font-medium text-primary hover:text-[var(--primary-hover)]">
                            Forgot your password?
                        </Link>
                        <p className="text-sm text-muted-foreground">
                            Don&apos;t have an account?{" "}
                            <Link href="/signup" className="font-semibold text-primary hover:text-[var(--primary-hover)]">
                                Sign up
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </main>
    )
}
