"use client"

import { useState, useEffect, Suspense, useCallback } from "react"
import Link from "next/link"
import { useRouter, useSearchParams } from "next/navigation"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { signIn } from "next-auth/react"
import { toast } from "sonner"
import { motion, AnimatePresence } from "framer-motion"
import { Loader2, Mail, Lock, ChevronDown, ArrowRight, Eye, EyeOff } from "lucide-react"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Field, FieldLabel, FieldError, FieldGroup } from "@/components/common/field"
import { NativeSelect, NativeSelectOption } from "@/components/common/native-select"
import { AuthHeader } from "@/components/auth/auth-header"
import { AnimatedBackground } from "@/components/auth/animated-background"
import { AlertBanner } from "@/components/auth/alert-banner"
import { cn } from "@/lib/utils"

const USER_TYPES = [
    { value: "Customer", label: "Customer" },
    { value: "Tenant", label: "Tenant" },
    { value: "Supplier", label: "Supplier" },
] as const

type UserTypeValue = (typeof USER_TYPES)[number]["value"]

const loginSchema = z.object({
    profile_type: z.enum(USER_TYPES.map((t) => t.value) as [string, ...string[]]),
    email: z.string().min(1, "Email is required").email("Please enter a valid email"),
    password: z.string().min(1, "Password is required"),
})

type LoginFormInputs = z.infer<typeof loginSchema>

const ERROR_MESSAGES: Record<string, string> = {
    SessionExpired: "Your session has expired. Please sign in again.",
    SessionRequired: "Please sign in to access this page.",
    AccountNotApproved: "Your account is pending approval.",
    CredentialsSignin: "Invalid email or password.",
    INVALID_CREDENTIALS: "Invalid email or password.",
    ACCOUNT_NOT_APPROVED: "Your account is pending approval.",
    SERVER_ERROR: "Something went wrong. Please try again.",
}

