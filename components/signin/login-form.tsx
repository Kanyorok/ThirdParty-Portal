"use client"

import * as React from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useForm, useWatch } from "react-hook-form"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"
import { Mail, Lock, Loader2, AlertCircle, Eye, EyeOff, X } from "lucide-react"
import { signIn } from "next-auth/react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"

function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

const schema = z.object({
    email: z.string().min(1, "Email is required").email("Please enter a valid email address"),
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
                setAuthError("Invalid email or password. Please try again.")
            } else {
                router.push("/dashboard")
            }
        } catch {
            setAuthError("An unexpected error occurred. Please try again later.")
        }
    }

    return (
        <div className="min-h-screen w-full flex flex-col items-center justify-center p-6 font-sans relative overflow-hidden">
            <div className="absolute top-[-5%] left-[-5%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none" />
            <div className="absolute bottom-[-5%] right-[-5%] w-[40%] h-[40%] bg-chart-2/5 rounded-full blur-[120px] pointer-events-none" />

            <div className="w-full max-w-[440px] z-10 space-y-10">
                <div className="text-center space-y-4">
                    <h1 className="text-4xl font-black tracking-tighter text-foreground inline-flex items-center gap-0.5">
                        <span>Craft Silicon</span>
                    </h1>

                    <div className="space-y-2 pt-2">
                        <p className="text-3xl font-bold tracking-tight text-primary">
                            Welcome back
                        </p>
                        <p className="text-sm font-medium text-muted-foreground leading-relaxed max-w-[340px] mx-auto">
                            Use your Email Address and password to log in to your account.
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                    {authError && (
                        <div className="p-4 bg-destructive/10 border border-destructive/20 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-1 duration-300">
                            <AlertCircle className="h-4 w-4 text-destructive shrink-0" />
                            <p className="text-xs font-semibold text-destructive">{authError}</p>
                        </div>
                    )}

                    <div className="space-y-2">
                        <div className="relative group">
                            <Input
                                id="email"
                                {...register("email")}
                                placeholder="Email Address"
                                disabled={isSubmitting}
                                className={cn(
                                    "h-14 bg-card rounded-xl pl-12 pr-10 transition-all duration-200 border-input shadow-sm text-base",
                                    showError("email")
                                        ? "border-destructive focus-visible:ring-destructive/20"
                                        : "focus-visible:ring-primary/10 focus-visible:border-primary"
                                )}
                            />
                            <Mail className={cn(
                                "absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 transition-colors",
                                showError("email") ? "text-destructive/60" : "text-muted-foreground/40 group-focus-within:text-primary"
                            )} />

                            {emailValue && (
                                <button
                                    type="button"
                                    onClick={() => setValue("email", "", { shouldValidate: true })}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground/30 hover:text-foreground transition-colors p-1"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            )}
                        </div>
                        {showError("email") && (
                            <p className="text-[11px] font-bold text-destructive uppercase tracking-wide ml-2">
                                {errors.email?.message}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <div className="relative group">
                            <Input
                                id="password"
                                type={showPassword ? "text" : "password"}
                                {...register("password")}
                                placeholder="Password"
                                disabled={isSubmitting}
                                className={cn(
                                    "h-14 bg-card rounded-xl pl-12 pr-10 transition-all duration-200 border-input shadow-sm text-base",
                                    showError("password")
                                        ? "border-destructive focus-visible:ring-destructive/20"
                                        : "focus-visible:ring-primary/10 focus-visible:border-primary"
                                )}
                            />
                            <Lock className={cn(
                                "absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 transition-colors",
                                showError("password") ? "text-destructive/60" : "text-muted-foreground/40 group-focus-within:text-primary"
                            )} />

                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground/30 hover:text-foreground transition-colors p-1"
                            >
                                {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                            </button>
                        </div>
                        {showError("password") && (
                            <p className="text-[11px] font-bold text-destructive uppercase tracking-wide ml-2">
                                {errors.password?.message}
                            </p>
                        )}
                    </div>

                    <Button
                        type="submit"
                        disabled={isSubmitting}
                        className={cn(
                            "w-full h-14 rounded-full font-bold text-base transition-all duration-300 active:scale-[0.98]",
                            "bg-primary hover:bg-primary/90 text-primary-foreground shadow-lg shadow-primary/20",
                            isSubmitting && "opacity-80"
                        )}
                    >
                        {isSubmitting ? (
                            <div className="flex items-center gap-2">
                                <Loader2 className="h-5 w-5 animate-spin" />
                                <span>Verifying...</span>
                            </div>
                        ) : (
                            "Login"
                        )}
                    </Button>
                </form>

                <div className="text-center pt-2 space-y-8">
                    <Link
                        href="/forgot-password"
                        className="text-sm font-bold text-primary hover:text-primary/80 transition-colors underline-offset-4 hover:underline"
                    >
                        Forgot your password?
                    </Link>

                    <div className="pt-4 border-t border-border/40">
                        <p className="text-sm font-medium text-muted-foreground">
                            Don't have an account yet?{" "}
                            <Link
                                href="/signup"
                                className="text-primary font-bold hover:underline underline-offset-4 decoration-2 transition-all ml-1"
                            >
                                Sign Up
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    )
}
