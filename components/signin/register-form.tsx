"use client"

import * as React from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import {
    AlertCircle,
    BriefcaseBusiness,
    Building2,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    Eye,
    EyeClosed,
    FileCheck2,
    LucideIcon,
    ShieldCheck,
    X,
    UserCog,
} from "lucide-react"

import Loading from "../common/custom_loader"
import { Button } from "../common/button"
import { Alert, AlertDescription, AlertTitle } from "../common/alert"
import { Input } from "../common/input"
import { Textarea } from "../common/textarea"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "../common/select"
import { cn } from "../../lib/utils"
import { CLIENT_APP_NAME_STRING } from "../../config/client-config"
import { isCompanyLikeBusinessType } from "../../lib/register-shared"
import { type RegisterFormInputs, type RegisterRole, type RegisterThirdPartyResult, useRegisterForm } from "../../hooks/use-register"

const ROLE_OPTIONS: Array<{ id: RegisterRole; label: string; icon: LucideIcon }> = [
    { id: "SU", label: "Supplier", icon: BriefcaseBusiness },
    { id: "TN", label: "Tenant", icon: Building2 },
    { id: "CU", label: "Customer", icon: UserCog },
]

const labelStyle = "flex min-h-5 items-center gap-2 text-[14px] font-semibold leading-5 tracking-[0.01em] text-slate-950"
const inputBaseClass = "h-12 rounded-[6px] border-slate-300 bg-white px-3.5 text-[15px] font-semibold text-slate-950 caret-primary transition-[border-color,background-color,box-shadow] placeholder:text-sm placeholder:font-medium placeholder:text-slate-500 hover:border-slate-400 focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/14 focus-visible:shadow-[0_0_0_1px_rgba(0,92,144,0.14)]"
const inputErrorClass = "border-rose-400 bg-rose-50 focus-visible:border-rose-500 focus-visible:ring-rose-200 focus-visible:shadow-none"
const inputStyle = `${inputBaseClass} pr-10`
const textAreaStyle = "min-h-28 rounded-[6px] border-slate-300 bg-white px-3.5 py-3 text-[15px] font-semibold text-slate-950 transition-[border-color,background-color,box-shadow] placeholder:text-sm placeholder:font-medium placeholder:text-slate-500 hover:border-slate-400 focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/14 focus-visible:shadow-[0_0_0_1px_rgba(0,92,144,0.14)]"
const fileInputStyle = "h-12 rounded-[6px] border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-[border-color,background-color,box-shadow] file:mr-3 file:rounded-[4px] file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:border-slate-400 focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/14"
const selectStyle = "h-12 w-full rounded-[6px] border-slate-300 bg-white px-3.5 text-[15px] font-semibold text-slate-950 transition-[border-color,background-color,box-shadow] hover:border-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/14 focus:shadow-[0_0_0_1px_rgba(0,92,144,0.14)]"
const errorStyle = "mt-1.5 flex items-center gap-1 text-sm font-medium text-rose-600"
const endButtonClass = "absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-500 hover:text-slate-800"
const submitButtonClass = "group h-[54px] w-full rounded-[8px] bg-primary text-sm font-bold tracking-[0.08em] uppercase shadow-[0_16px_28px_-18px_rgba(0,92,144,0.42)] ring-1 ring-primary/20 transition-[transform,background-color,box-shadow] duration-200 ease-out hover:-translate-y-0.5 hover:bg-[var(--primary-hover)] hover:shadow-[0_18px_30px_-18px_rgba(0,92,144,0.48)] active:translate-y-0 active:shadow-[0_10px_16px_-14px_rgba(0,92,144,0.34)] disabled:translate-y-0 disabled:bg-primary/70 disabled:shadow-none"
const secondaryButtonClass = "h-[54px] w-full rounded-[8px] border border-slate-300 bg-white text-sm font-bold tracking-[0.08em] uppercase text-slate-700 transition-[border-color,color,background-color] hover:border-slate-400 hover:text-slate-950"
const linkClass = "transition-[color,opacity,transform,text-decoration-color] duration-200 ease-out hover:text-primary hover:underline hover:underline-offset-4"
const signInLinkClass = "group inline-flex w-full items-center justify-center gap-2 rounded-[12px] border border-slate-300 bg-white px-4 py-3.5 text-sm font-bold text-slate-800 transition-[border-color,box-shadow,transform] hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-[0_12px_20px_-18px_rgba(15,23,42,0.4)] sm:w-auto"
const sectionCardClass = "p-0"
const formSectionGridClass = "mt-5 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2 md:items-start"
const fieldBlockClass = "grid content-start gap-2.5"
type StepId = "role-setup" | "business" | "supplier-documents" | "profile-details" | "user-access"

type StepMeta = {
    id: StepId
    title: string
    description: string
    fields: string[]
}

function AuthAlert({ id, message, onDismiss }: { id: string; message: string; onDismiss?: () => void }) {
    const items = message
        .split("\n")
        .map((item) => item.trim())
        .filter(Boolean)
        .map((item) => item.replace(/^-+\s*/, ""))
    const issueCount = items.length
    const title = issueCount > 1 ? `${issueCount} issues need attention` : "Validation error"

    return (
        <Alert id={id} variant="destructive" aria-live="assertive" aria-atomic="true" className="rounded-[10px] border-rose-300/80 bg-rose-50/95 pr-11 text-rose-700 [&>svg]:text-rose-700">
            <AlertCircle className="h-4 w-4" />
            <AlertTitle className="text-rose-900">{title}</AlertTitle>
            <AlertDescription className="text-rose-700">
                {items.length > 1 ? (
                    <ul className="list-disc space-y-1 pl-5">
                        {items.map((item, index) => (
                            <li key={`${item}-${index}`}>{item}</li>
                        ))}
                    </ul>
                ) : (
                    <p>{items[0] || message}</p>
                )}
            </AlertDescription>
            {onDismiss ? (
                <button
                    type="button"
                    onClick={onDismiss}
                    className="absolute right-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-[6px] text-rose-600 transition-colors hover:bg-rose-100 hover:text-rose-800"
                    aria-label="Dismiss alert"
                >
                    <X className="h-4 w-4" />
                </button>
            ) : null}
        </Alert>
    )
}

