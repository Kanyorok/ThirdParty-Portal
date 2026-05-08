"use client"

import * as React from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useForm, useWatch, type UseFormRegisterReturn } from "react-hook-form"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { Mail, Lock, Eye, X, EyeClosed, CircleCheck, type LucideIcon } from "lucide-react"
import { signIn } from "next-auth/react"

import { AlertBanner } from "@/components/auth/alert-banner"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { cn } from "@/lib/utils"

const schema = z.object({
    email: z.string().min(1, "Email is required").email("Enter a valid Email address"),
    password: z.string().min(1, "Password is required"),
})

type FormValues = z.infer<typeof schema>

const AUTH_ERROR_MESSAGES: Record<string, string> = {
    INVALID_CREDENTIALS: "Invalid email or password.",
    EMAIL_NOT_VERIFIED: "Your email address is not verified yet.",
    ACCOUNT_DISABLED: "Your account is disabled.",
    ACCOUNT_NOT_APPROVED: "Your account is pending approval.",
    PROFILE_NOT_AUTHORIZED: "This account is not authorized for the selected portal profile.",
    VALIDATION_ERROR: "Please review your login details and try again.",
    SERVER_ERROR: "An unexpected error occurred. Please try again later.",
}

const RAW_AUTH_MESSAGE_MAP: Array<[RegExp, string]> = [
    [/^the given data was invalid\.?$/i, AUTH_ERROR_MESSAGES.VALIDATION_ERROR],
    [/profile[_\s-]*type/i, "We could not determine which portal profile to use for this account. Contact support if this continues."],
    [/(email|password).*(required|invalid)/i, AUTH_ERROR_MESSAGES.VALIDATION_ERROR],
]

const fieldIconClass = "h-4 w-4 text-slate-700"
const inputBaseClass = "h-12 rounded-[6px] border-slate-300 bg-white px-3.5 pr-10 text-[15px] font-semibold text-slate-950 caret-primary transition-[border-color,background-color,box-shadow] placeholder:text-sm placeholder:font-medium placeholder:text-slate-500 hover:border-slate-400 focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/14 focus-visible:shadow-[0_0_0_1px_rgba(0,92,144,0.14)]"
const inputErrorClass = "border-rose-400 focus-visible:border-rose-500 focus-visible:ring-rose-200"
const endButtonClass = "absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-500 hover:text-slate-800"
const submitButtonClass = "group h-12 w-full rounded-[6px] bg-primary text-sm font-bold tracking-[0.08em] uppercase shadow-[0_16px_28px_-18px_rgba(0,92,144,0.42)] ring-1 ring-primary/20 transition-[transform,background-color,box-shadow] duration-200 ease-out hover:-translate-y-0.5 hover:bg-[var(--primary-hover)] hover:shadow-[0_18px_30px_-18px_rgba(0,92,144,0.48)] active:translate-y-0 active:shadow-[0_10px_16px_-14px_rgba(0,92,144,0.34)] disabled:translate-y-0 disabled:bg-primary/70 disabled:shadow-none"
const linkClass = "transition-[color,opacity,transform,text-decoration-color] duration-200 ease-out hover:text-primary hover:underline hover:underline-offset-4"

function parseAuthResultError(error: string | undefined) {
    if (!error) {
        return {
            code: "SERVER_ERROR",
            rawMessage: "",
        }
    }

    const separatorIndex = error.indexOf(":")
    if (separatorIndex === -1) {
        return {
            code: error.trim() || "SERVER_ERROR",
            rawMessage: "",
        }
    }

    return {
        code: error.slice(0, separatorIndex).trim() || "SERVER_ERROR",
        rawMessage: error.slice(separatorIndex + 1).trim(),
    }
}

