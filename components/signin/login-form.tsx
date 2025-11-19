"use client"

import React, { useState, useEffect, useCallback, Suspense } from "react"
import Link from "next/link"
import { useRouter, useSearchParams, ReadonlyURLSearchParams } from "next/navigation"
import { useForm, Controller } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { signIn } from "next-auth/react"
import { toast } from "sonner"
import { motion, AnimatePresence } from "framer-motion"
import { Loader2, ChevronDown, AlertCircle, CheckCircle, Eye, EyeOff } from "lucide-react"
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
// import { useTheme } from "next-themes"
import { AuthHeader } from "../layout/auth-header"
import { Spinner } from "@/components/common/spinner"

const USER_TYPES = [
    { value: "tenant", label: "Tenant" },
    { value: "supplier", label: "Supplier" },
] as const

const userTypeSchema = z.enum(USER_TYPES.map(t => t.value) as [string, ...string[]])
const loginSchema = z.object({
    email: z.string().email().min(1),
    password: z.string().min(1)
})
const roleLoginSchema = loginSchema.extend({ userType: userTypeSchema })
type RoleLoginFormInputs = z.infer<typeof roleLoginSchema>

const ERROR_MESSAGES = {
    SessionExpired: "Your session has expired. Please sign in again.",
    SessionRequired: "Please sign in to access this page.",
    AccountNotApproved: "Your account is not approved. Contact support.",
    NoAccessToken: "Authentication error. Please sign in again.",
    Configuration: "Authentication configuration error. Try again.",
    CredentialsSignin: "Invalid email or password.",
    INVALID_CREDENTIALS: "Invalid email or password.",
    ACCOUNT_NOT_APPROVED: "Your account is pending approval.",
    VALIDATION: "Please correct the highlighted fields.",
    NETWORK: "Network issue. Retry.",
    SERVER_ERROR: "Server error. Try again later.",
    MISSING_FIELDS: "Email and password are required."
} as const

type ErrorCode = keyof typeof ERROR_MESSAGES

const ANIMATION_VARIANTS = {
    fadeInUp: {
        initial: { opacity: 0, y: 10 },
        animate: { opacity: 1, y: 0 },
        transition: { duration: 0.25 }
    },
    fadeInDown: {
        initial: { opacity: 0, y: -10 },
        animate: { opacity: 1, y: 0 },
        exit: { opacity: 0, y: -10 }
    },
    fade: {
        initial: { opacity: 0 },
        animate: { opacity: 1 },
        exit: { opacity: 0 }
    }
}

const FIELD_STYLES = {
    base: "w-full p-3 border rounded-xl text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none transition focus:ring-2",
    error: "border-red-600 dark:border-red-500 bg-red-50 dark:bg-red-900/20 focus:ring-red-400 dark:focus:ring-red-500",
    success: "border-green-500 dark:border-green-600 focus:ring-green-300 dark:focus:ring-green-600",
    default: "border-gray-300 dark:border-zinc-700 focus:ring-indigo-500 dark:focus:ring-indigo-600"
}

// TODO: Move this to the root layout

// function ThemeToggle() {
//     const { theme, setTheme } = useTheme()
//     const toggle = useCallback(() => setTheme(theme === "dark" ? "light" : "dark"), [theme, setTheme])

//     return (
//         <motion.button
//             whileHover={{ scale: 1.1 }}
//             whileTap={{ scale: 0.95 }}
//             onClick={toggle}
//             className="p-2 rounded-full text-gray-500 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-800 transition"
//         >
//             {theme === "dark" ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
//         </motion.button>
//     )
// }

function getFieldClassName(error?: string, touched?: boolean, value?: string) {
    if (error) return `${FIELD_STYLES.base} ${FIELD_STYLES.error}`
    if (touched && value) return `${FIELD_STYLES.base} ${FIELD_STYLES.success}`
    return `${FIELD_STYLES.base} ${FIELD_STYLES.default}`
}

interface InputFieldProps {
    id: string
    label: string
    type?: string
    placeholder: string
    value: string
    error?: string
    touched?: boolean
    register: any
    isPassword?: boolean
    showPassword?: boolean
    onTogglePassword?: () => void
}