function StatusAlert({ message }: { message: string }) {
    return (
        <Alert role="status" aria-live="polite" aria-atomic="true" className="rounded-[10px] border-emerald-300/80 bg-emerald-50/95 text-emerald-700 [&>svg]:text-emerald-700">
            <CheckCircle2 className="h-4 w-4" />
            <AlertTitle className="text-emerald-900">Progress update</AlertTitle>
            <AlertDescription className="text-emerald-700">
                <p>{message}</p>
            </AlertDescription>
        </Alert>
    )
}

function FieldLabel({ children, required }: { children: React.ReactNode; required?: boolean }) {
    return (
        <label className={labelStyle}>
            {children}
            {required ? <span className="text-rose-600"> *</span> : null}
        </label>
    )
}

function FieldError({ message }: { message?: string | null }) {
    if (!message) return null
    return (
        <p className={errorStyle}>
            <AlertCircle className="h-3 w-3" />
            {message}
        </p>
    )
}

function SectionTitle({
    title,
    description,
    icon: Icon,
}: {
    title: string
    description?: string
    icon?: LucideIcon
}) {
    return (
        <div className="space-y-1.5">
            <div className="flex items-center gap-2">
                {Icon ? <Icon className="h-4 w-4 text-primary" /> : null}
                <h2 className="text-[17px] font-bold tracking-tight text-slate-950">{title}</h2>
            </div>
            {description ? <p className="text-sm font-medium leading-6 text-slate-600">{description}</p> : null}
        </div>
    )
}

function FieldShell({
    label,
    required,
    error,
    hint,
    className,
    children,
}: {
    label: React.ReactNode
    required?: boolean
    error?: string | null
    hint?: React.ReactNode
    className?: string
    children: React.ReactNode
}) {
    return (
        <div className={cn("grid gap-2.5", className)}>
            <FieldLabel required={required}>{label}</FieldLabel>
            {children}
            {hint ? <p className="text-sm font-medium leading-6 text-slate-500">{hint}</p> : null}
            <FieldError message={error} />
        </div>
    )
}

function SelectFieldBlock({
    label,
    required,
    error,
    placeholder,
    value,
    onValueChange,
    options,
    disabled,
    hint,
    className,
}: {
    label: React.ReactNode
    required?: boolean
    error?: string | null
    placeholder: string
    value?: string
    onValueChange: (value: string) => void
    options: Array<{ value: string; label: string }>
    disabled?: boolean
    hint?: React.ReactNode
    className?: string
}) {
    const controlledValue = value ?? ""

    return (
        <FieldShell label={label} required={required} error={error} hint={hint} className={className}>
            <Select value={controlledValue} onValueChange={onValueChange} disabled={disabled}>
                <SelectTrigger className={cn(selectStyle, error && inputErrorClass)} aria-required={required ? "true" : undefined}>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </FieldShell>
    )
}

function FileFieldBlock({
    label,
    required,
    error,
    accept,
    onChange,
    selectedFileName,
    hint,
    className,
}: {
    label: React.ReactNode
    required?: boolean
    error?: string | null
    accept?: string
    onChange: (event: React.ChangeEvent<HTMLInputElement>) => void
    selectedFileName?: string | null
    hint?: React.ReactNode
    className?: string
}) {
    return (
        <FieldShell label={label} required={required} error={error} hint={hint} className={className}>
            <Input type="file" accept={accept} onChange={onChange} className={cn(fileInputStyle, error && inputErrorClass)} />
            {selectedFileName ? (
                <p className="inline-flex items-center gap-1.5 text-sm font-medium leading-6 text-slate-500">
                    <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                    {selectedFileName}
                </p>
            ) : null}
        </FieldShell>
    )
}

