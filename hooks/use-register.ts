"use client"

import { useState, useEffect, useCallback, useRef } from "react"
import { apiFetch } from "../lib/api-base"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

type LookupItem = {
    id: string
    name: string
    label: string
    value: string
    description: string
}

const ROLE_VALUES = ["SU", "TN", "CU"] as const
type RoleValue = (typeof ROLE_VALUES)[number]
const PHONE_REGEX = /^\+?[0-9]{8,15}$/
const COMPANY_LIKE_BUSINESS_TYPES = new Set(["company", "partnership", "limited company", "llp"])
const GENERIC_REGISTRATION_ERROR = "We couldn't complete registration. Please correct the highlighted fields and try again."
const SENSITIVE_ERROR_PATTERN = /(exception|stack|trace|sql|syntax|internal server|undefined|vendor|route|line\s+\d+)/i

const SERVER_FIELD_FALLBACK_MESSAGES: Record<string, string> = {
    Name: "Please enter a valid legal company name.",
    TradingName: "Please enter a valid trading name.",
    BusinessType: "Please select a valid business type.",
    RegistrationNumber: "Please enter a valid registration number.",
    TaxPIN: "Please enter a valid tax PIN.",
    VATNumber: "Please enter a valid VAT number.",
    legalForm: "Please select a valid business type.",
    contactPersonName: "Please enter the contact person's name.",
    contactPersonEmail: "Please enter a valid contact person email address.",
    contactPersonPhone: "Please enter a valid contact person phone number.",
    Country: "Please select a valid country.",
    Location: "Please select a valid location.",
    Email: "Please enter a valid business email address.",
    Phone: "Please enter a valid business phone number.",
    PhysicalAddress: "Please enter a valid physical address.",
    Website: "Please enter a valid website URL.",
    types: "Please select at least one business role.",
    supplier_category_id: "Please select a supplier category.",
    user_Remarks: "Please provide tenant remarks.",
    user_DateOfBirth: "Please provide a valid date of birth.",
    user_MaritalStatus: "Please select a valid marital status.",
    user_Occupation: "Please select a valid occupation.",
    user_FirstName: "Please enter a valid first name.",
    user_LastName: "Please enter a valid last name.",
    user_Email: "Please enter a valid admin email address.",
    user_Phone: "Please enter a valid admin phone number.",
    user_Gender: "Please select a valid gender.",
    user_Password: "Please enter a valid password.",
    user_Password_confirmation: "Please confirm your password.",
}

const getSafeServerFieldMessage = (field: string, candidate: unknown): string => {
    const fallback = SERVER_FIELD_FALLBACK_MESSAGES[field] ?? "Please provide a valid value."
    if (typeof candidate !== "string") return fallback

    const normalized = candidate.replace(/\s+/g, " ").trim()
    if (!normalized || normalized.length > 140 || SENSITIVE_ERROR_PATTERN.test(normalized)) {
        return fallback
    }

    return normalized
}

const emptyToUndefined = (value: unknown) => {
    if (typeof value !== "string") return value
    const trimmed = value.trim()
    return trimmed.length ? trimmed : undefined
}

const optionalTextField = (label: string, maxLength: number) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .max(maxLength, `${label} must be ${maxLength} characters or fewer`)
    ).optional()

const optionalUrlField = (label: string, maxLength: number) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .max(maxLength, `${label} must be ${maxLength} characters or fewer`)
            .url(`Please enter a valid ${label.toLowerCase()}`)
    ).optional()

const optionalNameField = (label: string) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .min(2, `${label} must be at least 2 characters`)
            .max(50, `${label} must be 50 characters or fewer`)
            .regex(/^[a-zA-Z\s'-]+$/, `${label} can only contain letters, spaces, apostrophes, and hyphens`)
    ).optional()

const optionalEmailField = (label: string) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .email(`Please enter a valid ${label.toLowerCase()}`)
            .max(254, `${label} must be 254 characters or fewer`)
    ).optional()

const optionalPasswordField = (label: string) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .min(8, `${label} must be at least 8 characters`)
            .max(128, `${label} must be 128 characters or fewer`)
    ).optional()

const optionalLookupField = (label: string, maxLength: number) =>
    z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .max(maxLength, `${label} must be ${maxLength} characters or fewer`)
    ).optional()

const phoneField = (requiredMessage: string) =>
    z.string()
        .trim()
        .min(1, requiredMessage)
        .regex(PHONE_REGEX, "Phone number must be 8 to 15 digits and may start with +")

const optionalPhoneField = (requiredMessage: string) =>
    z.preprocess(emptyToUndefined, phoneField(requiredMessage)).optional()

