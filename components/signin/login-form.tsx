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
    X,
    ShieldCheck,
    Globe,
    Zap
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
    StatusRejected: "Your account is either inactive or pending approval.",
    CredentialsSignin: "Invalid email or password.",
    INVALID_CREDENTIALS: "Invalid email or password.",
    SERVER_ERROR: "An unexpected error occurred. Please try again later."
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
            const timer = setTimeout(() => setShowSuccess(false), 8000)
            return () => clearTimeout(timer)
        }
    }, [searchParams])

    const handleInputChange = useCallback(() => {
        if (errors.root) clearErrors("root")
    }, [errors.root, clearErrors])

    const onSubmit = async (data: z.infer<typeof loginSchema>) => {
        clearErrors("root")
        try {
            const result = await signIn("credentials", {
                redirect: false,
                email: data.email,
                password: data.password,
                callbackUrl
            })

            if (result?.error) {
                const errorCode = result.error.split(":")[0]?.trim() || "CredentialsSignin"
                setError("root", { message: ERROR_MESSAGES[errorCode] || result.error })
            } else if (result?.ok) {
                toast.success("Login Successful", {
                    description: "Welcome back! Preparing your workspace...",
                    className: "rounded-2xl border-slate-800 bg-slate-900 text-white"
                })
                router.push(result.url || callbackUrl)
            }
        } catch {
            setError("root", { message: ERROR_MESSAGES.SERVER_ERROR })
        }
    }

    return (
        <div className="w-full space-y-6">
            <AnimatePresence mode="wait">
                {showSuccess && (
                    <motion.div
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.95 }}
                        className="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 backdrop-blur-sm"
                    >
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 shadow-lg shadow-emerald-200">
                            <CheckCircle2 className="h-4 w-4 text-white" />
                        </div>
                        <p className="flex-1 text-sm font-semibold text-emerald-900">
                            Verification complete! Please sign in.
                        </p>
                        <button type="button" onClick={() => setShowSuccess(false)} className="rounded-lg p-1 hover:bg-emerald-100 transition-colors">
                            <X className="h-4 w-4 text-emerald-600" />
                        </button>
                    </motion.div>
                )}

                {errors.root && !isSubmitting && (
                    <motion.div
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.95 }}
                        className="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50/50 p-4 backdrop-blur-sm"
                    >
                        <AlertCircle className="h-5 w-5 text-rose-600" />
                        <p className="flex-1 text-sm font-semibold text-rose-900">{errors.root.message}</p>
                        <button type="button" onClick={() => clearErrors("root")} className="rounded-lg p-1 hover:bg-rose-100 transition-colors">
                            <X className="h-4 w-4 text-rose-600" />
                        </button>
                    </motion.div>
                )}
            </AnimatePresence>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
                <FieldGroup className="space-y-4">
                    <Field>
                        <FieldLabel className="mb-2 ml-1 text-[11px] font-black uppercase tracking-[0.15em] text-slate-500">Email Address</FieldLabel>
                        <div className="relative group">
                            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600">
                                <Mail className="h-5 w-5" />
                            </div>
                            <Input
                                type="email"
                                placeholder="name@company.com"
                                className={cn(
                                    "h-14 w-full rounded-2xl border-slate-200 bg-slate-50/50 pl-12 pr-4 text-slate-900 transition-all focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-600/5",
                                    errors.email && "border-rose-200 bg-rose-50/30 focus:border-rose-500 focus:ring-rose-500/5"
                                )}
                                {...register("email", { onChange: handleInputChange })}
                            />
                        </div>
                        {errors.email && <FieldError className="ml-1 font-bold text-rose-600">{errors.email.message}</FieldError>}
                    </Field>

                    <Field>
                        <div className="mb-2 flex items-center justify-between px-1">
                            <FieldLabel className="text-[11px] font-black uppercase tracking-[0.15em] text-slate-500">Password</FieldLabel>
                            <Link href="/forgot-password" className="text-[11px] font-black uppercase tracking-widest text-blue-600 transition-colors hover:text-blue-700">
                                Forgot Password?
                            </Link>
                        </div>
                        <div className="relative group">
                            <div className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600">
                                <Lock className="h-5 w-5" />
                            </div>
                            <Input
                                type={showPassword ? "text" : "password"}
                                placeholder="••••••••"
                                className={cn(
                                    "h-14 w-full rounded-2xl border-slate-200 bg-slate-50/50 pl-12 pr-12 text-slate-900 transition-all focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-600/5",
                                    errors.password && "border-rose-200 bg-rose-50/30 focus:border-rose-500 focus:ring-rose-500/5"
                                )}
                                {...register("password", { onChange: handleInputChange })}
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors hover:text-slate-900"
                            >
                                {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                            </button>
                        </div>
                        {errors.password && <FieldError className="ml-1 font-bold text-rose-600">{errors.password.message}</FieldError>}
                    </Field>
                </FieldGroup>

                <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="h-14 w-full rounded-2xl bg-slate-900 text-[13px] font-black uppercase tracking-[0.2em] text-white shadow-2xl shadow-slate-200 transition-all hover:bg-blue-600 hover:shadow-blue-200 active:scale-[0.98] disabled:opacity-70 group"
                >
                    {isSubmitting ? (
                        <div className="flex items-center gap-3">
                            <Loader2 className="h-5 w-5 animate-spin" />
                            <span>Verifying...</span>
                        </div>
                    ) : (
                        <div className="flex items-center justify-center gap-2">
                            <span>Initialize Session</span>
                            <ArrowRight className="h-5 w-5 transition-transform group-hover:translate-x-1" />
                        </div>
                    )}
                </Button>
            </form>

            <div className="relative py-2">
                <div className="absolute inset-0 flex items-center"><span className="w-full border-t border-slate-100" /></div>
                <div className="relative flex justify-center">
                    <span className="bg-[#FDFDFD] px-4 text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">Craft Silicon</span>
                </div>
            </div>

            <p className="text-center text-sm font-bold text-slate-500">
                Don't have an Account?{" "}
                <Link href="/signup" className="text-blue-600 decoration-2 underline-offset-8 transition-all hover:underline">
                    Register Here
                </Link>
            </p>
        </div>
    )
}