function InputField({
    id,
    label,
    type = "text",
    placeholder,
    value,
    error,
    touched,
    register,
    isPassword = false,
    showPassword = false,
    onTogglePassword
}: InputFieldProps) {
    const inputClasses = getFieldClassName(error, touched, value)

    return (
        <motion.div {...ANIMATION_VARIANTS.fadeInUp}>
            <label htmlFor={id} className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                {label}
            </label>
            <div className="relative">
                <Input
                    {...register}
                    id={id}
                    type={isPassword ? (showPassword ? "text" : "password") : type}
                    placeholder={placeholder}
                    className={inputClasses}
                />
                {isPassword && (
                    <button
                        type="button"
                        onClick={onTogglePassword}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400"
                    >
                        {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                    </button>
                )}
                {touched && !error && value && !isPassword && (
                    <CheckCircle className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-green-500" />
                )}
            </div>
            <AnimatePresence>
                {error && (
                    <motion.p {...ANIMATION_VARIANTS.fade} className="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                        <AlertCircle className="w-4 h-4" />
                        {error}
                    </motion.p>
                )}
            </AnimatePresence>
        </motion.div>
    )
}

interface UserTypeSelectFieldProps {
    control: any
    error?: string
    touched?: boolean
}

function UserTypeSelectField({ control, error, touched }: UserTypeSelectFieldProps) {
    return (
        <motion.div {...ANIMATION_VARIANTS.fadeInUp}>
            <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                Login As
            </label>
            <Controller
                name="userType"
                control={control}
                render={({ field }) => (
                    <div className="relative">
                        <select
                            {...field}
                            value={field.value || ""}
                            className={`w-full p-3 border rounded-xl bg-transparent dark:text-gray-100 appearance-none ${error
                                ? "border-red-600 dark:border-red-500"
                                : "border-gray-300 dark:border-zinc-700 focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                }`}
                        >
                            <option value="" disabled>Select your account type</option>
                            {USER_TYPES.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>
                        <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    </div>
                )}
            />
            <AnimatePresence>
                {error && (
                    <motion.p {...ANIMATION_VARIANTS.fade} className="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                        <AlertCircle className="w-4 h-4" />
                        {error}
                    </motion.p>
                )}
            </AnimatePresence>
        </motion.div>
    )
}

interface AlertBannerProps {
    type: "error" | "success"
    message: string
}

function AlertBanner({ type, message }: AlertBannerProps) {
    const isError = type === "error"
    const bgClass = isError
        ? "bg-red-50 dark:bg-red-900/30 border-red-300 dark:border-red-700 text-red-700 dark:text-red-200"
        : "bg-green-50 dark:bg-green-900/30 border-green-300 dark:border-green-700 text-green-700 dark:text-green-200"

    const Icon = isError ? AlertCircle : CheckCircle

    return (
        <motion.div
            {...ANIMATION_VARIANTS.fadeInDown}
            className={`p-4 border rounded-xl flex gap-2 ${bgClass}`}
        >
            <Icon className="w-5 h-5" />
            {message}
        </motion.div>
    )
}

function useSignInLogic(router: any, searchParams: ReadonlyURLSearchParams) {
    const callbackUrl = searchParams.get("callbackUrl") || "/dashboard"
    const urlError = searchParams.get("error")
    const [authError, setAuthError] = useState<string | null>(null)
    const [showPassword, setShowPassword] = useState(false)

    const { register, handleSubmit, formState, watch, setError, clearErrors, control } = useForm<RoleLoginFormInputs>({
        resolver: zodResolver(roleLoginSchema),
        mode: "onTouched",
        defaultValues: { email: "", password: "", userType: undefined }
    })

    const { errors, isSubmitting, touchedFields } = formState
    const watched = watch()

    useEffect(() => {
        if (urlError) {
            setAuthError(ERROR_MESSAGES[urlError as ErrorCode] || ERROR_MESSAGES.Configuration)
        }
    }, [urlError])

    useEffect(() => {
        if (authError && Object.values(watched).some(Boolean)) {
            setAuthError(null)
            clearErrors("root")
        }
    }, [watched, authError, clearErrors])

    const onSubmit = async (data: RoleLoginFormInputs) => {
        setAuthError(null)
        clearErrors("root")

        const result = await signIn("credentials", {
            redirect: false,
            email: data.email,
            password: data.password,
            userRole: data.userType,
            callbackUrl
        })

        if (result?.error) {
            const [code, msg] = result.error.split(":").map((x: string) => x.trim())
            const errorCode = code || "CredentialsSignin"
            setError("root", {
                type: errorCode,
                message: ERROR_MESSAGES[errorCode as ErrorCode] || msg || result.error
            })
        } else if (result?.ok) {
            toast.success("Signed In", { description: "Redirecting..." })
            setTimeout(() => router.push(result.url || callbackUrl), 300)
        }
    }

    return {
        register,
        handleSubmit,
        errors,
        isSubmitting,
        authError,
        touchedFields,
        showPassword,
        setShowPassword,
        onSubmit,
        control,
        watched
    }
}

function SignInFormComponent() {
    const router = useRouter()
    const searchParams = useSearchParams()
    const [showSuccess, setShowSuccess] = useState(false)
    const {
        register,
        handleSubmit,
        errors,
        isSubmitting,
        authError,
        showPassword,
        setShowPassword,
        onSubmit,
        touchedFields,
        control,
        watched
    } = useSignInLogic(router, searchParams)

    useEffect(() => {
        if (searchParams.get("registrationSuccess") === "true") {
            setShowSuccess(true)
            const timer = setTimeout(() => setShowSuccess(false), 5000)
            return () => clearTimeout(timer)
        }
    }, [searchParams])

    const displayError = errors.root?.message || authError

    return (
        <div className="w-full space-y-6">
            <AnimatePresence>
                {displayError && <AlertBanner type="error" message={displayError} />}
            </AnimatePresence>

            <AnimatePresence>
                {showSuccess && <AlertBanner type="success" message="Registration successful. Please log in." />}
            </AnimatePresence>

            <div className="space-y-5">
                <UserTypeSelectField
                    control={control}
                    error={errors.userType?.message}
                    touched={touchedFields.userType}
                />

                <InputField
                    id="email"
                    label="Email Address"
                    type="email"
                    placeholder="john.doe@example.com"
                    value={watched.email}
                    error={errors.email?.message}
                    touched={touchedFields.email}
                    register={register("email")}
                />

                <InputField
                    id="password"
                    label="Password"
                    placeholder="Enter password"
                    value={watched.password}
                    error={errors.password?.message}
                    touched={touchedFields.password}
                    register={register("password")}
                    isPassword
                    showPassword={showPassword}
                    onTogglePassword={() => setShowPassword(!showPassword)}
                />

                <Button
                    type="button"
                    onClick={handleSubmit(onSubmit)}
                    disabled={isSubmitting}
                    className="w-full p-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 flex items-center justify-center gap-2"
                >
                    {isSubmitting ? (
                        <>
                            <Spinner className="size-6 text-blue-500" />
                            <span>Signing In...</span>
                        </>
                    ) : (
                        "Sign In"
                    )}
                </Button>

                <div className="flex justify-center">
                    <Link href="/forgot-password" className="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Forgot password?
                    </Link>
                </div>
            </div>

            <div className="text-center text-sm text-gray-600 dark:text-gray-400">
                Don't have an account?{" "}
                <Link href="/signup" className="font-extrabold text-indigo-600 dark:text-indigo-400 hover:underline transition duration-150">
                    Create Account Today
                </Link>
            </div>
        </div>
    )
}

export default function SignInPage() {
    return (
        <Suspense fallback={
            <div className="flex min-h-screen items-center justify-center">
                <Loader2 className="animate-spin h-12 w-12 text-indigo-600" />
            </div>
        }>
            <div className="flex flex-col min-h-screen items-center justify-center p-6">
                {/* <div className="absolute top-4 right-4">
                    <ThemeToggle />
                </div> */}

                <motion.div
                    {...ANIMATION_VARIANTS.fade}
                    className="w-full max-w-xl bg-transparent dark:bg-transparent dark:border-zinc-700 rounded-2xl p-10 backdrop-blur-xl"
                >
                    <div className="text-center mb-10">
                        <motion.h1 {...ANIMATION_VARIANTS.fade} className="text-4xl font-extrabold text-gray-900 dark:text-white">
                            <AuthHeader />
                        </motion.h1>
                        <motion.p {...ANIMATION_VARIANTS.fade} className="text-gray-600 dark:text-gray-400 mt-2">
                            Use your Email and password to log in to the Self-Service Portal
                        </motion.p>
                    </div>
                    <SignInFormComponent />
                </motion.div>
            </div>
        </Suspense>
    )
}