function SignInForm() {
    const router = useRouter()
    const searchParams = useSearchParams()
    const callbackUrl = searchParams?.get("callbackUrl") || "/dashboard"
    const urlError = searchParams?.get("error")

    const [showPassword, setShowPassword] = useState(false)
    const [showSuccess, setShowSuccess] = useState(false)

    const {
        register,
        handleSubmit,
        formState: { errors, isSubmitting },
        setError,
        clearErrors,
    } = useForm<LoginFormInputs>({
        resolver: zodResolver(loginSchema),
        mode: "onSubmit",
        defaultValues: {
            email: "",
            password: "",
            profile_type: "" as UserTypeValue,
        },
    })

    useEffect(() => {
        if (urlError) {
            setError("root", { message: ERROR_MESSAGES[urlError] || ERROR_MESSAGES.SERVER_ERROR })
        }
    }, [urlError, setError])

    useEffect(() => {
        if (searchParams?.get("registrationSuccess") === "true") {
            setShowSuccess(true)
            const timer = setTimeout(() => setShowSuccess(false), 5000)
            return () => clearTimeout(timer)
        }
    }, [searchParams])

    const handleInputChange = useCallback(() => {
        if (errors.root) {
            clearErrors("root")
        }
    }, [errors.root, clearErrors])

    const onSubmit = async (data: LoginFormInputs) => {
        clearErrors("root")

        try {
            const result = await signIn("credentials", {
                redirect: false,
                email: data.email,
                password: data.password,
                profile_type: data.profile_type,
                callbackUrl,
            })

            if (result?.error) {
                const [code] = result.error.split(":").map((x: string) => x.trim())
                const errorCode = code || "CredentialsSignin"
                setError("root", { message: ERROR_MESSAGES[errorCode] || result.error })
            } else if (result?.ok) {
                toast.success("Welcome back!", { description: "Redirecting to your dashboard..." })

                let redirectUrl = result.url || callbackUrl || "/dashboard"

                // Prevent redirection to "undefined" string or invalid URLs
                if (redirectUrl === "undefined" || redirectUrl === "null" || !redirectUrl) {
                    redirectUrl = "/dashboard"
                }

                router.push(redirectUrl)
            }
        } catch {
            setError("root", { message: ERROR_MESSAGES.SERVER_ERROR })
        }
    }

    return (
        <div className="w-full space-y-8">
            <AnimatePresence mode="wait">
                {showSuccess && (
                    <AlertBanner
                        key="success"
                        type="success"
                        message="Account created successfully! Please sign in."
                        onDismiss={() => setShowSuccess(false)}
                    />
                )}
                {errors.root && !isSubmitting && (
                    <AlertBanner
                        key="error"
                        type="error"
                        message={errors.root.message || "Something went wrong"}
                        onDismiss={() => clearErrors("root")}
                    />
                )}
            </AnimatePresence>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                <FieldGroup className="gap-6">
                    <Field>
                        <FieldLabel htmlFor="profile_type">Sign in as</FieldLabel>
                        <div className="relative">
                            <NativeSelect
                                id="profile_type"
                                {...register("profile_type", { onChange: handleInputChange })}
                                className={cn(errors.profile_type && "border-destructive ring-2 ring-destructive/20")}
                            >
                                <NativeSelectOption value="">Select account type</NativeSelectOption>
                                {USER_TYPES.map((type) => (
                                    <NativeSelectOption key={type.value} value={type.value}>
                                        {type.label}
                                    </NativeSelectOption>
                                ))}
                            </NativeSelect>
                            <ChevronDown className="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground" />
                        </div>
                        {errors.profile_type && <FieldError>Please select an account type</FieldError>}
                    </Field>

                    <Field>
                        <FieldLabel htmlFor="email">Email</FieldLabel>
                        <div
                            className={cn(
                                "relative flex items-center border border-input transition-colors focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50",
                                errors.email && "border-destructive ring-2 ring-destructive/20",
                            )}
                        >
                            <span className="flex items-center justify-center pl-4 text-muted-foreground">
                                <Mail className="h-5 w-5" />
                            </span>
                            <Input
                                id="email"
                                type="email"
                                placeholder="you@example.com"
                                className="h-12 border-0 bg-transparent focus-visible:ring-0 lg:h-14"
                                {...register("email", { onChange: handleInputChange })}
                            />
                        </div>
                        {errors.email && <FieldError>{errors.email.message}</FieldError>}
                    </Field>

                    <Field>
                        <FieldLabel htmlFor="password">Password</FieldLabel>
                        <div
                            className={cn(
                                "relative flex items-center border border-input transition-colors focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50",
                                errors.password && "border-destructive ring-2 ring-destructive/20",
                            )}
                        >
                            <span className="flex items-center justify-center pl-4 text-muted-foreground">
                                <Lock className="h-5 w-5" />
                            </span>
                            <Input
                                id="password"
                                type={showPassword ? "text" : "password"}
                                placeholder="Enter your password"
                                className="h-12 border-0 bg-transparent focus-visible:ring-0 lg:h-14"
                                {...register("password", { onChange: handleInputChange })}
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="flex items-center justify-center pr-4 text-muted-foreground hover:text-foreground"
                            >
                                {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                            </button>
                        </div>
                        {errors.password && <FieldError>{errors.password.message}</FieldError>}
                    </Field>
                </FieldGroup>

                <div className="flex justify-end">
                    <Link href="/forgot-password" className="text-sm text-muted-foreground transition-colors hover:text-primary">
                        Forgot password?
                    </Link>
                </div>

                <Button type="submit" disabled={isSubmitting} className="h-12 w-full text-base font-semibold lg:h-14">
                    {isSubmitting ? (
                        <>
                            <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                            Signing in...
                        </>
                    ) : (
                        <>
                            Sign In
                            <ArrowRight className="ml-2 h-5 w-5" />
                        </>
                    )}
                </Button>
            </form>

            <div className="text-center">
                <p className="text-sm text-muted-foreground">
                    Don&apos;t have an account?{" "}
                    <Link href="/signup" className="font-semibold text-primary transition-colors hover:text-primary/80">
                        Create an account
                    </Link>
                </p>
            </div>
        </div>
    )
}

export default function SignInPage() {
    return (
        <Suspense
            fallback={
                <div className="flex min-h-screen items-center justify-center">
                    <Loader2 className="h-8 w-8 animate-spin text-primary" />
                </div>
            }
        >
            <div className="relative flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
                <AnimatedBackground />

                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5, ease: "easeOut" }}
                    className="w-full max-w-[480px] sm:max-w-[540px] lg:max-w-[600px] xl:max-w-[640px]"
                >
                    <div className="mb-10 flex justify-center lg:mb-12">
                        <AuthHeader />
                    </div>

                    <div className="bg-card p-8 sm:p-10 lg:p-14">
                        <div className="mb-10 text-center lg:mb-12">
                            <h1 className="text-3xl font-bold tracking-tight text-foreground lg:text-4xl">Welcome back</h1>
                            <p className="mt-3 text-base text-muted-foreground lg:text-lg">
                                Sign in to access your self-service portal
                            </p>
                        </div>

                        <SignInForm />
                    </div>

                    <p className="mt-8 text-center text-sm text-muted-foreground">
                        By signing in, you agree to our{" "}
                        <Link href="/terms" className="underline transition-colors hover:text-foreground">
                            Terms
                        </Link>{" "}
                        and{" "}
                        <Link href="/privacy" className="underline transition-colors hover:text-foreground">
                            Privacy Policy
                        </Link>
                    </p>
                </motion.div>
            </div>
        </Suspense>
    )
}
