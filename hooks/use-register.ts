"use client"

import { useState, useEffect, useCallback, useRef } from "react"
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

const emptyToUndefined = (value: unknown) => {
    if (typeof value !== "string") return value
    const trimmed = value.trim()
    return trimmed.length ? trimmed : undefined
}

const optionalTextField = () => z.preprocess(emptyToUndefined, z.string().min(1)).optional()
const optionalUrlField = () => z.preprocess(emptyToUndefined, z.string().url()).optional()

const hasRole = (types: string[] | undefined, flag: string) => types?.includes(flag)

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
    Name: z.string().min(2, "Company name is required"),
    TradingName: optionalTextField(),
    BusinessType: z.string().min(1, "Business type is required"),
    RegistrationNumber: z.string().min(2, "Registration number is required"),
    TaxPIN: z.string().min(2, "Tax PIN is required"),
    VATNumber: optionalTextField(),
    Country: z.string().min(1, "Country is required"),
    Location: z.coerce.number().min(1, "Location is required"),
    Email: z.string().email("Valid email is required").min(1, "Email is required"),
    Phone: z.string().min(10, "Phone number is required"),
    PhysicalAddress: optionalTextField(),
    Website: optionalUrlField(),
    types: z.array(z.string()).min(1, "Select at least one business role"),
    supplier_category_id: z.preprocess(v => (v === "" ? null : v), z.coerce.number().nullable().optional()),
    user_Remarks: optionalTextField(),
    user_DateOfBirth: optionalTextField(),
    user_MaritalStatus: optionalTextField(),
    user_Occupation: optionalTextField(),
    createUser: z.boolean(),
    user_FirstName: z.string().min(2, "First name is required").optional().or(z.literal("")),
    user_LastName: z.string().min(2, "Last name is required").optional().or(z.literal("")),
    user_Email: z.string().email("Valid email is required").optional().or(z.literal("")),
    user_Phone: z.string().min(10, "Valid phone is required").optional().or(z.literal("")),
    user_Gender: z.string().min(1, "Gender is required").optional().or(z.literal("")),
    user_Password: z.string().min(8, "Password must be at least 8 characters").optional().or(z.literal("")),
    user_Password_confirmation: z.string().optional().or(z.literal(""))
}).superRefine((data, ctx) => {
    if (hasRole(data.types, "SU") && !data.supplier_category_id) {
        ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["supplier_category_id"], message: "Select a supplier category." })
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
            types: [], supplier_category_id: null, user_Remarks: "",
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
        try {
            const baseUrl = process.env.NEXT_PUBLIC_API_URL
            const [countriesRes, categoriesRes, lookupsRes] = await Promise.all([
                fetch(`${baseUrl}/api/v1/portal/auth/metadata/countries`),
                fetch(`${baseUrl}/api/v1/portal/auth/metadata/supplier-categories`),
                fetch(`${baseUrl}/api/v1/portal/auth/lookups/bulk?codes=Gender,BusinessType,MaritalStatus,Occupation`)
            ])
            const countries = await countriesRes.json()
            const categories = await categoriesRes.json()
            const lookups = await lookupsRes.json()
            const data = lookups?.data ?? lookups?.Data ?? {}
            const pick = (key: string) => data?.[key] ?? data?.[key.toLowerCase()] ?? data?.[(key[0].toLowerCase() + key.slice(1))] ?? []

            setMetadata({
                countries: countries.data || [],
                supplierCategories: categories.data || [],
                localities: [],
                businessTypes: pick("BusinessType"),
                genders: pick("Gender"),
                maritalStatuses: pick("MaritalStatus"),
                occupations: pick("Occupation")
            })
        } catch {
            setMetadataError("Initialization failed")
        } finally {
            setIsLoadingMetadata(false)
        }
    }, [])

    const fetchLocalities = useCallback(async (countryCode: string) => {
        if (!countryCode) return
        const country = metadata.countries.find(c => c.code === countryCode)
        if (!country?.id) return
        setIsLoadingLocalities(true)
        try {
            const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/metadata/localities/${country.id}`)
            const result = await res.json()
            setMetadata(prev => ({ ...prev, localities: result.data || [] }))
        } finally {
            setIsLoadingLocalities(false)
        }
    }, [metadata.countries])

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
            if (result?.errors) {
                Object.entries(result.errors).forEach(([key, value]) => {
                    form.setError(key as any, { message: Array.isArray(value) ? value[0] : value as string })
                })
            }
            throw new Error(result?.message ?? "Registration failed.")
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
        toggleType: (type: string) => {
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