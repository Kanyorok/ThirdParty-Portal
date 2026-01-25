"use client"

import { useState, useEffect, useCallback } from "react"
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

const registerSchema = z.object({
    Name: z.string().min(2),
    TradingName: z.string().nullable().optional(),
    BusinessType: z.string().min(1),
    RegistrationNumber: z.string().min(2),
    TaxPIN: z.string().min(2),
    VATNumber: z.string().nullable().optional(),
    Country: z.string().min(1),
    Location: z.coerce.number().min(1),
    Email: z.string().email(),
    Phone: z.string().min(10),
    PhysicalAddress: z.string().nullable().optional(),
    Website: z.string().url().optional().or(z.literal("")).nullable(),
    types: z.array(z.string()).min(1),
    user_SupplierCategoryId: z.preprocess(v => v === "" ? null : v, z.coerce.number().nullable().optional()),
    user_Remarks: z.preprocess(v => v === "" ? null : v, z.string().nullable().optional()),
    user_DateOfBirth: z.string().nullable().optional(),
    user_MaritalStatus: z.string().nullable().optional(),
    user_Occupation: z.string().nullable().optional(),
    createUser: z.boolean(),
    user_FirstName: z.preprocess(emptyToUndefined, z.string().min(2).optional()),
    user_LastName: z.preprocess(emptyToUndefined, z.string().min(2).optional()),
    user_Email: z.preprocess(emptyToUndefined, z.string().email().optional()),
    user_Phone: z.preprocess(emptyToUndefined, z.string().min(10).optional()),
    user_Gender: z.preprocess(emptyToUndefined, z.string().min(1).optional()),
    user_Password: z.preprocess(emptyToUndefined, z.string().min(8).optional()),
    user_Password_confirmation: z.preprocess(emptyToUndefined, z.string().optional())
}).superRefine((data, ctx) => {
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

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema) as any,
        mode: "onBlur",
        defaultValues: {
            Name: "",
            TradingName: null,
            BusinessType: "",
            RegistrationNumber: "",
            TaxPIN: "",
            VATNumber: null,
            Country: "KE",
            Location: 0,
            Email: "",
            Phone: "",
            PhysicalAddress: null,
            Website: "",
            types: [],
            user_SupplierCategoryId: null,
            user_Remarks: null,
            user_DateOfBirth: null,
            user_MaritalStatus: null,
            user_Occupation: null,
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

    const onSubmitHandler = async (values: RegisterFormInputs) => {
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(values)
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

        return result
    }

    return {
        form,
        errors: form.formState.errors,
        metadata,
        isLoadingMetadata,
        isLoadingLocalities,
        metadataError,
        onSubmit: form.handleSubmit(onSubmitHandler),
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
        isCustomer: selectedTypes?.includes("CU")
    }
}
