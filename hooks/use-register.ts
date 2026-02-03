"use client"

import { useState, useEffect, useCallback, useRef } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

type LookupItem = {
    value: string
    description: string
}

const emptyToUndefined = (value: unknown) => {
    if (typeof value !== "string") return value
    const trimmed = value.trim()
    return trimmed.length ? trimmed : undefined
}

const optionalTextField = () => z.preprocess(emptyToUndefined, z.string().min(1)).optional()
const optionalEmailField = () => z.preprocess(emptyToUndefined, z.string().email()).optional()
const optionalUrlField = () => z.preprocess(emptyToUndefined, z.string().url()).optional()

const hasRole = (types: string[] | undefined, flag: string) => types?.includes(flag)

const VERIFY_EMAIL_LINK_REGEX = /https?:\/\/[^"'<>\s]+\/verify-email\?[^"'<>\s]+/i

const extractVerifyEmailUrl = (payload: Record<string, any> | null | undefined): string | null => {
    if (!payload) {
        return null
    }

    const candidates = [
        payload.verify_url,
        payload.verifyUrl,
        payload.verificationUrl,
        payload.data?.verify_url,
        payload.data?.verifyUrl,
        payload.data?.verificationUrl,
        payload.data?.data?.verify_url,
        payload.data?.data?.verifyUrl,
        payload.data?.data?.verificationUrl,
    ]

    for (const candidate of candidates) {
        if (typeof candidate === "string" && candidate.trim()) {
            return candidate
        }
    }

    const message = payload.message ?? payload.data?.message ?? payload.data?.data?.message
    if (typeof message === "string") {
        const match = message.match(VERIFY_EMAIL_LINK_REGEX)
        if (match && match[0]) {
            return match[0]
        }
    }

    return null
}

const registerSchema = z.object({
    Name: z.string().min(2),
    TradingName: optionalTextField(),
    BusinessType: z.string().min(1),
    RegistrationNumber: z.string().min(2),
    TaxPIN: z.string().min(2),
    VATNumber: optionalTextField(),
    Country: z.string().min(1),
    Location: z.coerce.number().min(1),
    Email: optionalEmailField(),
    Phone: z.string().min(10),
    PhysicalAddress: optionalTextField(),
    Website: optionalUrlField(),
    types: z.array(z.string()).min(1),
    user_SupplierCategoryId: z.preprocess(v => (v === "" ? null : v), z.coerce.number().nullable().optional()),
    user_Remarks: optionalTextField(),
    user_DateOfBirth: optionalTextField(),
    user_MaritalStatus: optionalTextField(),
    user_Occupation: optionalTextField(),
    createUser: z.boolean(),
    user_FirstName: z.preprocess(emptyToUndefined, z.string().min(2).optional()),
    user_LastName: z.preprocess(emptyToUndefined, z.string().min(2).optional()),
    user_Email: z.preprocess(emptyToUndefined, z.string().email().optional()),
    user_Phone: z.preprocess(emptyToUndefined, z.string().min(10).optional()),
    user_Gender: z.preprocess(emptyToUndefined, z.string().min(1).optional()),
    user_Password: z.preprocess(emptyToUndefined, z.string().min(8).optional()),
    user_Password_confirmation: z.preprocess(emptyToUndefined, z.string().optional())
}).superRefine((data, ctx) => {
    if (hasRole(data.types, "SU") && !data.user_SupplierCategoryId) {
        ctx.addIssue({
            code: z.ZodIssueCode.custom,
            path: ["user_SupplierCategoryId"],
            message: "Select a supplier category."
        })
    }

    if (hasRole(data.types, "TN") && !(data.user_Remarks ?? "").trim()) {
        ctx.addIssue({
            code: z.ZodIssueCode.custom,
            path: ["user_Remarks"],
            message: "Tell us why you need tenant access."
        })
    }

    if (hasRole(data.types, "CU")) {
        if (!data.user_DateOfBirth) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_DateOfBirth"], message: "Customer date of birth is required." })
        }
        if (!data.user_MaritalStatus) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_MaritalStatus"], message: "Marital status is required." })
        }
        if (!data.user_Occupation) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ["user_Occupation"], message: "Occupation is required." })
        }
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
    const resetVerifyEmailUrl = useCallback(() => {
        lastVerifyEmailUrlRef.current = null
        setVerifyEmailUrl(null)
    }, [])

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema) as any,
        mode: "onBlur",
        defaultValues: {
            Name: "",
            TradingName: "",
            BusinessType: "",
            RegistrationNumber: "",
            TaxPIN: "",
            VATNumber: "",
            Country: "KE",
            Location: 0,
            Email: "",
            Phone: "",
            PhysicalAddress: "",
            Website: "",
            types: [],
            user_SupplierCategoryId: null,
            user_Remarks: "",
            user_DateOfBirth: "",
            user_MaritalStatus: "",
            user_Occupation: "",
            createUser: true,
            user_FirstName: "",
            user_LastName: "",
            user_Email: "",
            user_Phone: "",
            user_Gender: "",
            user_Password: "",
            user_Password_confirmation: ""
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
            const pick = (key: string) =>
                data?.[key] ??
                data?.[key.toLowerCase?.() as any] ??
                data?.[(key[0]?.toLowerCase?.() + key.slice(1)) as any] ??
                []

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

    useEffect(() => {
        fetchInitialMetadata()
    }, [fetchInitialMetadata])

    useEffect(() => {
        if (selectedCountryCode) {
            fetchLocalities(selectedCountryCode)
            form.setValue("Location", 0)
        }
    }, [selectedCountryCode, fetchLocalities, form])

    const getLastVerifyEmailUrl = useCallback(() => lastVerifyEmailUrlRef.current, [])

    const registerThirdParty = useCallback(async (values: RegisterFormInputs) => {
        const { user_SupplierCategoryId, ...rest } = values
        const payload = {
            ...rest,
            ...(user_SupplierCategoryId != null ? { supplier_category_id: user_SupplierCategoryId } : {})
        }

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })

        const result = await res.json()

        if (res.ok && result?.success === false) {
            throw new Error(result?.message || "Registration failed.")
        }

        if (!res.ok && result.errors) {
            Object.keys(result.errors).forEach(k => {
                form.setError(k as any, { message: result.errors[k][0] })
            })
            throw new Error(result.message)
        }

        const verifyEmailLink = extractVerifyEmailUrl(result)
        lastVerifyEmailUrlRef.current = verifyEmailLink
        setVerifyEmailUrl(verifyEmailLink)

        const normalizedResult = {
            ...(typeof result === "object" && result !== null ? result : { message: String(result) }),
            verifyEmailUrl: verifyEmailLink,
        }

        return normalizedResult
    }, [form])

    return {
        form,
        errors: form.formState.errors,
        metadata,
        isLoadingMetadata,
        isLoadingLocalities,
        metadataError,
        onSubmit: form.handleSubmit(registerThirdParty),
        verifyEmailUrl,
        resetVerifyEmailUrl,
        getLastVerifyEmailUrl,
        isSubmitting: form.formState.isSubmitting,
        toggleType: (type: string) => {
            const current = form.getValues("types") || []
            const updated = current.includes(type)
                ? current.filter(t => t !== type)
                : [...current, type]
            form.setValue("types", updated, { shouldValidate: true })
        },
        selectedTypes,
        isSupplier: selectedTypes?.includes("SU"),
        isTenant: selectedTypes?.includes("TN"),
        requiresSupplierCategory: selectedTypes?.includes("SU") ?? false,
        isCustomer: selectedTypes?.includes("CU")
    }
}