export default function RegisterForm() {
    const router = useRouter()
    const authErrorId = React.useId()
    const [authError, setAuthError] = React.useState<string | null>(null)
    const [success, setSuccess] = React.useState(false)
    const [successTitle, setSuccessTitle] = React.useState("Registration successful")
    const [successDescription, setSuccessDescription] = React.useState("Your account has been created successfully.")
    const [submitState, setSubmitState] = React.useState<"idle" | "posting" | "success" | "error">("idle")
    const [submitNotice, setSubmitNotice] = React.useState<string | null>(null)
    const [showPassword, setShowPassword] = React.useState(false)
    const [showConfirmPassword, setShowConfirmPassword] = React.useState(false)
    const [currentStepId, setCurrentStepId] = React.useState<StepId>("role-setup")

    const {
        form,
        errors,
        metadata,
        isLoadingMetadata,
        metadataError,
        isLoadingLocalities,
        isSubmitting,
        toggleType,
        selectedTypes,
        isSupplier,
        isTenant,
        isCustomer,
        logoFile,
        setLogoFile,
        logoError,
        documentFiles,
        documentNotes,
        documentErrors,
        setRegistrationDocumentFile,
        setRegistrationDocumentNote,
        validateSupplierDocuments,
        validateRegistrationStep,
        registerThirdParty,
    } = useRegisterForm()

    const createUser = form.watch("createUser")
    const businessTypeValue = form.watch("BusinessType")
    const supplierCategoryValue = form.watch("supplier_category_id")
    const countryValue = form.watch("Country")
    const locationValue = form.watch("Location")
    const genderValue = form.watch("user_Gender")
    const maritalStatusValue = form.watch("user_MaritalStatus")
    const occupationValue = form.watch("user_Occupation")

    const showContactPersonFields = isSupplier && isCompanyLikeBusinessType(businessTypeValue)
    const isPosting = submitState === "posting"
    const isBusy = isSubmitting || isPosting
    const formDescriptionIds = [authError ? authErrorId : null].filter(Boolean).join(" ")

    const steps = React.useMemo<StepMeta[]>(() => {
        const dynamicSteps: StepMeta[] = [
            {
                id: "role-setup",
                title: "Roles",
                description: "Choose the portal profiles and whether to create login access.",
                fields: ["types"],
            },
            {
                id: "business",
                title: "Business",
                description: "Register the organization using official business details and primary contact information.",
                fields: [
                    "Name",
                    "TradingName",
                    "BusinessType",
                    "RegistrationNumber",
                    "TaxPIN",
                    "VATNumber",
                    "Email",
                    "Phone",
                    "Country",
                    "Location",
                    "PhysicalAddress",
                    "Website",
                    ...(isSupplier ? ["supplier_category_id"] : []),
                    ...(showContactPersonFields ? ["contactPersonName", "contactPersonEmail", "contactPersonPhone"] : []),
                ],
            },
        ]

        if (isSupplier) {
            dynamicSteps.push({
                id: "supplier-documents",
                title: "Documents",
                description: "Upload supplier documents required by the backend.",
                fields: [],
            })
        }

        if (isTenant || isCustomer) {
            dynamicSteps.push({
                id: "profile-details",
                title: "Profile",
                description: "Provide the extra details required for tenant or customer profiles.",
                fields: [
                    ...(isTenant ? ["user_Remarks"] : []),
                    ...(isCustomer ? ["user_DateOfBirth", "user_MaritalStatus", "user_Occupation", "user_Gender"] : []),
                ],
            })
        }

        if (createUser) {
            dynamicSteps.push({
                id: "user-access",
                title: "Access",
                description: "Create the login credentials for the primary account user.",
                fields: ["user_FirstName", "user_LastName", "user_Email", "user_Phone", "user_Gender", "user_Password", "user_Password_confirmation"],
            })
        }

        return dynamicSteps
    }, [createUser, isCustomer, isSupplier, isTenant, showContactPersonFields])

    React.useEffect(() => {
        if (!steps.some((step) => step.id === currentStepId)) {
            setCurrentStepId(steps[steps.length - 1]?.id ?? "role-setup")
        }
    }, [currentStepId, steps])

    const currentStepIndex = Math.max(steps.findIndex((step) => step.id === currentStepId), 0)
    const currentStep = steps[currentStepIndex]
    const isFirstStep = currentStepIndex === 0
    const isLastStep = currentStepIndex === steps.length - 1

    const getStepFieldErrors = React.useCallback((fields: string[]) => {
        const errorsMap = form.formState.errors as Record<string, { message?: string }>
        const messages: string[] = []
        let firstField: string | null = null

        for (const field of fields) {
            if (errorsMap[field]?.message) {
                if (!firstField) firstField = field
                messages.push(errorsMap[field].message as string)
            }
        }

        if (currentStep?.id === "supplier-documents") {
            for (const message of Object.values(documentErrors)) {
                if (message) messages.push(message)
            }
        }

        if (messages.length === 0) {
            const fallbackError = Object.entries(errorsMap)[0]
            if (fallbackError?.[1]?.message) {
                firstField = fallbackError[0]
                messages.push(fallbackError[1].message)
            }
        }

        return {
            firstField,
            messages: Array.from(new Set(messages)),
        }
    }, [currentStep?.id, documentErrors, form.formState.errors])

    const extractRegisterMessage = React.useCallback((payload?: Record<string, any> | null) => {
        const candidates = [payload?.message, payload?.data?.message]
        for (const candidate of candidates) {
            if (typeof candidate === "string" && candidate.trim()) return candidate.trim()
        }
        return ""
    }, [])

    const pickVerificationEmail = React.useCallback((values: RegisterFormInputs) => {
        const userEmail = values.user_Email?.trim()
        if (userEmail) return userEmail
        const organizationEmail = values.Email?.trim()
        return organizationEmail || null
    }, [])

    const handleRegistrationSuccess = React.useCallback((values: RegisterFormInputs, registerResponse: RegisterThirdPartyResult) => {
        const backendMessage = extractRegisterMessage(registerResponse.payload)
        const verificationRequired =
            Boolean(registerResponse.verifyEmailUrl) ||
            values.createUser ||
            /verify|verification|confirm.+email/i.test(backendMessage)
        const verificationEmail = pickVerificationEmail(values)

        setSubmitState("success")
        setSubmitNotice("Registration completed.")

        if (verificationRequired) {
            setSuccessTitle("Check your email")
            setSuccessDescription(
                verificationEmail
                    ? `We sent a verification link to ${verificationEmail}.`
                    : "We sent a verification link to your email address.",
            )
        } else {
            setSuccessTitle("Registration successful")
            setSuccessDescription("Your account has been created successfully.")
        }

        setSuccess(true)
    }, [extractRegisterMessage, pickVerificationEmail])

    const validateCurrentStep = React.useCallback(async () => {
        setAuthError(null)
        setSubmitNotice(null)

        const fields = currentStep?.fields ?? []
        const formValid = fields.length > 0 ? await form.trigger(fields as never) : true
        const documentsValid = currentStep?.id === "supplier-documents" ? validateSupplierDocuments() : true
        const valid = formValid && documentsValid

        if (!valid) {
            const stepErrors = getStepFieldErrors(fields)
            const fallbackMessage = "Please fix the validation errors before continuing."
            const combinedMessage = stepErrors.messages.length > 0
                ? stepErrors.messages.join("\n")
                : fallbackMessage

            setAuthError(combinedMessage)
            if (stepErrors.firstField) form.setFocus(stepErrors.firstField as never)
            return false
        }

        const requiresServerValidation = currentStep?.id === "business"
        if (requiresServerValidation && currentStep) {
            const serverValidation = await validateRegistrationStep(currentStep.id, form.getValues(), fields)
            if (!serverValidation.valid) {
                const stepErrors = getStepFieldErrors(fields)
                const fallbackMessage = serverValidation.message ?? "Please fix the highlighted fields before continuing."
                const combinedMessage = stepErrors.messages.length > 0
                    ? stepErrors.messages.join("\n")
                    : fallbackMessage

                setAuthError(combinedMessage)
                if (stepErrors.firstField) form.setFocus(stepErrors.firstField as never)
                return false
            }
        }

        return valid
    }, [currentStep, form, getStepFieldErrors, validateRegistrationStep, validateSupplierDocuments])

    const handleNextStep = async () => {
        if (isBusy || !currentStep) return
        const valid = await validateCurrentStep()
        if (!valid) return

        const nextStep = steps[currentStepIndex + 1]
        if (nextStep) {
            setCurrentStepId(nextStep.id)
            window.scrollTo({ top: 0, behavior: "smooth" })
        }
    }

    const handlePreviousStep = () => {
        if (isFirstStep) return
        const previousStep = steps[currentStepIndex - 1]
        if (previousStep) {
            setAuthError(null)
            setCurrentStepId(previousStep.id)
            window.scrollTo({ top: 0, behavior: "smooth" })
        }
    }

    const handleSubmitForm = async (event: React.FormEvent) => {
        event.preventDefault()
        if (isBusy) return

        const valid = await validateCurrentStep()
        if (!valid) return

        try {
            setSubmitState("posting")
            setSubmitNotice("Submitting registration")
            const values = form.getValues()
            const registerResponse = await registerThirdParty(values)
            if (registerResponse?.success) handleRegistrationSuccess(values, registerResponse)
        } catch (error: unknown) {
            setSubmitState("error")
            setSubmitNotice("Registration could not be completed.")
            setAuthError(error instanceof Error ? error.message : "Registration could not be completed. Please try again.")
        }
    }

    const getSubmitButtonLabel = () => {
        if (submitState === "posting") return "Submitting registration"
        if (submitState === "error") return "Try Again"
        return "Create Account"
    }

    if (isLoadingMetadata) {
        return (
            <main className="relative flex min-h-dvh items-center bg-slate-50 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                <div className="relative mx-auto flex w-full max-w-6xl items-center justify-center">
                    <div className="w-full px-1 sm:px-2 lg:px-4">
                        <Loading message="Loading form" fullScreen={false} className="py-24" />
                    </div>
                </div>
            </main>
        )
    }

    if (metadataError) {
        return (
            <main className="relative flex min-h-dvh items-center bg-slate-50 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                <div className="relative mx-auto flex w-full max-w-6xl items-center justify-center">
                    <section className="w-full max-w-[820px] rounded-[10px] border border-slate-300 bg-transparent px-5 py-7 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.16)] sm:rounded-[12px] sm:px-8 sm:py-9 lg:px-10 lg:py-10">
                        <AuthAlert id={authErrorId} message={metadataError} />
                    </section>
                </div>
            </main>
        )
    }

    if (success) {
        return (
            <main className="relative flex min-h-dvh items-center bg-slate-50 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                <div className="relative mx-auto flex w-full max-w-5xl items-center justify-center">
                    <section className="w-full max-w-[560px] rounded-[10px] border border-slate-300 bg-transparent px-5 py-7 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.16)] sm:rounded-[12px] sm:px-8 sm:py-9 lg:px-10 lg:py-10">
                        <div className="mx-auto w-full max-w-md space-y-8 text-center">
                            <header className="space-y-5 border-b border-slate-300 pb-6">
                                <div className="space-y-3">
                                    <CheckCircle2 className="mx-auto h-12 w-12 text-emerald-600" />
                                    <h1 className="text-2xl font-bold tracking-tight text-slate-950 sm:text-[2rem]">{successTitle}</h1>
                                    <p className="mx-auto max-w-md rounded-[6px] bg-emerald-50 px-4 py-2.5 text-sm font-medium leading-6 text-emerald-700">{successDescription}</p>
                                </div>
                                <div className="mx-auto h-px w-16 bg-primary/70" />
                            </header>

                            <div className="grid gap-4">
                                <Button onClick={() => router.replace("/signin")} className={submitButtonClass}>
                                    <span className="inline-flex items-center gap-2.5">
                                        <CircleCheck className="h-4 w-4" />
                                        <span>Sign in</span>
                                    </span>
                                </Button>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        )
    }

    return (
        <main className="relative flex min-h-dvh items-center bg-slate-50 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
            <div className="relative mx-auto flex w-full max-w-6xl items-center justify-center">
                <div className="w-full px-1 sm:px-2 lg:px-4">
                    {isPosting ? (
                        <div className="absolute inset-0 z-20 bg-white/85 backdrop-blur-[1px]">
                            <Loading message="Submitting your registration" fullScreen={false} className="h-full bg-transparent py-0" />
                        </div>
                    ) : null}

                    <div className="mx-auto w-full max-w-5xl space-y-9">
                        <header className="space-y-5 border-b border-slate-300 pb-6 text-center">
                            <div className="space-y-3">
                                <h1 className="text-2xl font-bold tracking-tight text-slate-950 sm:text-[2rem]">Create your account</h1>
                                <p className="mx-auto max-w-md rounded-[6px] bg-slate-100 px-4 py-2.5 text-sm font-medium leading-6 text-slate-600">
                                    Complete your registration details to continue to {CLIENT_APP_NAME_STRING}.
                                </p>
                            </div>
                            <div className="mx-auto h-px w-16 bg-primary/70" />
                        </header>

                        <div className="mx-auto w-full max-w-4xl">
                            <form onSubmit={handleSubmitForm} className={cn("grid gap-7", isPosting && "pointer-events-none")} noValidate aria-busy={isPosting} aria-describedby={formDescriptionIds || undefined}>
                                {authError ? <AuthAlert id={authErrorId} message={authError} onDismiss={() => setAuthError(null)} /> : null}
                                {submitNotice && submitState !== "error" ? <StatusAlert message={submitNotice} /> : null}

                                {currentStep?.id === "role-setup" ? (
                                    <section className={sectionCardClass}>
                                        <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
                                            <div className="rounded-[14px] border border-slate-200 bg-white p-4 sm:p-5">
                                                <div className="mb-4 flex items-center justify-between gap-3">
                                                    <h3 className="text-sm font-bold uppercase tracking-[0.08em] text-slate-700">Select profiles</h3>
                                                    <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                        {selectedTypes?.length ?? 0} selected
                                                    </span>
                                                </div>

                                                <div aria-label="Business role" className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3" role="group">
                                                    {ROLE_OPTIONS.map((type) => (
                                                        <button
                                                            key={type.id}
                                                            type="button"
                                                            onClick={() => toggleType(type.id)}
                                                            className={cn(
                                                                "group relative min-h-[84px] rounded-[12px] border px-4 py-3.5 text-left transition-[border-color,color,box-shadow,transform]",
                                                                selectedTypes?.includes(type.id)
                                                                    ? "border-primary text-primary shadow-[0_12px_20px_-18px_rgba(0,92,144,0.42)] ring-1 ring-primary/20"
                                                                    : "border-slate-300 text-slate-700 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-[0_12px_18px_-16px_rgba(15,23,42,0.35)]",
                                                            )}
                                                            aria-pressed={selectedTypes?.includes(type.id)}
                                                            aria-label={type.label}
                                                        >
                                                            <div className="flex h-full items-center gap-3">
                                                                <span
                                                                    className={cn(
                                                                        "inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] border",
                                                                        selectedTypes?.includes(type.id)
                                                                            ? "border-primary/30 bg-primary/10"
                                                                            : "border-slate-200 bg-slate-50",
                                                                    )}
                                                                >
                                                                    <type.icon className={cn("h-5 w-5", selectedTypes?.includes(type.id) ? "text-primary" : "text-slate-500")} />
                                                                </span>
                                                                <span className="grid gap-0.5 pr-6">
                                                                    <span className="text-base font-semibold leading-5 text-current">{type.label}</span>
                                                                </span>
                                                                {selectedTypes?.includes(type.id) ? (
                                                                    <span className="absolute right-2.5 top-2.5 inline-flex items-center justify-center rounded-full bg-primary/10 p-1 text-primary" aria-label="Selected">
                                                                        <CheckCircle2 className="h-3.5 w-3.5" />
                                                                    </span>
                                                                ) : null}
                                                            </div>
                                                        </button>
                                                    ))}
                                                </div>
                                                <div className="mt-2.5">
                                                    <FieldError message={errors.types?.message as string | undefined} />
                                                </div>
                                            </div>

                                            <div className="rounded-[14px] border border-slate-200 bg-white p-4 sm:p-5">
                                                <div className="space-y-1">
                                                    <h3 className="text-sm font-bold uppercase tracking-[0.08em] text-slate-700">Account User</h3>
                                                </div>

                                                <div className="mt-4">
                                                    <Select
                                                        value={createUser ? "create" : "skip"}
                                                        onValueChange={(value) => form.setValue("createUser", value === "create", { shouldDirty: true, shouldValidate: true })}
                                                    >
                                                        <SelectTrigger className={cn(selectStyle, "h-12 rounded-[10px] border-slate-300 bg-white font-semibold text-slate-900")}>
                                                            <SelectValue placeholder="Select login access" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="create">Set Up Login</SelectItem>
                                                            <SelectItem value="skip">Skip for now</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                ) : null}

                                {currentStep?.id === "business" ? (
                                    <section className={sectionCardClass}>
                                        <SectionTitle title="Business" icon={BriefcaseBusiness} />
                                        <div className={formSectionGridClass}>
                                            <div className="md:col-span-2">
                                                <FieldLabel required>Legal Name</FieldLabel>
                                                <Input required autoComplete="organization" {...form.register("Name")} placeholder="Daniel Logistics Limited" className={cn(inputStyle, errors.Name && inputErrorClass)} />
                                                <FieldError message={errors.Name?.message as string | undefined} />
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel>Trading Name</FieldLabel>
                                                <Input {...form.register("TradingName")} placeholder="Daniel Logistics" className={cn(inputStyle, errors.TradingName && inputErrorClass)} />
                                                <FieldError message={errors.TradingName?.message as string | undefined} />
                                            </div>

                                            <div className="md:col-span-2 grid gap-4 lg:grid-cols-2">
                                                <SelectFieldBlock
                                                    label="Business Type"
                                                    required
                                                    value={businessTypeValue || undefined}
                                                    onValueChange={(value) => form.setValue("BusinessType", value, { shouldDirty: true, shouldValidate: true })}
                                                    options={metadata.businessTypes.map((item) => ({ value: item.value, label: item.label }))}
                                                    placeholder="Select business type"
                                                    error={errors.BusinessType?.message as string | undefined}
                                                />

                                                {isSupplier ? (
                                                    <SelectFieldBlock
                                                        label="Supplier Category"
                                                        required
                                                        value={supplierCategoryValue != null ? String(supplierCategoryValue) : undefined}
                                                        onValueChange={(value) => form.setValue("supplier_category_id", Number(value), { shouldDirty: true, shouldValidate: true })}
                                                        options={metadata.supplierCategories.map((item) => ({ value: String(item.id), label: item.name }))}
                                                        placeholder="Select supplier category"
                                                        error={errors.supplier_category_id?.message as string | undefined}
                                                    />
                                                ) : null}
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>Registration Number</FieldLabel>
                                                <Input required {...form.register("RegistrationNumber")} placeholder="REG-12345" className={cn(inputStyle, errors.RegistrationNumber && inputErrorClass)} />
                                                <FieldError message={errors.RegistrationNumber?.message as string | undefined} />
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>Tax PIN</FieldLabel>
                                                <Input required {...form.register("TaxPIN")} placeholder="A001234567X" className={cn(inputStyle, errors.TaxPIN && inputErrorClass)} />
                                                <FieldError message={errors.TaxPIN?.message as string | undefined} />
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>VAT Number</FieldLabel>
                                                <Input required {...form.register("VATNumber")} placeholder="VAT-00991" className={cn(inputStyle, errors.VATNumber && inputErrorClass)} />
                                                <FieldError message={errors.VATNumber?.message as string | undefined} />
                                            </div>

                                            {isSupplier && showContactPersonFields ? (
                                                <>
                                                    <div className={fieldBlockClass}>
                                                        <FieldLabel required>Contact Person Name</FieldLabel>
                                                        <Input required {...form.register("contactPersonName")} placeholder="Jane Doe" className={cn(inputStyle, errors.contactPersonName && inputErrorClass)} />
                                                        <FieldError message={errors.contactPersonName?.message as string | undefined} />
                                                    </div>

                                                    <div className={fieldBlockClass}>
                                                        <FieldLabel required>Contact Person Email</FieldLabel>
                                                        <Input type="email" required {...form.register("contactPersonEmail")} placeholder="jane@company.com" className={cn(inputStyle, errors.contactPersonEmail && inputErrorClass)} />
                                                        <FieldError message={errors.contactPersonEmail?.message as string | undefined} />
                                                    </div>

                                                    <div className={fieldBlockClass}>
                                                        <FieldLabel required>Contact Person Phone</FieldLabel>
                                                        <Input type="tel" inputMode="tel" pattern="[+]?[0-9]{8,15}" required {...form.register("contactPersonPhone")} placeholder="+254711111111" className={cn(inputStyle, errors.contactPersonPhone && inputErrorClass)} />
                                                        <FieldError message={errors.contactPersonPhone?.message as string | undefined} />
                                                    </div>
                                                </>
                                            ) : null}

                                            <div className="md:col-span-2 mt-2 border-t border-slate-200 pt-5">
                                                <div className="space-y-1.5">
                                                    <h3 className="text-sm font-bold uppercase tracking-[0.08em] text-slate-700">Primary Contact</h3>
                                                    <p className="text-sm font-medium leading-6 text-slate-500">These fields are required by the registration backend for the business profile.</p>
                                                </div>
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel>Business Email</FieldLabel>
                                                <Input type="email" autoComplete="email" {...form.register("Email")} placeholder="procurement@company.com" className={cn(inputStyle, errors.Email && inputErrorClass)} />
                                                <FieldError message={errors.Email?.message as string | undefined} />
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>Phone Number</FieldLabel>
                                                <Input type="tel" inputMode="tel" pattern="[+]?[0-9]{8,15}" required autoComplete="tel" {...form.register("Phone")} placeholder="+254712345678" className={cn(inputStyle, errors.Phone && inputErrorClass)} />
                                                <p className="text-sm font-medium leading-6 text-slate-500">Use 8-15 digits, with optional +.</p>
                                                <FieldError message={errors.Phone?.message as string | undefined} />
                                            </div>

                                            <SelectFieldBlock
                                                label="Country"
                                                required
                                                value={countryValue || undefined}
                                                onValueChange={(value) => form.setValue("Country", value, { shouldDirty: true, shouldValidate: true })}
                                                options={metadata.countries.map((item) => ({ value: item.code, label: item.name }))}
                                                placeholder="Select country"
                                                error={errors.Country?.message as string | undefined}
                                            />

                                            <SelectFieldBlock
                                                label="Location"
                                                required
                                                value={locationValue != null ? String(locationValue) : undefined}
                                                onValueChange={(value) => form.setValue("Location", Number(value), { shouldDirty: true, shouldValidate: true })}
                                                options={metadata.localities.map((item) => ({ value: String(item.id), label: item.name }))}
                                                placeholder={isLoadingLocalities ? "Loading locations" : "Select location"}
                                                disabled={isLoadingLocalities || metadata.localities.length === 0}
                                                error={errors.Location?.message as string | undefined}
                                            />

                                            <div className={cn(fieldBlockClass, "md:col-span-2")}>
                                                <FieldLabel>Physical Address</FieldLabel>
                                                <Input {...form.register("PhysicalAddress")} placeholder="Building, street, city" className={cn(inputStyle, errors.PhysicalAddress && inputErrorClass)} />
                                                <FieldError message={errors.PhysicalAddress?.message as string | undefined} />
                                            </div>

                                            <div className={cn(fieldBlockClass, "md:col-span-2")}>
                                                <FieldLabel>Website</FieldLabel>
                                                <Input {...form.register("Website")} placeholder="https://example.com" className={cn(inputStyle, errors.Website && inputErrorClass)} />
                                                <p className="text-sm font-medium leading-6 text-slate-500">If provided, the URL must start with https://.</p>
                                                <FieldError message={errors.Website?.message as string | undefined} />
                                            </div>

                                            <FileFieldBlock
                                                label="Company Logo"
                                                accept="image/*"
                                                onChange={(event) => setLogoFile(event.target.files?.[0] ?? null)}
                                                selectedFileName={logoFile?.name}
                                                error={logoError}
                                                className="md:col-span-2"
                                            />
                                        </div>
                                    </section>
                                ) : null}

                                {currentStep?.id === "supplier-documents" ? (
                                    <section className={sectionCardClass}>
                                        <SectionTitle title="Supplier Documents" icon={FileCheck2} description="Upload the supporting documents required for supplier registration." />
                                        <div className="grid gap-5">
                                            {metadata.supplierDocumentRequirements.length > 0 ? (
                                                metadata.supplierDocumentRequirements.map((requirement) => {
                                                    const selectedFile = documentFiles[requirement.id]
                                                    const noteValue = documentNotes[requirement.id] ?? ""
                                                    const allowedExtensions = requirement.allowedExtensions
                                                        .map((extension) => extension.trim())
                                                        .filter(Boolean)
                                                    const accept = allowedExtensions.length > 0
                                                        ? allowedExtensions.map((extension) => (extension.startsWith(".") ? extension : `.${extension}`)).join(",")
                                                        : undefined
                                                    const metadataHint = [
                                                        allowedExtensions.length > 0 ? `Allowed: ${allowedExtensions.join(", ")}` : null,
                                                        requirement.maxFileSizeKb ? `Max size: ${requirement.maxFileSizeKb} KB` : null,
                                                    ].filter(Boolean).join(". ")

                                                    return (
                                                        <div key={requirement.id} className="rounded-[14px] border border-slate-200 bg-white p-4 sm:p-5">
                                                            <div className="space-y-1.5">
                                                                <h3 className="text-sm font-bold uppercase tracking-[0.08em] text-slate-700">
                                                                    {requirement.name}
                                                                    {requirement.isRequired ? <span className="text-rose-600"> *</span> : null}
                                                                </h3>
                                                                {requirement.description ? (
                                                                    <p className="text-sm font-medium leading-6 text-slate-600">{requirement.description}</p>
                                                                ) : null}
                                                                {metadataHint ? (
                                                                    <p className="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">{metadataHint}</p>
                                                                ) : null}
                                                            </div>

                                                            <div className="mt-4 grid gap-4 md:grid-cols-2 md:items-start">
                                                                <FileFieldBlock
                                                                    label="Document File"
                                                                    required={requirement.isRequired}
                                                                    accept={accept}
                                                                    onChange={(event) => setRegistrationDocumentFile(requirement.id, event.target.files?.[0] ?? null)}
                                                                    selectedFileName={selectedFile?.name ?? null}
                                                                    error={documentErrors[requirement.id]}
                                                                />

                                                                <FieldShell
                                                                    label="Document Note"
                                                                    hint="Optional note for reviewers or document context."
                                                                >
                                                                    <Textarea
                                                                        value={noteValue}
                                                                        onChange={(event) => setRegistrationDocumentNote(requirement.id, event.target.value)}
                                                                        placeholder="Add a note for this document"
                                                                        className={textAreaStyle}
                                                                    />
                                                                </FieldShell>
                                                            </div>
                                                        </div>
                                                    )
                                                })
                                            ) : (
                                                <div className="rounded-[14px] border border-dashed border-slate-300 bg-white p-5">
                                                    <p className="text-sm font-medium leading-6 text-slate-600">
                                                        No supplier document requirements were returned for the current registration metadata.
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </section>
                                ) : null}

                                {currentStep?.id === "profile-details" ? (
                                    <section className={sectionCardClass}>
                                        <SectionTitle title="Profile Details" icon={UserCog} />
                                        <div className={formSectionGridClass}>
                                            {isTenant ? (
                                                <div className="md:col-span-2">
                                                    <FieldLabel required>Tenant Remarks</FieldLabel>
                                                    <Textarea
                                                        required
                                                        {...form.register("user_Remarks")}
                                                        placeholder="Primary tenant account"
                                                        className={cn(textAreaStyle, errors.user_Remarks && inputErrorClass)}
                                                    />
                                                    <FieldError message={errors.user_Remarks?.message as string | undefined} />
                                                </div>
                                            ) : null}

                                            {isCustomer ? (
                                                <>
                                                    <div className={fieldBlockClass}>
                                                        <FieldLabel required>Date of Birth</FieldLabel>
                                                        <Input type="date" required {...form.register("user_DateOfBirth")} className={cn(inputStyle, errors.user_DateOfBirth && inputErrorClass)} />
                                                        <FieldError message={errors.user_DateOfBirth?.message as string | undefined} />
                                                    </div>

                                                    <SelectFieldBlock
                                                        label="Marital Status"
                                                        required
                                                        value={maritalStatusValue || undefined}
                                                        onValueChange={(value) => form.setValue("user_MaritalStatus", value, { shouldDirty: true, shouldValidate: true })}
                                                        options={metadata.maritalStatuses.map((item) => ({ value: item.value, label: item.label }))}
                                                        placeholder="Select marital status"
                                                        error={errors.user_MaritalStatus?.message as string | undefined}
                                                    />

                                                    <SelectFieldBlock
                                                        label="Occupation"
                                                        required
                                                        value={occupationValue || undefined}
                                                        onValueChange={(value) => form.setValue("user_Occupation", value, { shouldDirty: true, shouldValidate: true })}
                                                        options={metadata.occupations.map((item) => ({ value: item.value, label: item.label }))}
                                                        placeholder="Select occupation"
                                                        error={errors.user_Occupation?.message as string | undefined}
                                                    />

                                                    <SelectFieldBlock
                                                        label="Gender"
                                                        required
                                                        value={genderValue || undefined}
                                                        onValueChange={(value) => form.setValue("user_Gender", value, { shouldDirty: true, shouldValidate: true })}
                                                        options={metadata.genders.map((item) => ({ value: item.value, label: item.label }))}
                                                        placeholder="Select gender"
                                                        error={errors.user_Gender?.message as string | undefined}
                                                    />
                                                </>
                                            ) : null}
                                        </div>
                                    </section>
                                ) : null}

                                {currentStep?.id === "user-access" ? (
                                    <section className={sectionCardClass}>
                                        <SectionTitle title="User Access" icon={ShieldCheck} />
                                        <div className={formSectionGridClass}>
                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>First Name</FieldLabel>
                                                <Input required autoComplete="given-name" {...form.register("user_FirstName")} placeholder="Jane" className={cn(inputStyle, errors.user_FirstName && inputErrorClass)} />
                                                <FieldError message={errors.user_FirstName?.message as string | undefined} />
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>Last Name</FieldLabel>
                                                <Input required autoComplete="family-name" {...form.register("user_LastName")} placeholder="Doe" className={cn(inputStyle, errors.user_LastName && inputErrorClass)} />
                                                <FieldError message={errors.user_LastName?.message as string | undefined} />
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>User Email</FieldLabel>
                                                <Input type="email" required autoComplete="email" {...form.register("user_Email")} placeholder="admin@company.com" className={cn(inputStyle, errors.user_Email && inputErrorClass)} />
                                                <FieldError message={errors.user_Email?.message as string | undefined} />
                                            </div>

                                            <div className={fieldBlockClass}>
                                                <FieldLabel required>User Phone</FieldLabel>
                                                <Input type="tel" inputMode="tel" pattern="[+]?[0-9]{8,15}" required autoComplete="tel" {...form.register("user_Phone")} placeholder="+254711111111" className={cn(inputStyle, errors.user_Phone && inputErrorClass)} />
                                                <FieldError message={errors.user_Phone?.message as string | undefined} />
                                            </div>

                                            {!isCustomer ? (
                                                <SelectFieldBlock
                                                    label="Gender"
                                                    required
                                                    value={genderValue || undefined}
                                                    onValueChange={(value) => form.setValue("user_Gender", value, { shouldDirty: true, shouldValidate: true })}
                                                    options={metadata.genders.map((item) => ({ value: item.value, label: item.label }))}
                                                    placeholder="Select gender"
                                                    error={errors.user_Gender?.message as string | undefined}
                                                    className="md:col-span-2"
                                                />
                                            ) : null}

                                            <div className="relative grid content-start gap-2.5">
                                                <FieldLabel required>Password</FieldLabel>
                                                <Input
                                                    type={showPassword ? "text" : "password"}
                                                    required
                                                    autoComplete="new-password"
                                                    {...form.register("user_Password")}
                                                    placeholder="At least 8 characters"
                                                    className={cn(inputStyle, errors.user_Password && inputErrorClass)}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => setShowPassword(!showPassword)}
                                                    className={endButtonClass}
                                                    aria-label={showPassword ? "Hide password" : "Show password"}
                                                >
                                                    {showPassword ? <EyeClosed className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                </button>
                                                <FieldError message={errors.user_Password?.message as string | undefined} />
                                            </div>

                                            <div className="relative grid content-start gap-2.5">
                                                <FieldLabel required>Confirm Password</FieldLabel>
                                                <Input
                                                    type={showConfirmPassword ? "text" : "password"}
                                                    required
                                                    autoComplete="new-password"
                                                    {...form.register("user_Password_confirmation")}
                                                    placeholder="Repeat password"
                                                    className={cn(inputStyle, errors.user_Password_confirmation && inputErrorClass)}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                                    className={endButtonClass}
                                                    aria-label={showConfirmPassword ? "Hide confirm password" : "Show confirm password"}
                                                >
                                                    {showConfirmPassword ? <EyeClosed className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                </button>
                                                <FieldError message={errors.user_Password_confirmation?.message as string | undefined} />
                                            </div>
                                        </div>
                                    </section>
                                ) : null}

                                <div className="grid gap-4 border-t border-slate-300 pt-7 sm:grid-cols-2">
                                    <Button type="button" onClick={handlePreviousStep} disabled={isFirstStep || isBusy} className={secondaryButtonClass}>
                                        <span className="inline-flex items-center gap-2.5">
                                            <ChevronLeft className="h-4 w-4" />
                                            <span>Previous</span>
                                        </span>
                                    </Button>

                                    {isLastStep ? (
                                        <Button type="submit" disabled={isBusy} className={submitButtonClass}>
                                            {isBusy ? (
                                                <span className="inline-flex items-center gap-2.5">
                                                    <span className="loader-bars loader-bars--inline [&>span]:bg-white [&>span]:shadow-none" aria-hidden="true">
                                                        <span />
                                                        <span />
                                                        <span />
                                                    </span>
                                                    <span>{getSubmitButtonLabel()}</span>
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-2.5">
                                                    <CircleCheck className="h-4 w-4 transition-transform duration-200 group-hover:scale-105" />
                                                    <span>{getSubmitButtonLabel()}</span>
                                                </span>
                                            )}
                                        </Button>
                                    ) : (
                                        <Button type="button" onClick={handleNextStep} disabled={isBusy} className={submitButtonClass}>
                                            <span className="inline-flex items-center gap-2.5">
                                                <span>Next Step</span>
                                                <ChevronRight className="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                                            </span>
                                        </Button>
                                    )}
                                </div>

                            </form>
                        </div>

                        <div className="mx-auto grid w-full max-w-4xl gap-4 border-t border-slate-300 pt-7 text-left">
                            <Link href="/signin" className={cn(signInLinkClass, linkClass)}>
                                <span>Already registered? Sign in</span>
                                <ChevronRight className="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    )
}
