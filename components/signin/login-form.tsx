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
import {
    Loader2,
    Mail,
    Lock,
    ArrowRight,
    Eye,
    EyeOff,
    AlertCircle,
    CheckCircle2,
    X
} from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
    Field,
    FieldLabel,
    FieldError,
    FieldGroup
} from "@/components/common/field"
import { cn } from "@/lib/utils"

const loginSchema = z.object({
    email: z.string().min(1, "Email is required").email("Please enter a valid email"),
    password: z.string().min(1, "Password is required")
})

const ERROR_MESSAGES: Record<string, string> = {
    SessionExpired: "Your session has expired. Please sign in again.",
    SessionRequired: "Please sign in to access this page.",
    StatusRejected: "Your account is either inactive or pending approval. Please contact support.",
    CredentialsSignin: "Invalid email or password.",
    INVALID_CREDENTIALS: "Invalid email or password.",
    SERVER_ERROR: "Something went wrong. Please try again."
}

function SignInForm() {
    const router = useRouter()
    const searchParams = useSearchParams()
    const callbackUrl = searchParams.get("callbackUrl") || "/dashboard"

    const urlError = searchParams.get("error")
    const [showPassword, setShowPassword] = useState(false)
    const [showSuccess, setShowSuccess] = useState(false)

    const {
        register,
        handleSubmit,
        formState: { errors, isSubmitting },
        setError,
        clearErrors
    } = useForm({
        resolver: zodResolver(loginSchema),
        defaultValues: { email: "", password: "" }
    })

    useEffect(() => {
        if (urlError) {
            setError("root", { message: ERROR_MESSAGES[urlError] || ERROR_MESSAGES.SERVER_ERROR })
        }
    }, [urlError, setError])

    useEffect(() => {
        if (searchParams.get("registrationSuccess") === "true") {
            setShowSuccess(true)
            const timer = setTimeout(() => setShowSuccess(false), 5000)
            return () => clearTimeout(timer)
        }
    }, [searchParams])

    const handleInputChange = useCallback(() => {
        if (errors.root) clearErrors("root")
    }, [errors.root, clearErrors])

    const onSubmit = async (data: any) => {
        clearErrors("root")
        try {
            const result = await signIn("credentials", {
                redirect: false,
                email: data.email,
                password: data.password,
                callbackUrl
            })

            if (result?.error) {
                const [code] = result.error.split(":").map((x: string) => x.trim())
                const errorCode = code || "CredentialsSignin"
                setError("root", { message: ERROR_MESSAGES[errorCode] || result.error })
            } else if (result?.ok) {
                toast.success("Welcome back!", { description: "Redirecting to your dashboard..." })
                router.push(result.url || callbackUrl)
            }
        } catch {
            setError("root", { message: ERROR_MESSAGES.SERVER_ERROR })
        }
    }

    return (
        <div className="w-full space-y-8">
            <AnimatePresence mode="wait">
                {showSuccess && (
                    <motion.div
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="flex items-center gap-3 bg-blue-100 p-4 rounded-sm"
                    >
                        <CheckCircle2 className="h-5 w-5 text-blue-700" />
                        <span className="flex-1 text-sm text-black">
                            Account created successfully! Please sign in.
                        </span>
                        <button onClick={() => setShowSuccess(false)} className="text-black/70 hover:text-black">
                            <X className="h-4 w-4" />
                        </button>
                    </motion.div>
                )}

                {errors.root && !isSubmitting && (
                    <motion.div
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="flex items-center gap-3 bg-red-100 p-4 rounded-sm"
                    >
                        <AlertCircle className="h-5 w-5 text-red-700" />
                        <span className="flex-1 text-sm text-red-900">{errors.root.message}</span>
                        <button onClick={() => clearErrors("root")} className="text-black/70 hover:text-black">
                            <X className="h-4 w-4" />
                        </button>
                    </motion.div>
                )}
            </AnimatePresence>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                <FieldGroup className="gap-5">
                    <Field>
                        <FieldLabel className="text-sm font-medium">Email</FieldLabel>
                        <div className="relative">
                            <Mail className="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500" />
                            <Input
                                type="email"
                                placeholder="you@example.com"
                                className={cn(
                                    "h-12 w-full border bg-gray-100 pl-12 pr-4 text-base rounded-sm focus-visible:ring-0 focus-visible:border-blue-700",
                                    errors.email && "border-red-600"
                                )}
                                {...register("email", { onChange: handleInputChange })}
                            />
                        </div>
                        {errors.email && <FieldError>{errors.email.message}</FieldError>}
                    </Field>

                    <Field>
                        <FieldLabel className="text-sm font-medium">Password</FieldLabel>
                        <div className="relative">
                            <Lock className="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500" />
                            <Input
                                type={showPassword ? "text" : "password"}
                                placeholder="Enter your password"
                                className={cn(
                                    "h-12 w-full border bg-gray-100 pl-12 pr-12 text-base rounded-sm focus-visible:ring-0 focus-visible:border-blue-700",
                                    errors.password && "border-red-600"
                                )}
                                {...register("password", { onChange: handleInputChange })}
                            />

                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black"
                            >
                                {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                            </button>
                        </div>

                        {errors.password && <FieldError>{errors.password.message}</FieldError>}
                    </Field>
                </FieldGroup>

                <div className="flex justify-end">
                    <Link href="/forgot-password" className="text-sm text-gray-600 hover:text-blue-700">
                        Forgot password?
                    </Link>
                </div>

                <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="h-12 w-full text-base font-medium bg-blue-700 text-white hover:bg-blue-800 rounded-sm"
                >
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

            <p className="text-center text-sm text-gray-600">
                Don&apos;t have an account?{" "}
                <Link href="/signup" className="font-medium text-blue-700 hover:text-blue-800">
                    Create an account
                </Link>
            </p>
        </div>
    )
}

export default function SignInPage() {
    return (
        <Suspense
            fallback={
                <div className="flex min-h-screen items-center justify-center">
                    <Loader2 className="h-8 w-8 animate-spin text-blue-700" />
                </div>
            }
        >
            <div className="relative flex min-h-screen">
                <div className="hidden w-1/2 bg-blue-700 lg:block">
                    <div className="flex h-full flex-col justify-between p-12 text-white">
                        <h1 className="text-2xl font-bold">Craft Silicon</h1>

                        <div className="space-y-6">
                            <h2 className="text-3xl font-bold lg:text-4xl">
                                Welcome back to self-service portal
                            </h2>
                            <p className="text-lg text-white/80">
                                Manage and access all services in one place.
                            </p>
                        </div>

                        <p className="text-sm text-white/70">Trusted worldwide by 10k+ suppliers, tenants</p>
                    </div>
                </div>

                <div className="flex w-full items-center justify-center px-6 py-12 lg:w-1/2 lg:px-12">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4 }}
                        className="w-full max-w-md lg:max-w-lg"
                    >
                        <div className="mb-10 lg:hidden">
                            <h1 className="text-xl font-bold text-black">Craft Silicon</h1>
                        </div>

                        <div className="mb-8">
                            <h2 className="text-2xl font-bold text-black lg:text-3xl">
                                Sign in To Third Parties Portal
                            </h2>
                        </div>

                        <SignInForm />

                        <p className="mt-8 text-center text-xs text-gray-600 lg:text-left">
                            By signing in, you agree to our{" "}
                            <Link href="/terms" className="underline hover:text-black">
                                Terms
                            </Link>{" "}
                            and{" "}
                            <Link href="/privacy" className="underline hover:text-black">
                                Privacy Policy
                            </Link>
                        </p>
                    </motion.div>
                </div>
            </div>
        </Suspense>
    )
}