const hasRole = (types: RoleValue[] | undefined, flag: RoleValue) => types?.includes(flag)
const isCompanyLikeBusinessType = (value: string | undefined | null) => COMPANY_LIKE_BUSINESS_TYPES.has(String(value || "").trim().toLowerCase())

const VERIFY_EMAIL_LINK_REGEX = /https?:\/\/[^"'<>\s]+\/verify-email\?[^"'<>\s]+/i

const extractVerifyEmailUrl = (payload: Record<string, any> | null | undefined): string | null => {
    if (!payload) return null
    const candidates = [
        payload.verify_url, payload.verifyUrl, payload.verificationUrl,
        payload.data?.verify_url, payload.data?.verifyUrl, payload.data?.verificationUrl,
        payload.data?.data?.verify_url, payload.data?.data?.verifyUrl, payload.data?.data?.verificationUrl,
    ]
    for (const candidate of candidates) {
        if (typeof candidate === "string" && candidate.trim()) return candidate
    }
    const message = payload.message ?? payload.data?.message ?? payload.data?.data?.message
    if (typeof message === "string") {
        const match = message.match(VERIFY_EMAIL_LINK_REGEX)
        if (match && match[0]) return match[0]
    }
    return null
}

export type RegisterThirdPartyResult = {
    success: boolean
    payload: Record<string, any>
    verifyEmailUrl: string | null
}

const registerSchema = z.object({
    Name: z
        .string()
        .trim()
        .min(2, "Company name is required")
        .max(100, "Company name must be 100 characters or fewer"),
    TradingName: optionalTextField("Trading name", 100),
    BusinessType: z.string().trim().min(1, "Business type is required"),
    RegistrationNumber: z
        .string()
        .trim()
        .min(2, "Registration number is required")
        .max(50, "Registration number must be 50 characters or fewer"),
    TaxPIN: z
        .string()
        .trim()
        .min(2, "Tax PIN is required")
        .max(50, "Tax PIN must be 50 characters or fewer"),
    VATNumber: z.preprocess(
        emptyToUndefined,
        z
            .string()
            .trim()
            .max(50, "VAT number must be 50 characters or fewer")
    ).optional(),
    Country: z
        .string()
        .trim()
        .min(2, "Country is required")
        .max(3, "Please select a valid country code"),
    Location: z.coerce.number().int("Please select a valid location").min(1, "Location is required"),
    Email: z
        .string()
        .trim()
        .email("Please enter a valid business email address")
        .min(1, "Email is required")
        .max(254, "Email must be 254 characters or fewer"),
    Phone: phoneField("Phone number is required"),
    PhysicalAddress: optionalTextField("Physical address", 200),
    Website: optionalUrlField("Website URL", 255),
    types: z.array(z.enum(ROLE_VALUES)).min(1, "Select at least one business role"),
    supplier_category_id: z.preprocess(v => (v === "" ? null : v), z.coerce.number().nullable().optional()),
    contactPersonName: optionalNameField("Contact person name"),
    contactPersonEmail: optionalEmailField("Contact person email"),
    contactPersonPhone: optionalPhoneField("Contact person phone is required"),
    user_Remarks: optionalTextField("Remarks", 500),
    user_DateOfBirth: optionalTextField("Date of birth", 25),
    user_MaritalStatus: optionalLookupField("Marital status", 100),
    user_Occupation: optionalLookupField("Occupation", 100),
    createUser: z.boolean(),
    user_FirstName: optionalNameField("First name"),
    user_LastName: optionalNameField("Last name"),
    user_Email: optionalEmailField("Admin email"),
    user_Phone: optionalPhoneField("Admin phone is required"),
    user_Gender: optionalLookupField("Gender", 50),
    user_Password: optionalPasswordField("Password"),
    user_Password_confirmation: optionalPasswordField("Confirm password")
}).superRefine((data, ctx) => {
    const supplierFlow = hasRole(data.types, "SU")

    if (supplierFlow && !data.supplier_category_id) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["supplier_category_id"], message: "Select a supplier category." })
    }
    if (supplierFlow && !data.VATNumber) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["VATNumber"], message: "VAT number is required for suppliers." })
    }
    if (supplierFlow && !data.BusinessType) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["BusinessType"], message: "Business type is required for suppliers." })
    }
    if (supplierFlow && isCompanyLikeBusinessType(data.BusinessType)) {
        if (!data.contactPersonName) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["contactPersonName"], message: "Contact person name is required." })
        if (!data.contactPersonEmail) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["contactPersonEmail"], message: "Contact person email is required." })
        if (!data.contactPersonPhone) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["contactPersonPhone"], message: "Contact person phone is required." })
    }
    if (hasRole(data.types, "TN") && !(data.user_Remarks ?? "").trim()) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Remarks"], message: "Remarks are required for tenant access." })
    }
    if (hasRole(data.types, "CU")) {
        if (!data.user_DateOfBirth) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_DateOfBirth"], message: "Customer date of birth is required." })
        if (!data.user_MaritalStatus) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_MaritalStatus"], message: "Marital status is required." })
        if (!data.user_Occupation) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Occupation"], message: "Occupation is required." })
    }
    if (!data.createUser) return
    if (!data.user_FirstName) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_FirstName"], message: "First name is required." })
    if (!data.user_LastName) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_LastName"], message: "Last name is required." })
    if (!data.user_Email) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Email"], message: "Admin email is required." })
    if (!data.user_Phone) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Phone"], message: "Admin phone is required." })
    if (!data.user_Gender) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Gender"], message: "Gender is required." })
    if (!data.user_Password) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Password"], message: "Password is required." })
    if (!data.user_Password_confirmation) ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Password_confirmation"], message: "Confirm your password." })
    if (data.user_Password && data.user_Password_confirmation && data.user_Password !== data.user_Password_confirmation) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Password_confirmation"], message: "Passwords do not match." })
    }
})

