"use client"

import * as React from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import {
    AlertCircle,
    ArrowRight,
    Check,
    CheckCircle2,
    ChevronLeft,
    ChevronsUpDown,
    CircleCheck,
    Eye,
    EyeClosed,
    UserCog,
    X,
} from "lucide-react"

import { Button } from "../common/button"
import { Field as SharedField, FieldLabel as SharedFieldLabel, FieldLegend, FieldSet } from "../common/field"
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "../common/dialog"
import { Alert, AlertDescription, AlertTitle } from "../common/alert"
import {
    Command,
    CommandEmpty,
    CommandInput,
    CommandItem,
    CommandList,
} from "../common/command"
import { Input } from "../common/input"
import { Popover, PopoverContent, PopoverTrigger } from "../common/popover"
import { RadioGroup, RadioGroupItem } from "../common/radio-group"
import { Textarea } from "../common/textarea"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "../common/select"
import { toast } from "sonner"
import { cn } from "../../lib/utils"
import {
    buildInternationalPhoneNumber,
    extractNationalPhoneNumber,
    normalizeCountryDialCode,
} from "../../lib/register-shared"
import { SYSTEM_ERROR_MESSAGE, type RegisterFormInputs, type RegisterRole, type RegisterThirdPartyResult, useRegisterForm } from "../../hooks/use-register"

const ROLE_OPTIONS: Array<{ id: RegisterRole; label: string }> = [
    { id: "SU", label: "Supplier" },
    { id: "TN", label: "Tenant" },
    { id: "CU", label: "Customer" },
]

const labelStyle = "flex min-h-5 items-center gap-2 text-xs font-bold uppercase tracking-[0.1em] text-slate-600"
const inputBaseClass = "h-11 rounded-lg border border-slate-200 bg-white px-3.5 text-sm font-medium text-slate-950 transition-[border-color,background-color,box-shadow] placeholder:text-sm placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 focus-visible:border-blue-500 focus-visible:ring-1 focus-visible:ring-blue-100 focus-visible:shadow-[0_0_0_3px_rgba(59,130,246,0.1)]"
const inputErrorClass = "border-red-300 bg-red-50 focus-visible:border-red-500 focus-visible:ring-red-100 focus-visible:shadow-none hover:border-red-300"
const inputStyle = `${inputBaseClass} pr-10`
const textAreaStyle = "min-h-28 rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-950 transition-[border-color,background-color,box-shadow] placeholder:text-sm placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 focus-visible:border-blue-500 focus-visible:ring-1 focus-visible:ring-blue-100 focus-visible:shadow-[0_0_0_3px_rgba(59,130,246,0.1)]"
const fileInputStyle = "h-11 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-[border-color,background-color,box-shadow] file:mr-2.5 file:rounded-md file:border-0 file:bg-slate-100 file:px-2.5 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:border-slate-300 focus-visible:border-blue-500 focus-visible:ring-1 focus-visible:ring-blue-100"
const selectStyle = "h-11 w-full rounded-lg border border-slate-200 bg-white px-3.5 text-sm font-medium text-slate-950 transition-[border-color,background-color,box-shadow] hover:border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-100 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.1)]"
const endButtonClass = "absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
const navigationPrimaryButtonClass = "h-11 w-full rounded-lg border border-slate-950 bg-slate-950 px-5 text-sm font-semibold text-white transition-[background-color,border-color,color] duration-150 hover:border-slate-800 hover:bg-slate-800 disabled:cursor-not-allowed disabled:border-slate-300 disabled:bg-slate-300 disabled:text-white/90"
const navigationSecondaryButtonClass = "h-11 w-full rounded-lg border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900 transition-[border-color,background-color,color] duration-150 hover:border-slate-400 hover:bg-slate-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"
const linkClass = "text-blue-600 transition-colors duration-150 hover:text-blue-700 hover:underline"
const sectionCardClass = "pt-4 sm:pt-5 lg:pt-6"
const formSectionGridClass = "mt-4 grid grid-cols-1 gap-4 sm:mt-5 sm:gap-5 md:grid-cols-2 md:items-start"
const fieldBlockClass = "grid content-start gap-2"
const popoverTriggerClass = "h-11 w-full justify-between rounded-lg border-slate-200 bg-white px-3.5 text-sm font-medium shadow-none hover:bg-white"
const popoverContentClass = "w-[var(--radix-popover-trigger-width)] min-w-[var(--radix-popover-trigger-width)] border-slate-200 bg-white p-0 shadow-none"
const popoverItemClass = "gap-2.5 px-3 py-2.5"

type StepId = "business" | "account" | "type-specific"

type StepMeta = {
    id: StepId
    title: string
    description: string
    fields: string[]
}

const SERVER_STEP_BY_FORM_STEP: Record<StepId, "business" | "account" | "supplier"> = {
    business: "business",
    account: "account",
    "type-specific": "supplier",
}

const FIELD_LABELS: Partial<Record<string, string>> = {
    types: "business role",
    Name: "legal name",
    TradingName: "trading name",
    BusinessType: "business type",
    category_ids: "supplier categories",
    RegistrationNumber: "registration number",
    TaxPIN: "tax PIN",
    VATNumber: "VAT number",
    Email: "business email",
    Phone: "company phone number",
    PhysicalAddress: "physical address",
    Website: "website",
    Country: "country",
    Location: "location",
    registration_documents: "supplier documents",
    registration_document_notes: "document notes",
    user_Remarks: "tenant remarks",
    user_DateOfBirth: "date of birth",
    user_MaritalStatus: "marital status",
    user_Occupation: "occupation",
    user_Gender: "gender",
    user_FirstName: "first name",
    user_LastName: "last name",
    user_Email: "email",
    user_Phone: "phone number",
    user_Password: "password",
    user_Password_confirmation: "confirm password",
}

const SELECT_FIELDS = new Set(["BusinessType", "Country", "Location", "user_MaritalStatus", "user_Occupation", "user_Gender"])
const GENERIC_ERROR_PATTERN = /^(required|validation error|this field is required\.?|please review this field\.?)$/i

const getFieldLabel = (field?: string, fallback?: string) => {
    if (fallback) return fallback
    if (!field) return "this field"
    return FIELD_LABELS[field] ?? field.replace(/_/g, " ").toLowerCase()
}

const getFieldPrompt = (field?: string, fallback?: string) => {
    const label = getFieldLabel(field, fallback)
    if (SELECT_FIELDS.has(String(field))) return `Select ${label}.`
    return `Enter ${label}.`
}

const normalizeErrorMessage = (message?: string | null, label?: string, showGeneric = true) => {
    if (!message) return null

    const normalized = message.trim().replace(/\s+/g, " ")
    if (!normalized) return null
    if (GENERIC_ERROR_PATTERN.test(normalized)) {
        if (!showGeneric) return null
        return label ? `${label} is required.` : "This field is required."
    }

    return normalized
}