function getFriendlyAuthMessage(error: string | undefined) {
    const { code, rawMessage } = parseAuthResultError(error)
    const mappedCodeMessage = AUTH_ERROR_MESSAGES[code]

    if (mappedCodeMessage && code !== "VALIDATION_ERROR") {
        return mappedCodeMessage
    }

    if (rawMessage) {
        const matchedPattern = RAW_AUTH_MESSAGE_MAP.find(([pattern]) => pattern.test(rawMessage))
        if (matchedPattern) return matchedPattern[1]

        if (rawMessage.length <= 160) return rawMessage
    }

    return mappedCodeMessage || AUTH_ERROR_MESSAGES.SERVER_ERROR
}

interface FormFieldProps {
    id: string
    label: string
    icon: LucideIcon
    type: string
    autoComplete: string
    inputMode?: React.HTMLAttributes<HTMLInputElement>["inputMode"]
    placeholder: string
    disabled: boolean
    hasError: boolean
    errorMessage?: string
    registration: UseFormRegisterReturn
    endAction?: React.ReactNode
}

function FormField({
    id, label, icon: Icon, type, autoComplete, inputMode, placeholder,
    disabled, hasError, errorMessage, registration, endAction,
}: FormFieldProps) {
    const errorId = `${id}-error`

    return (
        <div className="grid gap-2.5">
            <label htmlFor={id} className="flex items-center gap-2 text-[15px] font-semibold tracking-[0.01em] text-slate-950">
                <Icon className={cn(fieldIconClass, hasError && "text-rose-500")} />
                <span>{label}</span>
            </label>
            <div className="relative">
                <Input
                    id={id}
                    type={type}
                    autoComplete={autoComplete}
                    inputMode={inputMode}
                    aria-describedby={hasError && errorMessage ? errorId : undefined}
                    aria-invalid={hasError || undefined}
                    autoCapitalize={type === "email" ? "none" : undefined}
                    placeholder={placeholder}
                    disabled={disabled}
                    spellCheck={false}
                    className={cn(inputBaseClass, hasError && inputErrorClass)}
                    {...registration}
                />
                {endAction}
            </div>
            {hasError && errorMessage ? <p id={errorId} className="sr-only">{errorMessage}</p> : null}
        </div>
    )
}