export type RegisterFormInputs = z.infer<typeof registerSchema>
export type RegisterRole = RoleValue

export const useRegisterForm = () => {
    const [metadata, setMetadata] = useState({
        countries: [] as any[],
        supplierCategories: [] as any[],
        localities: [] as any[],
        businessTypes: [] as LookupItem[],
        genders: [] as LookupItem[],
        maritalStatuses: [] as LookupItem[],
        occupations: [] as LookupItem[]
    })

    const [isLoadingMetadata, setIsLoadingMetadata] = useState(true)
    const [isLoadingLocalities, setIsLoadingLocalities] = useState(false)
    const [metadataError, setMetadataError] = useState<string | null>(null)
    const [verifyEmailUrl, setVerifyEmailUrl] = useState<string | null>(null)
    const lastVerifyEmailUrlRef = useRef<string | null>(null)
    const lastRegisterResponseRef = useRef<RegisterThirdPartyResult | null>(null)

    const resetVerifyEmailUrl = useCallback(() => {
        lastVerifyEmailUrlRef.current = null
        setVerifyEmailUrl(null)
    }, [])

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema) as any,
        mode: "onBlur",
        defaultValues: {
            Name: "", TradingName: "", BusinessType: "", RegistrationNumber: "",
            TaxPIN: "", VATNumber: "", Country: "KE", Location: undefined,
            Email: "", Phone: "", PhysicalAddress: "", Website: "",
            types: [], supplier_category_id: null, contactPersonName: "", contactPersonEmail: "", contactPersonPhone: "", user_Remarks: "",
            user_DateOfBirth: "", user_MaritalStatus: "", user_Occupation: "",
            createUser: true, user_FirstName: "", user_LastName: "",
            user_Email: "", user_Phone: "", user_Gender: "",
            user_Password: "", user_Password_confirmation: ""
        }
    })

    const selectedCountryCode = form.watch("Country")
    const selectedTypes = form.watch("types")

    const fetchInitialMetadata = useCallback(async () => {
        setIsLoadingMetadata(true)
        setMetadataError(null)
        try {
            const [countriesResult, categoriesResult, lookupsResult] = await Promise.allSettled([
                apiFetch<{ data?: any[] }>(`/api/countries`, { allowError: true }),
                apiFetch<{ data?: any[] }>(`/portal/metadata/supplier-categories`, { allowError: true }),
                apiFetch<Record<string, any>>(`/api/v1/portal/auth/lookups/bulk?codes=Gender,BusinessType,MaritalStatus,Occupation`, { allowError: true })
            ])

            const countries = countriesResult.status === "fulfilled" ? countriesResult.value : { data: [] }
            const categories = categoriesResult.status === "fulfilled" ? categoriesResult.value : { data: [] }
            const lookups = lookupsResult.status === "fulfilled" ? lookupsResult.value : {}

            const data = lookups?.data ?? lookups?.Data ?? {}
            const pick = (key: string) => data?.[key] ?? data?.[key.toLowerCase()] ?? data?.[(key[0].toLowerCase() + key.slice(1))] ?? []

            const countryRows = Array.isArray(countries?.data) ? countries.data : []
            const categoryRows = Array.isArray(categories?.data) ? categories.data : []

            if (countryRows.length === 0) {
                setMetadataError("We couldn't load registration metadata right now. Please refresh and try again.")
            }

            setMetadata({
                countries: countryRows,
                supplierCategories: categoryRows,
                localities: [],
                businessTypes: pick("BusinessType"),
                genders: pick("Gender"),
                maritalStatuses: pick("MaritalStatus"),
                occupations: pick("Occupation")
            })
        } catch {
            setMetadata({
                countries: [],
                supplierCategories: [],
                localities: [],
                businessTypes: [],
                genders: [],
                maritalStatuses: [],
                occupations: []
            })
            setMetadataError("We couldn't load registration metadata right now. Please refresh and try again.")
        } finally {
            setIsLoadingMetadata(false)
        }
    }, [])

    const fetchLocalities = useCallback(async (countryCode: string) => {
        if (!countryCode) return
        setIsLoadingLocalities(true)
        try {
            const result = await apiFetch<{ data?: any[] }>(`/api/countries/${encodeURIComponent(countryCode)}/localities`, { allowError: true })
            setMetadata(prev => ({ ...prev, localities: result?.data || [] }))
        } catch {
            setMetadata(prev => ({ ...prev, localities: [] }))
        } finally {
            setIsLoadingLocalities(false)
        }
    }, [])

    useEffect(() => { fetchInitialMetadata() }, [fetchInitialMetadata])

    useEffect(() => {
        if (selectedCountryCode) {
            fetchLocalities(selectedCountryCode)
            form.setValue("Location", undefined as any)
        }
    }, [selectedCountryCode, fetchLocalities, form])

    const registerThirdParty = useCallback(async (values: RegisterFormInputs): Promise<RegisterThirdPartyResult> => {
        lastRegisterResponseRef.current = null
        const isTenant = values.types.includes("TN")
        const isSupplier = values.types.includes("SU")
        const payload: Record<string, any> = { ...values }

        if (isTenant) {
            payload.tenant_Remarks = values.user_Remarks
        } else {
            delete payload.user_Remarks
        }
        if (!isSupplier) {
            delete payload.supplier_category_id
            delete payload.contactPersonName
            delete payload.contactPersonEmail
            delete payload.contactPersonPhone
        } else {
            payload.legalForm = values.BusinessType
        }

        if (!isSupplier || !isCompanyLikeBusinessType(values.BusinessType)) {
            delete payload.contactPersonName
            delete payload.contactPersonEmail
            delete payload.contactPersonPhone
        }

        Object.keys(payload).forEach(key => {
            if (payload[key] === "" || payload[key] === undefined || payload[key] === null) {
                if (key !== "supplier_category_id" && key !== "Location") delete payload[key]
            }
        })

        const res = await fetch("/api/register", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            body: JSON.stringify(payload)
        })

        const result = await res.json().catch(() => null)
        if (!res.ok) {
            let hasFieldErrors = false

            if (result?.errors && typeof result.errors === "object" && !Array.isArray(result.errors)) {
                Object.entries(result.errors).forEach(([key, value]) => {
                    const first = Array.isArray(value) ? value[0] : value
                    const fieldKey = key === "legalForm" ? "BusinessType" : key
                    const safeMessage = getSafeServerFieldMessage(fieldKey, first)
                    form.setError(fieldKey as any, { message: safeMessage })
                    hasFieldErrors = true
                })
            }

            throw new Error(hasFieldErrors ? GENERIC_REGISTRATION_ERROR : "We couldn't submit your registration right now. Please try again.")
        }

        const verifyEmailLink = extractVerifyEmailUrl(result)
        lastVerifyEmailUrlRef.current = verifyEmailLink
        setVerifyEmailUrl(verifyEmailLink)

        const response: RegisterThirdPartyResult = {
            success: true,
            payload: typeof result === "object" && result !== null ? result : { message: String(result) },
            verifyEmailUrl: verifyEmailLink
        }
        lastRegisterResponseRef.current = response
        return response
    }, [form])

    return {
        form,
        errors: form.formState.errors,
        metadata,
        isLoadingMetadata,
        isLoadingLocalities,
        metadataError,
        onSubmit: (e?: React.BaseSyntheticEvent) => form.handleSubmit(registerThirdParty)(e),
        registerThirdParty,
        verifyEmailUrl,
        resetVerifyEmailUrl,
        getLastVerifyEmailUrl: useCallback(() => lastVerifyEmailUrlRef.current, []),
        getLastRegisterResponse: useCallback(() => lastRegisterResponseRef.current, []),
        isSubmitting: form.formState.isSubmitting,
        toggleType: (type: RoleValue) => {
            const current = form.getValues("types") || []
            const updated = current.includes(type) ? current.filter(t => t !== type) : [...current, type]
            form.setValue("types", updated, { shouldValidate: true })
        },
        selectedTypes,
        isSupplier: selectedTypes?.includes("SU"),
        isTenant: selectedTypes?.includes("TN"),
        requiresSupplierCategory: selectedTypes?.includes("SU") ?? false,
        isCustomer: selectedTypes?.includes("CU")
    }
}