function FeedbackAlert({
    id,
    tone = "error",
    title,
    message,
    onDismiss,
    summarize = false,
}: {
    id?: string
    tone?: "error" | "success"
    title?: string
    message: string
    onDismiss?: () => void
    summarize?: boolean
}) {
    const items = message
        .split("\n")
        .map((item) => item.trim())
        .filter(Boolean)
        .map((item) => item.replace(/^-+\s*/, ""))
        .map((item) => normalizeErrorMessage(item, undefined, false) ?? item)

    const issueCount = items.length
    const computedTitle = title ?? (tone === "success"
        ? "Success"
        : issueCount > 1
            ? `Review ${issueCount} fields`
            : "Please review")
    const Icon = tone === "success" ? CheckCircle2 : AlertCircle

    return (
        <Alert
            id={id}
            variant={tone === "error" ? "destructive" : "default"}
            aria-live={tone === "error" ? "assertive" : "polite"}
            aria-atomic="true"
            className={cn(
                "relative rounded-xl border px-4 py-4 pr-10 shadow-none",
                tone === "error"
                    ? "border-red-200 bg-red-50/90 text-red-950"
                    : "border-emerald-200 bg-emerald-50/90 text-emerald-950"
            )}
        >
            <Icon className={cn("h-4 w-4", tone === "error" ? "text-red-600" : "text-emerald-600")} />
            <AlertTitle className={cn("text-sm font-semibold", tone === "error" ? "text-red-950" : "text-emerald-950")}>
                {computedTitle}
            </AlertTitle>
            {!summarize ? (
                <AlertDescription className={cn("mt-1.5 text-sm leading-5", tone === "error" ? "text-red-900" : "text-emerald-900")}>
                    {items.length > 1 ? (
                        <ul className="list-disc space-y-0.5 pl-5">
                            {items.map((item, index) => (
                                <li key={`${item}-${index}`}>{item}</li>
                            ))}
                        </ul>
                    ) : (
                        <p>{items[0] || message}</p>
                    )}
                </AlertDescription>
            ) : null}
            {onDismiss ? (
                <button
                    type="button"
                    onClick={onDismiss}
                    className={cn(
                        "absolute right-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-md transition-colors",
                        tone === "error"
                            ? "text-red-400 hover:bg-red-100 hover:text-red-700"
                            : "text-emerald-500 hover:bg-emerald-100 hover:text-emerald-700"
                    )}
                    aria-label="Dismiss alert"
                >
                    <X className="h-4 w-4" />
                </button>
            ) : null}
        </Alert>
    )
}

function FieldLabel({ children, required }: { children: React.ReactNode; required?: boolean }) {
    return (
        <label className={labelStyle}>
            {children}
            {required ? <span className="text-red-500"> *</span> : null}
        </label>
    )
}

function FieldError({ message, label, touched = true }: { message?: string | null; label?: string; touched?: boolean }) {
    const normalized = normalizeErrorMessage(message, label)
    if (!normalized || !touched) return null

    return (
        <p role="alert" className="text-xs font-medium text-red-600">
            {normalized}
        </p>
    )
}

function FieldShell({
    label,
    errorLabel,
    touched,
    required,
    error,
    className,
    children,
}: {
    label: React.ReactNode
    errorLabel?: string
    touched?: boolean
    required?: boolean
    error?: string | null
    className?: string
    children: React.ReactNode
}) {
    return (
        <div className={cn("grid gap-1.5", className)}>
            <FieldLabel required={required}>{label}</FieldLabel>
            {children}
            <FieldError message={error} label={errorLabel} touched={touched} />
        </div>
    )
}