export default function LoginPage() {
    const router = useRouter()
    const authInfoId = React.useId()
    const authErrorId = React.useId()
    const [authError, setAuthError] = React.useState<string | null>(null)
    const [showPassword, setShowPassword] = React.useState(false)

    const {
        register,
        handleSubmit,
        setValue,
        control,
        formState: { errors, touchedFields, isSubmitted, isSubmitting },
    } = useForm<FormValues>({
        resolver: zodResolver(schema),
        mode: "onBlur",
    })

    const emailValue = useWatch({ control, name: "email" })

    const hasFieldError = (field: keyof FormValues) =>
        (touchedFields[field] || isSubmitted) && !!errors[field]

    const onSubmit = async (data: FormValues) => {
        setAuthError(null)

        try {
            const result = await signIn("credentials", {
                email: data.email,
                password: data.password,
                redirect: false,
            })

            if (result?.error) {
                const { code: errorCode } = parseAuthResultError(result.error)

                if (errorCode === "EMAIL_NOT_VERIFIED") {
                    router.push(`/verify-email/expired?email=${encodeURIComponent(data.email)}`)
                    return
                }

                setAuthError(getFriendlyAuthMessage(result.error))
                return
            }

            router.replace("/dashboard")
        } catch {
            setAuthError(AUTH_ERROR_MESSAGES.SERVER_ERROR)
        }
    }

    const formDescriptionIds = [authInfoId, authError ? authErrorId : null].filter(Boolean).join(" ")

    return (
        <main className="relative flex min-h-dvh items-center bg-slate-50 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
            <div className="relative mx-auto flex w-full max-w-5xl items-center justify-center">
                <section className="w-full max-w-[560px] rounded-[10px] border border-slate-300 bg-white px-5 py-7 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.16)] sm:rounded-[12px] sm:px-8 sm:py-9 lg:px-10 lg:py-10">
                    <div className="mx-auto w-full max-w-md space-y-8">
                        <header className="space-y-5 border-b border-slate-300 pb-6 text-center">
                            <div className="space-y-3">
                                <h1 className="text-2xl font-bold tracking-tight text-slate-950 sm:text-[2rem]">Authorization Required</h1>
                                <p id={authInfoId} className="mx-auto max-w-md rounded-[6px] bg-slate-100 px-4 py-2.5 text-sm font-medium leading-6 text-slate-600">Please sign in to continue.</p>
                            </div>
                            <div className="mx-auto h-px w-16 bg-primary/70" />
                        </header>

                        <div className="grid gap-6">
                            <form noValidate aria-busy={isSubmitting} aria-describedby={formDescriptionIds || undefined} onSubmit={handleSubmit(onSubmit)} className="grid gap-6">
                                {authError ? (
                                    <div id={authErrorId} aria-live="assertive" aria-atomic="true">
                                        <AlertBanner type="error" message={authError} />
                                    </div>
                                ) : null}

                                <div className="grid gap-5">
                                    <FormField
                                        id="email"
                                        label="Email"
                                        icon={Mail}
                                        type="email"
                                        autoComplete="username"
                                        inputMode="email"
                                        placeholder="Enter your email address"
                                        disabled={isSubmitting}
                                        hasError={hasFieldError("email")}
                                        errorMessage={errors.email?.message}
                                        registration={register("email")}
                                        endAction={emailValue ? (
                                            <button
                                                type="button"
                                                onClick={() => setValue("email", "", { shouldValidate: true })}
                                                className={endButtonClass}
                                                aria-label="Clear email"
                                            >
                                                <X className="h-4 w-4" />
                                            </button>
                                        ) : null}
                                    />

                                    <FormField
                                        id="password"
                                        label="Password"
                                        icon={Lock}
                                        type={showPassword ? "text" : "password"}
                                        autoComplete="current-password"
                                        placeholder="Enter your password"
                                        disabled={isSubmitting}
                                        hasError={hasFieldError("password")}
                                        errorMessage={errors.password?.message}
                                        registration={register("password")}
                                        endAction={
                                            <button
                                                type="button"
                                                onClick={() => setShowPassword(prev => !prev)}
                                                className={endButtonClass}
                                                aria-label={showPassword ? "Hide password" : "Show password"}
                                            >
                                                {showPassword ? <EyeClosed className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                            </button>
                                        }
                                    />
                                </div>

                                <div className="grid gap-3">
                                    <Button type="submit" disabled={isSubmitting} className={submitButtonClass}>
                                        {isSubmitting ? (
                                            <span className="inline-flex items-center gap-2.5">
                                                <span className="loader-bars loader-bars--inline [&>span]:bg-white [&>span]:shadow-none" aria-hidden="true">
                                                    <span />
                                                    <span />
                                                    <span />
                                                </span>
                                                <span>Signing in...</span>
                                            </span>
                                        ) : (
                                            <span className="inline-flex items-center gap-2.5">
                                                <CircleCheck className="h-4 w-4 transition-transform duration-200 group-hover:scale-105" />
                                                <span>Sign in</span>
                                            </span>
                                        )}
                                    </Button>
                                    <p className="text-center text-xs font-medium tracking-[0.01em] text-slate-500">Powered by <span className="font-bold text-slate-900">Craft Silicon</span></p>
                                </div>
                            </form>

                            <div className="grid gap-4 border-t border-slate-300 pt-6 text-left">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <Link href="/forgot-password" className={cn("inline-flex items-center text-sm font-semibold text-slate-700", linkClass)}>
                                        Reset your password
                                    </Link>
                                    <p className="text-sm text-slate-600">
                                        New here?{" "}
                                        <Link href="/signup" className={cn("font-semibold text-primary", linkClass)}>
                                            Create an account
                                        </Link>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    )
}