export default function SignInPage() {
    return (
        <Suspense fallback={<div className="fixed inset-0 flex items-center justify-center bg-white"><Loader2 className="h-10 w-10 animate-spin text-blue-600" /></div>}>
            <div className="flex min-h-screen w-full flex-col lg:flex-row bg-[#FDFDFD]">
                <div className="relative hidden w-full lg:flex lg:w-[55%] xl:w-[60%] overflow-hidden bg-slate-950">
                    <div className="absolute inset-0">
                        <div className="absolute -left-[10%] -top-[10%] h-[60%] w-[60%] rounded-full bg-blue-600/20 blur-[140px]" />
                        <div className="absolute -bottom-[10%] -right-[10%] h-[60%] w-[60%] rounded-full bg-indigo-600/10 blur-[140px]" />
                        <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-[0.03]" />
                    </div>

                    <div className="relative z-10 flex h-full w-full flex-col justify-between p-12 xl:p-20">
                        <motion.div
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            className="flex items-center gap-4"
                        >
                            <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 shadow-2xl shadow-blue-500/40">
                                <ShieldCheck className="h-8 w-8 text-white" />
                            </div>
                            <div className="flex flex-col">
                                <span className="text-2xl font-black tracking-tighter text-white">CRAFT SILICON</span>
                                <span className="text-[10px] font-black uppercase tracking-[0.4em] text-blue-500">Third Party Ecosystem</span>
                            </div>
                        </motion.div>

                        <div className="max-w-2xl space-y-8">
                            <motion.h1
                                initial={{ opacity: 0, y: 30 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.2 }}
                                className="text-6xl font-black leading-[1.05] tracking-tight text-white xl:text-7xl"
                            >
                                Sign in to your <br />
                                <span className="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">account.</span>
                            </motion.h1>
                            <motion.p
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                transition={{ delay: 0.4 }}
                                className="text-xl font-medium leading-relaxed text-slate-400"
                            >
                                Unified portal for suppliers and tenants.
                            </motion.p>

                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 0.5 }}
                                className="flex flex-wrap gap-4 pt-4"
                            >
                                {[
                                    { icon: <Globe className="h-4 w-4" />, label: "Global Reach", val: "10k+" },
                                    { icon: <Zap className="h-4 w-4" />, label: "Service Uptime", val: "99.9%" }
                                ].map((stat, i) => (
                                    <div key={i} className="flex items-center gap-4 rounded-3xl border border-white/5 bg-white/5 p-5 backdrop-blur-xl transition-colors hover:bg-white/10">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/20 text-blue-400">
                                            {stat.icon}
                                        </div>
                                        <div>
                                            <p className="text-2xl font-black text-white">{stat.val}</p>
                                            <p className="text-[10px] font-bold uppercase tracking-widest text-slate-500">{stat.label}</p>
                                        </div>
                                    </div>
                                ))}
                            </motion.div>
                        </div>

                        <div className="flex items-center gap-6">
                            <span className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600">Enterprise v2.5.0</span>
                            <div className="h-px flex-1 bg-slate-800/50" />
                            <span className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600">Secure Environment</span>
                        </div>
                    </div>
                </div>

                <main className="flex min-h-screen w-full flex-col lg:w-[45%] xl:w-[40%]">
                    <div className="flex flex-1 flex-col justify-center px-8 py-12 sm:px-16 lg:px-20">
                        <motion.div
                            initial={{ opacity: 0, scale: 0.98 }}
                            animate={{ opacity: 1, scale: 1 }}
                            className="mx-auto w-full max-w-sm"
                        >
                            <header className="mb-12">
                                <div className="mb-10 lg:hidden">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 shadow-xl shadow-blue-200">
                                        <ShieldCheck className="h-7 w-7 text-white" />
                                    </div>
                                </div>
                                <h2 className="text-4xl font-black tracking-tight text-slate-900">Sign In</h2>
                                <p className="mt-3 text-lg font-medium text-slate-500">Access your Third Party dashboard.</p>
                            </header>

                            <SignInForm />

                            <footer className="mt-16 flex flex-wrap gap-x-6 gap-y-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                                <Link href="/terms" className="transition-colors hover:text-slate-900">Legal</Link>
                                <Link href="/privacy" className="transition-colors hover:text-slate-900">Privacy</Link>
                                <Link href="/help" className="transition-colors hover:text-slate-900">Infrastructure</Link>
                            </footer>
                        </motion.div>
                    </div>
                </main>
            </div>
        </Suspense>
    )
}