function CenteredAuthLoading({ label, className }: { label: string; className?: string }) {
    return (
        <div className={cn("flex h-full min-h-[12rem] w-full items-center justify-center", className)}>
            <div className="flex flex-col items-center justify-center gap-3 text-center">
                <span className="loader-bars loader-bars--inline [&>span]:bg-slate-900 [&>span]:shadow-none" aria-hidden="true">
                    <span />
                    <span />
                    <span />
                </span>
                <p className="text-sm font-medium text-slate-600">{label}</p>
            </div>
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
    touched,
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
    touched?: boolean
    className?: string
}) {
    const controlledValue = value ?? ""

    return (
        <FieldShell label={label} errorLabel={typeof label === "string" ? label : undefined} touched={touched} required={required} error={error} className={className}>
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

function CompactComboboxField({
    label,
    required,
    error,
    placeholder,
    value,
    onChange,
    options,
    disabled,
    touched,
    className,
    searchPlaceholder,
    emptyMessage,
}: {
    label: React.ReactNode
    required?: boolean
    error?: string | null
    placeholder: string
    value?: string
    onChange: (value: string) => void
    options: Array<{ value: string; label: string }>
    disabled?: boolean
    touched?: boolean
    className?: string
    searchPlaceholder?: string
    emptyMessage?: string
}) {
    const [open, setOpen] = React.useState(false)
    const selectedOption = options.find((option) => option.value === value)

    return (
        <FieldShell label={label} errorLabel={typeof label === "string" ? label : undefined} touched={touched} required={required} error={error} className={className}>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={disabled}
                        className={cn(
                            popoverTriggerClass,
                            error && inputErrorClass,
                        )}
                        aria-required={required ? "true" : undefined}
                    >
                        <span className={cn("truncate", selectedOption ? "text-slate-950" : "text-slate-500")}>
                            {selectedOption?.label ?? placeholder}
                        </span>
                        <ChevronsUpDown className="h-4 w-4 text-slate-400" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className={popoverContentClass} align="start">
                    <Command className="rounded-lg bg-white">
                        <CommandInput placeholder={searchPlaceholder ?? placeholder} />
                        <CommandList className="max-h-64">
                            <CommandEmpty>{emptyMessage ?? "No results found."}</CommandEmpty>
                            {options.map((option) => {
                                const selected = option.value === value

                                return (
                                    <CommandItem
                                        key={option.value}
                                        value={option.label}
                                        onSelect={() => {
                                            onChange(option.value)
                                            setOpen(false)
                                        }}
                                        className={popoverItemClass}
                                    >
                                        <Check className={cn("h-4 w-4", selected ? "text-primary opacity-100" : "opacity-0")} />
                                        <span className="truncate text-sm text-slate-700">{option.label}</span>
                                    </CommandItem>
                                )
                            })}
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
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
    touched,
    className,
}: {
    label: React.ReactNode
    required?: boolean
    error?: string | null
    accept?: string
    onChange: (event: React.ChangeEvent<HTMLInputElement>) => void
    selectedFileName?: string | null
    touched?: boolean
    className?: string
}) {
    return (
        <FieldShell label={label} errorLabel={typeof label === "string" ? label : undefined} touched={touched} required={required} error={error} className={className}>
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

function RoleSelector({
    selectedTypes,
    onToggle,
    error,
}: {
    selectedTypes: RegisterRole[]
    onToggle: (role: RegisterRole) => void
    error?: string | null
}) {
    const [open, setOpen] = React.useState(false)
    const selectedRoles = ROLE_OPTIONS.filter((option) => selectedTypes.includes(option.id))
    const summaryLabel = selectedRoles.length === 0
        ? "Select business profiles"
        : selectedRoles.length === 1
            ? selectedRoles[0].label
            : `${selectedRoles[0].label} +${selectedRoles.length - 1}`

    return (
        <FieldSet className="w-full gap-3">
            <FieldLegend variant="label" className="mb-0 text-sm font-bold uppercase tracking-[0.08em] text-slate-800">
                Choose a profile
            </FieldLegend>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        className={cn(
                            popoverTriggerClass,
                            error && inputErrorClass,
                        )}
                    >
                        <span className={cn("truncate", selectedRoles.length > 0 ? "text-slate-950" : "text-slate-500")}>
                            {summaryLabel}
                        </span>
                        <ChevronsUpDown className="h-4 w-4 text-slate-400" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className={popoverContentClass} align="start">
                    <Command className="rounded-lg bg-white">
                        <CommandInput placeholder="Search profiles" />
                        <CommandList className="max-h-64">
                            <CommandEmpty>No profiles found.</CommandEmpty>
                            {ROLE_OPTIONS.map((option) => {
                                const selected = selectedTypes.includes(option.id)

                                return (
                                    <CommandItem
                                        key={option.id}
                                        value={option.label}
                                        onSelect={() => onToggle(option.id)}
                                        className={popoverItemClass}
                                    >
                                        <Check className={cn("h-4 w-4", selected ? "text-primary opacity-100" : "opacity-0")} />
                                        <span className="truncate text-sm text-slate-700">{option.label}</span>
                                    </CommandItem>
                                )
                            })}
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>

            <div className="min-h-5">
                {error ? <FieldError message={error} label="Business role" /> : null}
            </div>
        </FieldSet>
    )
}

function CreateUserSelector({
    value,
    onChange,
}: {
    value: boolean
    onChange: (nextValue: boolean) => void
}) {
    return (
        <FieldSet className="w-full gap-3">
            <FieldLegend variant="label" className="mb-0 text-sm font-bold uppercase tracking-[0.08em] text-slate-800">
                Create a user?
            </FieldLegend>
            <RadioGroup
                value={value ? "yes" : "no"}
                onValueChange={(nextValue) => onChange(nextValue === "yes")}
                className="gap-2"
            >
                <SharedField
                    orientation="horizontal"
                    className={cn(
                        "min-h-0 cursor-pointer items-center gap-2 py-1 shadow-none transition-colors",
                        value ? "text-slate-950" : "text-slate-600 hover:text-slate-900",
                    )}
                >
                    <RadioGroupItem value="yes" id="create-user-yes" className="border-slate-300" />
                    <SharedFieldLabel htmlFor="create-user-yes" className="w-auto flex-none font-normal text-sm text-current">
                        Yes
                    </SharedFieldLabel>
                </SharedField>
                <SharedField
                    orientation="horizontal"
                    className={cn(
                        "min-h-0 cursor-pointer items-center gap-2 py-1 shadow-none transition-colors",
                        !value ? "text-slate-950" : "text-slate-600 hover:text-slate-900",
                    )}
                >
                    <RadioGroupItem value="no" id="create-user-no" className="border-slate-300" />
                    <SharedFieldLabel htmlFor="create-user-no" className="w-auto flex-none font-normal text-sm text-current">
                        No
                    </SharedFieldLabel>
                </SharedField>
            </RadioGroup>
        </FieldSet>
    )
}

function SupplierCategorySelector({
    categories,
    selectedCategoryIds,
    selectedCategories,
    onChange,
    error,
    className,
}: {
    categories: Array<{ id: number; name: string }>
    selectedCategoryIds: number[]
    selectedCategories: Array<{ id: number; name: string }>
    onChange: (nextValue: number[]) => void
    error?: string | null
    className?: string
}) {
    const [open, setOpen] = React.useState(false)

    const summaryLabel = selectedCategories.length === 0
        ? "Select supplier categories"
        : selectedCategories.length === 1
            ? selectedCategories[0].name
            : `${selectedCategories[0].name} +${selectedCategories.length - 1}`

    const toggleCategory = (categoryId: number) => {
        const nextValues = selectedCategoryIds.includes(categoryId)
            ? selectedCategoryIds.filter((entry) => entry !== categoryId)
            : [...selectedCategoryIds, categoryId]

        onChange(nextValues)
    }

    return (
        <FieldShell
            label="Supplier Categories"
            required
            error={error}
            className={className}
        >
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        className={cn(
                            popoverTriggerClass,
                            error && inputErrorClass,
                        )}
                    >
                        <span className={cn("truncate", selectedCategories.length > 0 ? "text-slate-950" : "text-slate-500")}>
                            {summaryLabel}
                        </span>
                        <ChevronsUpDown className="h-4 w-4 text-slate-400" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className={popoverContentClass} align="start">
                    <Command className="rounded-lg bg-white">
                        <CommandInput placeholder="Search supplier categories" />
                        <CommandList className="max-h-64">
                            <CommandEmpty>No supplier categories found.</CommandEmpty>
                            {categories.map((item) => {
                                const selected = selectedCategoryIds.includes(item.id)

                                return (
                                    <CommandItem
                                        key={item.id}
                                        value={item.name}
                                        onSelect={() => toggleCategory(item.id)}
                                        className={popoverItemClass}
                                    >
                                        <Check className={cn("h-4 w-4", selected ? "text-primary opacity-100" : "opacity-0")} />
                                        <span className="truncate text-sm text-slate-700">{item.name}</span>
                                    </CommandItem>
                                )
                            })}
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </FieldShell>
    )
}

export default function RegisterForm() {
    const router = useRouter()
    const authErrorId = React.useId()
    const [authError, setAuthError] = React.useState<string | null>(null)
    const [existingAccountSuggestion, setExistingAccountSuggestion] = React.useState<string | null>(null)
    const [successDialogOpen, setSuccessDialogOpen] = React.useState(false)
    const [successTitle, setSuccessTitle] = React.useState("Registration successful")
    const [successDescription, setSuccessDescription] = React.useState("Your account has been created successfully.")
    const [submitState, setSubmitState] = React.useState<"idle" | "posting" | "success" | "error">("idle")
    const [pendingAction, setPendingAction] = React.useState<"idle" | "next" | "submit">("idle")
    const [showPassword, setShowPassword] = React.useState(false)
    const [showConfirmPassword, setShowConfirmPassword] = React.useState(false)
    const [currentStepId, setCurrentStepId] = React.useState<StepId>("business")
    const [openDocumentNotes, setOpenDocumentNotes] = React.useState<Record<number, boolean>>({})

    const {
        form,
        errors,
        metadata,
        isLoadingMetadata,
        metadataError,
        isLoadingLocalities,
        isSubmitting,
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
        toggleType,
    } = useRegisterForm()

    const createUser = form.watch("createUser")
    const businessTypeValue = form.watch("BusinessType")
    const watchedCategoryIds = form.watch("category_ids")
    const supplierCategoryValues = React.useMemo(() => watchedCategoryIds ?? [], [watchedCategoryIds])
    const countryValue = form.watch("Country")
    const companyPhoneValue = form.watch("Phone")
    const locationValue = form.watch("Location")
    const genderValue = form.watch("user_Gender")
    const maritalStatusValue = form.watch("user_MaritalStatus")
    const occupationValue = form.watch("user_Occupation")
    const selectedCountry = React.useMemo(
        () => metadata.countries.find((country) => country.code === countryValue),
        [countryValue, metadata.countries],
    )
    const selectedDialCode = normalizeCountryDialCode(selectedCountry?.phoneCode)
    const nationalCompanyPhone = extractNationalPhoneNumber(companyPhoneValue, selectedDialCode)

    const handleCountryChange = React.useCallback((nextCountryCode: string) => {
        const currentCountry = metadata.countries.find((country) => country.code === countryValue)
        const currentDialCode = normalizeCountryDialCode(currentCountry?.phoneCode)
        const nationalNumber = extractNationalPhoneNumber(form.getValues("Phone"), currentDialCode)
        const nextCountry = metadata.countries.find((country) => country.code === nextCountryCode)
        const nextDialCode = normalizeCountryDialCode(nextCountry?.phoneCode)

        form.setValue("Country", nextCountryCode, { shouldDirty: true, shouldValidate: true })
        form.setValue("Phone", buildInternationalPhoneNumber(nextDialCode, nationalNumber), {
            shouldDirty: true,
            shouldValidate: nationalNumber.length > 0,
        })
    }, [countryValue, form, metadata.countries])

    const handleCompanyPhoneChange = React.useCallback((event: React.ChangeEvent<HTMLInputElement>) => {
        const nextNationalNumber = event.target.value.replace(/\D+/g, "").replace(/^0+/, "")
        form.setValue("Phone", buildInternationalPhoneNumber(selectedDialCode, nextNationalNumber), {
            shouldDirty: true,
            shouldTouch: true,
            shouldValidate: true,
        })
    }, [form, selectedDialCode])

    const isPosting = submitState === "posting"
    const isBusy = isSubmitting || isPosting || pendingAction !== "idle"
    const isAdvancing = pendingAction === "next"
    const isSubmittingAction = isSubmitting || isPosting || pendingAction === "submit"
    const formDescriptionIds = [authError ? authErrorId : null].filter(Boolean).join(" ")
    const selectedSupplierCategories = React.useMemo(
        () => metadata.supplierCategories.filter((item) => supplierCategoryValues.includes(item.id)),
        [metadata.supplierCategories, supplierCategoryValues],
    )

    const steps = React.useMemo<StepMeta[]>(() => ([
        {
            id: "business",
            title: "Business",
            description: "",
            fields: [
                "types",
                "Name",
                "TradingName",
                "BusinessType",
                "RegistrationNumber",
                "TaxPIN",
                "VATNumber",
                "Phone",
                ...(!createUser ? ["Email"] : []),
                ...(isSupplier ? ["category_ids"] : []),
                "Country",
                "Location",
                "PhysicalAddress",
                "Website",
            ],
        },
        {
            id: "account",
            title: "Account",
            description: "",
            fields: [
                "createUser",
                ...(createUser ? ["user_FirstName", "user_LastName", "user_Email", "user_Phone", "user_Gender", "user_Password", "user_Password_confirmation"] : []),
            ],
        },
        {
            id: "type-specific",
            title: "Type-Specific",
            description: "",
            fields: [
                "types",
                ...(isSupplier ? ["registration_documents", "registration_document_notes"] : []),
                ...(isTenant ? ["user_Remarks"] : []),
                ...(isCustomer ? ["user_DateOfBirth", "user_MaritalStatus", "user_Occupation", "user_Gender"] : []),
            ],
        },
    ]), [createUser, isCustomer, isSupplier, isTenant])

    React.useEffect(() => {
        if (!steps.some((step) => step.id === currentStepId)) {
            setCurrentStepId(steps[0]?.id ?? "business")
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
                const rawMessage = errorsMap[field].message as string
                const normalizedMessage = normalizeErrorMessage(rawMessage, getFieldLabel(field), false)
                messages.push(normalizedMessage ?? getFieldPrompt(field))
            }
        }

        if (currentStep?.id === "type-specific") {
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
        const approvalMessage = values.types.includes("SU")
            ? "Supplier approval may still continue after email verification."
            : ""

        setSubmitState("success")

        if (verificationRequired) {
            setSuccessTitle("Set your password and verify your email")
            setSuccessDescription(
                verificationEmail
                    ? approvalMessage
                        ? `An account setup link was sent to ${verificationEmail}.\n${approvalMessage}`
                        : `An account setup link was sent to ${verificationEmail}.`
                    : approvalMessage
                        ? `An account setup link was sent to your email address.\n${approvalMessage}`
                        : "An account setup link was sent to your email address.",
            )
        } else {
            setSuccessTitle("Registration successful")
            setSuccessDescription(
                approvalMessage
                    ? `Your account is ready.\n${approvalMessage}`
                    : "Your account is ready.",
            )
        }

        setSuccessDialogOpen(true)
    }, [extractRegisterMessage, pickVerificationEmail])

    const validateCurrentStep = React.useCallback(async () => {
        setAuthError(null)
        setExistingAccountSuggestion(null)

        const fields = currentStep?.fields ?? []
        const formValid = fields.length > 0 ? await form.trigger(fields as never) : true
        const documentsValid = currentStep?.id === "type-specific" ? validateSupplierDocuments() : true
        const valid = formValid && documentsValid

        if (!valid) {
            const stepErrors = getStepFieldErrors(fields)
            const fallbackMessage = "Complete the highlighted fields before continuing."
            const combinedMessage = stepErrors.messages.length > 0
                ? stepErrors.messages.join("\n")
                : fallbackMessage

            setAuthError(combinedMessage)
            if (stepErrors.firstField) form.setFocus(stepErrors.firstField as never)
            return false
        }

        const serverStep = currentStep ? SERVER_STEP_BY_FORM_STEP[currentStep.id] : undefined
        if (serverStep && currentStep) {
            const serverValidation = await validateRegistrationStep(serverStep, form.getValues(), fields)
            if (!serverValidation.valid) {
                if (serverValidation.existingAccount) {
                    setExistingAccountSuggestion(serverValidation.message || "An account already exists for these details.")
                    return false
                }
                const stepErrors = getStepFieldErrors(fields)
                const fallbackMessage = stepErrors.messages.length > 0
                    ? "Complete the highlighted fields before continuing."
                    : (serverValidation.message || "We couldn't validate this step right now. Try again.")
                const combinedMessage = stepErrors.messages.length > 0
                    ? stepErrors.messages.join("\n")
                    : fallbackMessage

                setAuthError(combinedMessage)
                if (stepErrors.messages.length === 0 && serverValidation.message) {
                    toast.error(serverValidation.message)
                }
                if (stepErrors.firstField) form.setFocus(stepErrors.firstField as never)
                return false
            }
        }

        return valid
    }, [currentStep, form, getStepFieldErrors, validateRegistrationStep, validateSupplierDocuments])

    const handleNextStep = async () => {
        if (isBusy || !currentStep) return
        setPendingAction("next")
        try {
            const valid = await validateCurrentStep()
            if (!valid) return

            const nextStep = steps[currentStepIndex + 1]
            if (nextStep) {
                setCurrentStepId(nextStep.id)
                window.scrollTo({ top: 0, behavior: "smooth" })
            }
        } catch {
            setAuthError(SYSTEM_ERROR_MESSAGE)
            toast.error(SYSTEM_ERROR_MESSAGE)
        } finally {
            setPendingAction("idle")
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

        setPendingAction("submit")
        try {
            const valid = await validateCurrentStep()
            if (!valid) return

            setSubmitState("posting")
            const values = form.getValues()
            const registerResponse = await registerThirdParty(values)
            if (registerResponse?.success) handleRegistrationSuccess(values, registerResponse)
        } catch (error) {
            setSubmitState("error")

            const allFields = steps.flatMap((step) => step.fields)
            const fieldErrors = getStepFieldErrors(allFields)

            if (fieldErrors.messages.length > 0) {
                const combinedMessage = fieldErrors.messages.join("\n")
                setAuthError(combinedMessage)
                toast.error(fieldErrors.messages.length === 1 ? fieldErrors.messages[0] : `Please review ${fieldErrors.messages.length} fields.`)

                if (fieldErrors.firstField) {
                    const stepWithField = steps.find((step) => step.fields.includes(fieldErrors.firstField as string))
                    if (stepWithField && stepWithField.id !== currentStepId) {
                        setCurrentStepId(stepWithField.id)
                        window.scrollTo({ top: 0, behavior: "smooth" })
                    }
                    form.setFocus(fieldErrors.firstField as never)
                }
            } else {
                const errorMessage = error instanceof Error && error.message ? error.message : SYSTEM_ERROR_MESSAGE
                setAuthError(errorMessage)
                toast.error(errorMessage)
            }
        } finally {
            setPendingAction("idle")
        }
    }

    const getSubmitButtonLabel = () => {
        if (isSubmittingAction) return "Submitting registration"
        if (submitState === "error") return "Try Again"
        return "Create Account"
    }

    if (isLoadingMetadata) {
        return (
            <main className="relative flex min-h-dvh items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-white to-slate-50 px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                <div className="relative mx-auto flex w-full max-w-3xl items-center justify-center">
                    <CenteredAuthLoading label="Loading registration" className="min-h-[16rem]" />
                </div>
            </main>
        )
    }

    if (metadataError) {
        return (
            <main className="relative flex min-h-dvh items-center bg-slate-50 px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                <div className="relative mx-auto flex w-full max-w-3xl items-center justify-center">
                    <section className="w-full max-w-[720px] rounded-[10px] border border-slate-300 bg-white px-5 py-6 shadow-[0_18px_36px_-30px_rgba(15,23,42,0.16)] sm:rounded-[12px] sm:px-8 sm:py-8 lg:px-10 lg:py-9">
                        <FeedbackAlert id={authErrorId} message={metadataError} />
                    </section>
                </div>
            </main>
        )
    }

    return (
        <>
            <Dialog open={successDialogOpen} onOpenChange={setSuccessDialogOpen}>
                <DialogContent className="max-w-[calc(100%-2rem)] rounded-2xl border border-slate-200 bg-white p-5 shadow-none sm:max-w-md sm:p-6">
                    <DialogHeader className="space-y-0 text-left">
                        <DialogTitle className="sr-only">{successTitle}</DialogTitle>
                        <DialogDescription className="sr-only">{successDescription}</DialogDescription>
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 sm:p-5">
                            <div className="flex items-start gap-3">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <CheckCircle2 className="h-5 w-5" />
                                </div>
                                <div className="min-w-0 space-y-1.5">
                                    <h2 className="text-base font-semibold text-slate-950">{successTitle}</h2>
                                    <div className="space-y-1 text-sm leading-6 text-slate-600">
                                        {successDescription.split("\n").filter(Boolean).map((line) => (
                                            <p key={line}>{line}</p>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </DialogHeader>
                    <DialogFooter className="mt-5 gap-3 sm:flex-col sm:justify-center">
                        <Button
                            type="button"
                            onClick={() => router.replace("/signin")}
                            className="h-11 w-full rounded-lg border border-slate-950 bg-slate-950 px-5 text-sm font-semibold text-white transition-[background-color,border-color] duration-150 hover:border-slate-800 hover:bg-slate-800"
                        >
                            <span className="inline-flex items-center gap-2.5">
                                <span>Continue to sign in</span>
                                <ArrowRight className="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                            </span>
                        </Button>
                        <Button type="button" className="h-11 w-full rounded-lg border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-900 transition-[border-color,background-color] duration-150 hover:border-slate-400 hover:bg-slate-50" onClick={() => setSuccessDialogOpen(false)}>
                            Close
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            <main className="relative flex min-h-dvh items-center bg-slate-50 px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
                <div className="relative mx-auto flex w-full max-w-5xl items-center justify-center">
                    <section className="relative w-full max-w-[980px] rounded-[10px] border border-slate-300 bg-white shadow-[0_18px_36px_-30px_rgba(15,23,42,0.16)] sm:rounded-[12px]">
                        {isPosting ? (
                            <div className="absolute inset-0 z-20 rounded-[10px] bg-white/85 backdrop-blur-sm sm:rounded-[12px]">
                                <CenteredAuthLoading label="Submitting your registration" className="min-h-0" />
                            </div>
                        ) : null}

                        <div className="mx-auto w-full max-w-4xl space-y-6 px-5 py-6 sm:space-y-7 sm:px-8 sm:py-8 lg:px-10 lg:py-9">
                            <header className="space-y-2 pb-1 text-center sm:space-y-3 sm:pb-2">
                                <div className="space-y-2 sm:space-y-3">
                                    <h1 className="text-2xl font-bold tracking-tight text-slate-950 sm:text-[2rem]">Create your account</h1>
                                    <p className="mx-auto max-w-md rounded-[6px] bg-slate-100 px-4 py-2.5 text-sm font-medium leading-6 text-slate-600">
                                        Complete the details below to register and continue.
                                    </p>
                                </div>
                            </header>

                            {existingAccountSuggestion ? (
                                <Alert className="border-blue-200 bg-blue-50 text-blue-950">
                                    <UserCog className="h-4 w-4 text-blue-700" />
                                    <AlertTitle>It looks like you already have an account</AlertTitle>
                                    <AlertDescription className="space-y-3">
                                        <p>{existingAccountSuggestion}</p>
                                        <Button asChild type="button" size="sm" className="bg-blue-700 text-white hover:bg-blue-800">
                                            <Link href="/signin">Sign in to add a profile</Link>
                                        </Button>
                                    </AlertDescription>
                                </Alert>
                            ) : null}

                            {authError ? <FeedbackAlert id={authErrorId} message={authError} onDismiss={() => setAuthError(null)} /> : null}

                            <div className="grid gap-5 sm:gap-6">
                                <form onSubmit={handleSubmitForm} className={cn("grid gap-6 sm:gap-7", isPosting && "pointer-events-none")} noValidate aria-busy={isPosting} aria-describedby={formDescriptionIds || undefined}>
                                    {currentStep?.id === "business" ? (
                                        <section className={sectionCardClass}>
                                            <div className={formSectionGridClass}>
                                                <div className="md:col-span-2 grid gap-4 sm:gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(260px,0.85fr)] lg:items-start">
                                                    <RoleSelector
                                                        selectedTypes={selectedTypes ?? []}
                                                        onToggle={toggleType}
                                                        error={errors.types?.message as string | undefined}
                                                    />

                                                    <CreateUserSelector
                                                        value={createUser}
                                                        onChange={(nextValue) => form.setValue("createUser", nextValue, { shouldDirty: true, shouldValidate: true })}
                                                    />
                                                </div>

                                                <div className="md:col-span-2">
                                                    <FieldLabel required>Legal Name</FieldLabel>
                                                    <Input required autoComplete="organization" {...form.register("Name")} placeholder="Daniel Logistics Limited" className={cn(inputStyle, errors.Name && inputErrorClass)} />
                                                    <FieldError message={errors.Name?.message as string | undefined} label="Legal name" />
                                                </div>

                                                <div className={fieldBlockClass}>
                                                    <FieldLabel>Trading Name</FieldLabel>
                                                    <Input {...form.register("TradingName")} placeholder="Daniel Logistics" className={cn(inputStyle, errors.TradingName && inputErrorClass)} />
                                                    <FieldError message={errors.TradingName?.message as string | undefined} label="Trading name" />
                                                </div>

                                                <CompactComboboxField
                                                    label="Business Type"
                                                    required
                                                    value={businessTypeValue || undefined}
                                                    onChange={(value) => form.setValue("BusinessType", value, { shouldDirty: true, shouldValidate: true })}
                                                    options={metadata.businessTypes.map((item) => ({ value: item.value, label: item.label }))}
                                                    placeholder="Select business type"
                                                    error={errors.BusinessType?.message as string | undefined}
                                                    className={fieldBlockClass}
                                                    searchPlaceholder="Search business type"
                                                    emptyMessage="No business types found."
                                                />

                                                {isSupplier ? (
                                                    <SupplierCategorySelector
                                                        categories={metadata.supplierCategories}
                                                        selectedCategoryIds={supplierCategoryValues}
                                                        selectedCategories={selectedSupplierCategories}
                                                        onChange={(nextValues) => form.setValue("category_ids", nextValues, { shouldDirty: true, shouldValidate: true })}
                                                        error={errors.category_ids?.message as string | undefined}
                                                        className={fieldBlockClass}
                                                    />
                                                ) : null}

                                                <div className={fieldBlockClass}>
                                                    <FieldLabel required>Registration Number</FieldLabel>
                                                    <Input required {...form.register("RegistrationNumber")} placeholder="REG-12345" className={cn(inputStyle, errors.RegistrationNumber && inputErrorClass)} />
                                                    <FieldError message={errors.RegistrationNumber?.message as string | undefined} label="Registration number" />
                                                </div>

                                                <div className={fieldBlockClass}>
                                                    <FieldLabel required>Tax PIN</FieldLabel>
                                                    <Input required {...form.register("TaxPIN")} placeholder="P123456789X" className={cn(inputStyle, errors.TaxPIN && inputErrorClass)} />
                                                    <FieldError message={errors.TaxPIN?.message as string | undefined} label="Tax PIN" />
                                                </div>

                                                <div className={fieldBlockClass}>
                                                    <FieldLabel>VAT Number</FieldLabel>
                                                    <Input {...form.register("VATNumber")} placeholder="P123456789X" className={cn(inputStyle, errors.VATNumber && inputErrorClass)} />
                                                    <FieldError message={errors.VATNumber?.message as string | undefined} label="VAT number" />
                                                </div>

                                                {!createUser ? (
                                                    <div className={fieldBlockClass}>
                                                        <FieldLabel>Business Email</FieldLabel>
                                                        <Input type="email" autoComplete="email" {...form.register("Email")} placeholder="procurement@company.com" className={cn(inputStyle, errors.Email && inputErrorClass)} />
                                                        <FieldError message={errors.Email?.message as string | undefined} label="Business email" />
                                                    </div>
                                                ) : null}

                                                <CompactComboboxField
                                                    label="Country"
                                                    required
                                                    value={countryValue || undefined}
                                                    onChange={handleCountryChange}
                                                    options={metadata.countries.map((item) => ({ value: item.code, label: item.name }))}
                                                    placeholder="Select country"
                                                    error={errors.Country?.message as string | undefined}
                                                    searchPlaceholder="Search country"
                                                    emptyMessage="No countries found."
                                                />

                                                <div className={fieldBlockClass}>
                                                    <FieldLabel required>Company Phone</FieldLabel>
                                                    <input type="hidden" {...form.register("Phone")} />
                                                    <div className={cn(
                                                        "flex h-11 overflow-hidden rounded-lg border border-slate-200 bg-white transition-[border-color,box-shadow] focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-100 focus-within:shadow-[0_0_0_3px_rgba(59,130,246,0.1)]",
                                                        errors.Phone && "border-red-300 bg-red-50 focus-within:border-red-500 focus-within:ring-red-100 focus-within:shadow-none",
                                                    )}>
                                                        <span className="inline-flex min-w-20 items-center justify-center border-r border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-700">
                                                            {selectedDialCode || "Prefix"}
                                                        </span>
                                                        <input
                                                            type="tel"
                                                            inputMode="numeric"
                                                            autoComplete="tel-national"
                                                            value={nationalCompanyPhone}
                                                            onChange={handleCompanyPhoneChange}
                                                            onBlur={() => void form.trigger("Phone")}
                                                            disabled={!selectedDialCode}
                                                            placeholder={selectedDialCode ? "712345678" : "Select country first"}
                                                            aria-invalid={Boolean(errors.Phone) || undefined}
                                                            className="min-w-0 flex-1 bg-transparent px-3.5 text-sm font-medium text-slate-950 outline-none placeholder:font-normal placeholder:text-slate-400 disabled:cursor-not-allowed disabled:bg-slate-100"
                                                        />
                                                    </div>
                                                    <p className="text-xs text-slate-500">Enter the number without the country prefix or leading zero.</p>
                                                    <FieldError message={errors.Phone?.message as string | undefined} label="Company phone" />
                                                </div>

                                                <CompactComboboxField
                                                    label="Location"
                                                    required
                                                    value={locationValue != null ? String(locationValue) : undefined}
                                                    onChange={(value) => form.setValue("Location", Number(value), { shouldDirty: true, shouldValidate: true })}
                                                    options={metadata.localities.map((item) => ({ value: String(item.id), label: item.name }))}
                                                    placeholder={isLoadingLocalities ? "Loading locations" : "Select location"}
                                                    disabled={isLoadingLocalities || metadata.localities.length === 0}
                                                    error={errors.Location?.message as string | undefined}
                                                    searchPlaceholder={isLoadingLocalities ? "Loading locations" : "Search location"}
                                                    emptyMessage="No locations found."
                                                />

                                                <div className={cn(fieldBlockClass, "md:col-span-2")}>
                                                    <FieldLabel>Physical Address</FieldLabel>
                                                    <Input {...form.register("PhysicalAddress")} placeholder="Building, street, city" className={cn(inputStyle, errors.PhysicalAddress && inputErrorClass)} />
                                                    <FieldError message={errors.PhysicalAddress?.message as string | undefined} label="Physical address" />
                                                </div>

                                                <div className={cn(fieldBlockClass, "md:col-span-2")}>
                                                    <FieldLabel>Website</FieldLabel>
                                                    <Input {...form.register("Website")} placeholder="https://example.com" className={cn(inputStyle, errors.Website && inputErrorClass)} />
                                                    <FieldError message={errors.Website?.message as string | undefined} label="Website" />
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

                                    {currentStep?.id === "account" ? (
                                        <section className={sectionCardClass}>
                                            <div className={formSectionGridClass}>
                                                {!createUser ? (
                                                    <div className="md:col-span-2 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                                                        <p className="text-sm leading-6 text-slate-600">
                                                            Login setup is off. Turn it on from Business if needed.
                                                        </p>
                                                    </div>
                                                ) : (
                                                    <>
                                                        <div className={fieldBlockClass}>
                                                            <FieldLabel required>First Name</FieldLabel>
                                                            <Input required autoComplete="given-name" {...form.register("user_FirstName")} placeholder="Jane" className={cn(inputStyle, errors.user_FirstName && inputErrorClass)} />
                                                            <FieldError message={errors.user_FirstName?.message as string | undefined} label="First name" />
                                                        </div>

                                                        <div className={fieldBlockClass}>
                                                            <FieldLabel required>Last Name</FieldLabel>
                                                            <Input required autoComplete="family-name" {...form.register("user_LastName")} placeholder="Doe" className={cn(inputStyle, errors.user_LastName && inputErrorClass)} />
                                                            <FieldError message={errors.user_LastName?.message as string | undefined} label="Last name" />
                                                        </div>

                                                        <div className={fieldBlockClass}>
                                                            <FieldLabel required>Email</FieldLabel>
                                                            <Input type="email" required autoComplete="email" {...form.register("user_Email")} placeholder="portal.user@example.com" className={cn(inputStyle, errors.user_Email && inputErrorClass)} />
                                                            <FieldError message={errors.user_Email?.message as string | undefined} label="Email" />
                                                        </div>

                                                        <div className={fieldBlockClass}>
                                                            <FieldLabel required>Phone Number</FieldLabel>
                                                            <Input type="tel" inputMode="tel" pattern="[+]?[0-9]{8,15}" required autoComplete="tel" {...form.register("user_Phone")} placeholder="+254722222222" className={cn(inputStyle, errors.user_Phone && inputErrorClass)} />
                                                            <FieldError message={errors.user_Phone?.message as string | undefined} label="Phone number" />
                                                        </div>

                                                        <CompactComboboxField
                                                            label="Gender"
                                                            required
                                                            value={genderValue || undefined}
                                                            onChange={(value) => form.setValue("user_Gender", value, { shouldDirty: true, shouldValidate: true })}
                                                            options={metadata.genders.map((item) => ({ value: item.value, label: item.label }))}
                                                            placeholder="Select gender"
                                                            error={errors.user_Gender?.message as string | undefined}
                                                            className="md:col-span-2"
                                                            searchPlaceholder="Search gender"
                                                            emptyMessage="No genders found."
                                                        />

                                                        <div className="relative grid content-start gap-2">
                                                            <FieldLabel required>Password</FieldLabel>
                                                            <div className="relative">
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
                                                            </div>
                                                            <FieldError message={errors.user_Password?.message as string | undefined} label="Password" />
                                                        </div>

                                                        <div className="relative grid content-start gap-2">
                                                            <FieldLabel required>Confirm Password</FieldLabel>
                                                            <div className="relative">
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
                                                            </div>
                                                            <FieldError message={errors.user_Password_confirmation?.message as string | undefined} label="Confirm password" />
                                                        </div>
                                                    </>
                                                )}
                                            </div>
                                        </section>
                                    ) : null}

                                    {currentStep?.id === "type-specific" ? (
                                        <section className={sectionCardClass}>
                                            <div className={formSectionGridClass}>
                                                {isSupplier ? (
                                                    <>
                                                        <div className="md:col-span-2 grid gap-4 sm:gap-5">
                                                            {metadata.supplierDocumentRequirements.length > 0 ? (
                                                                metadata.supplierDocumentRequirements.map((requirement) => {
                                                                    const selectedFile = documentFiles[requirement.id]
                                                                    const noteValue = documentNotes[requirement.id] ?? ""
                                                                    const hasNote = noteValue.trim().length > 0
                                                                    const noteOpen = openDocumentNotes[requirement.id] || hasNote
                                                                    const allowedExtensions = requirement.allowedExtensions
                                                                        .map((extension) => extension.trim())
                                                                        .filter(Boolean)
                                                                    const accept = allowedExtensions.length > 0
                                                                        ? allowedExtensions.map((extension) => (extension.startsWith(".") ? extension : `.${extension}`)).join(",")
                                                                        : undefined
                                                                    const acceptedExtensionsLabel = allowedExtensions.length > 0
                                                                        ? allowedExtensions.map((extension) => (extension.startsWith(".") ? extension : `.${extension}`).toUpperCase()).join(" · ")
                                                                        : null

                                                                    return (
                                                                        <div key={requirement.id} className="rounded-xl border border-slate-200 bg-white p-3.5 sm:p-4">
                                                                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                                                <h3 className="text-sm font-semibold tracking-[0.02em] text-slate-950">
                                                                                    {requirement.name}
                                                                                    {requirement.isRequired ? <span className="text-rose-600"> *</span> : null}
                                                                                </h3>
                                                                                {acceptedExtensionsLabel ? (
                                                                                    <span className="inline-flex w-fit rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-600">
                                                                                        {acceptedExtensionsLabel}
                                                                                    </span>
                                                                                ) : null}
                                                                            </div>

                                                                            <div className="mt-3 grid gap-3">
                                                                                <div
                                                                                    className={cn(
                                                                                        "flex flex-col gap-3 rounded-lg border px-3 py-3 sm:flex-row sm:items-center sm:justify-between",
                                                                                        documentErrors[requirement.id]
                                                                                            ? "border-red-300 bg-red-50"
                                                                                            : "border-slate-200 bg-slate-50",
                                                                                    )}
                                                                                >
                                                                                    <div className="min-w-0">
                                                                                        <span className="text-xs font-bold uppercase tracking-[0.1em] text-slate-600">Upload file</span>
                                                                                        <div className="mt-1 inline-flex max-w-full items-center gap-2 text-sm text-slate-900">
                                                                                            {selectedFile ? <CheckCircle2 className="h-4 w-4 shrink-0 text-emerald-600" /> : null}
                                                                                            <span className={cn("truncate", selectedFile ? "font-medium" : "text-slate-500")}>{selectedFile?.name ?? "No file selected"}</span>
                                                                                        </div>
                                                                                    </div>

                                                                                    <label className="inline-flex h-10 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-900 transition-colors hover:border-slate-400 hover:bg-slate-100">
                                                                                        <input
                                                                                            type="file"
                                                                                            accept={accept}
                                                                                            onChange={(event) => setRegistrationDocumentFile(requirement.id, event.target.files?.[0] ?? null)}
                                                                                            className="sr-only"
                                                                                        />
                                                                                        {selectedFile ? "Replace file" : "Choose file"}
                                                                                    </label>
                                                                                </div>

                                                                                <div className="grid gap-2">
                                                                                    <button
                                                                                        type="button"
                                                                                        onClick={() => setOpenDocumentNotes((current) => ({
                                                                                            ...current,
                                                                                            [requirement.id]: !noteOpen,
                                                                                        }))}
                                                                                        className="inline-flex w-fit items-center gap-2 rounded-md px-0 text-sm font-semibold text-slate-700 transition-colors hover:text-slate-950"
                                                                                    >
                                                                                        <span>{noteOpen ? "Hide note" : "Add note"}</span>
                                                                                    </button>

                                                                                    {noteOpen ? (
                                                                                        <FieldShell label="Note">
                                                                                            <Textarea
                                                                                                value={noteValue}
                                                                                                onChange={(event) => setRegistrationDocumentNote(requirement.id, event.target.value)}
                                                                                                placeholder="Add note"
                                                                                                className={cn(textAreaStyle, "min-h-24")}
                                                                                            />
                                                                                        </FieldShell>
                                                                                    ) : null}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    )
                                                                })
                                                            ) : (
                                                                <div className="rounded-xl border border-dashed border-slate-300 bg-white p-4 sm:p-5">
                                                                    <p className="text-sm leading-6 text-slate-600">
                                                                        No supplier document requirements were returned for the current registration metadata.
                                                                    </p>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </>
                                                ) : null}

                                                {isTenant ? (
                                                    <div className="md:col-span-2">
                                                        <FieldLabel required>Tenant Remarks</FieldLabel>
                                                        <Textarea required {...form.register("user_Remarks")} placeholder="Requires portal access for tenancy onboarding." className={cn(textAreaStyle, errors.user_Remarks && inputErrorClass)} />
                                                        <FieldError message={errors.user_Remarks?.message as string | undefined} label="Tenant remarks" />
                                                    </div>
                                                ) : null}

                                                {isCustomer ? (
                                                    <>
                                                        <div className={fieldBlockClass}>
                                                            <FieldLabel required>Date of Birth</FieldLabel>
                                                            <Input type="date" required {...form.register("user_DateOfBirth")} className={cn(inputStyle, errors.user_DateOfBirth && inputErrorClass)} />
                                                            <FieldError message={errors.user_DateOfBirth?.message as string | undefined} label="Date of birth" />
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

                                    <div className={cn(
                                        "grid w-full gap-3 pt-2 sm:pt-4",
                                        isFirstStep ? "grid-cols-1" : "sm:grid-cols-2",
                                    )}>
                                        {!isFirstStep && (
                                            <Button type="button" onClick={handlePreviousStep} disabled={isBusy} className={navigationSecondaryButtonClass}>
                                                <span className="inline-flex items-center gap-2">
                                                    <ChevronLeft className="h-4 w-4" />
                                                    <span>Back</span>
                                                </span>
                                            </Button>
                                        )}

                                        {isLastStep ? (
                                            <Button type="submit" disabled={isBusy} className={navigationPrimaryButtonClass}>
                                                {isBusy ? (
                                                    <span className="inline-flex w-full items-center justify-center gap-2.5">
                                                        <span className="loader-bars loader-bars--inline [&>span]:bg-white [&>span]:shadow-none" aria-hidden="true">
                                                            <span />
                                                            <span />
                                                            <span />
                                                        </span>
                                                        <span>{getSubmitButtonLabel()}</span>
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-2">
                                                        <span>{getSubmitButtonLabel()}</span>
                                                        <CircleCheck className="h-4 w-4 transition-transform duration-200 hover:scale-110" />
                                                    </span>
                                                )}
                                            </Button>
                                        ) : (
                                            <Button type="button" onClick={handleNextStep} disabled={isBusy} className={navigationPrimaryButtonClass}>
                                                {isAdvancing ? (
                                                    <span className="inline-flex w-full items-center justify-center gap-2.5">
                                                        <span className="loader-bars loader-bars--inline [&>span]:bg-white [&>span]:shadow-none" aria-hidden="true">
                                                            <span />
                                                            <span />
                                                            <span />
                                                        </span>
                                                        <span>Checking details</span>
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-2">
                                                        <span>Continue</span>
                                                        <ArrowRight className="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                                                    </span>
                                                )}
                                            </Button>
                                        )}
                                    </div>
                                </form>
                            </div>

                            <div className="flex justify-center pt-2 text-center sm:pt-4">
                                <p className="text-sm text-slate-600">
                                    Already have an Account?{" "}
                                    <Link href="/signin" className={cn("font-semibold text-primary", linkClass)}>
                                        Sign in
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </>
    )
}
