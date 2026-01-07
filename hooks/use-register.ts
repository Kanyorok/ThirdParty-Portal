import { useState, useEffect, useCallback } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

const registerSchema = z.object({
    Name: z.string().min(2, "Company name required"),
    TradingName: z.string().optional(),
    BusinessType: z.string().min(1, "Required"),
    RegistrationNumber: z.string().min(2, "Required"),
    TaxPIN: z.string().min(2, "Required"),
    VATNumber: z.string().optional(),
    Country: z.string().min(1, "Required"),
    Location: z.coerce.number().min(1, "Required"),
    Email: z.string().email("Invalid email"),
    Phone: z.string().min(10, "Invalid phone"),
    PhysicalAddress: z.string().optional(),
    Website: z.string().url().optional().or(z.literal("")),
    types: z.array(z.string()).min(1, "Selection required"),
    supplier_category_id: z.coerce.number().optional(),
    tenant_Remarks: z.string().optional(),
    customer_PreferredPaymentMethod: z.string().optional(),
    createUser: z.boolean(),
    user_FirstName: z.string().min(2, "Required"),
    user_LastName: z.string().min(2, "Required"),
    user_Email: z.string().email("Invalid email"),
    user_Phone: z.string().min(10, "Required"),
    user_Gender: z.string().min(1, "Required"),
    user_Password: z.string().min(8, "Min 8 chars"),
    user_Password_confirmation: z.string()
}).refine((data) => data.user_Password === data.user_Password_confirmation, {
    message: "Passwords mismatch",
    path: ["user_Password_confirmation"],
}).refine((data) => {
    if (data.types.includes("SU")) {
        return !!data.supplier_category_id && data.supplier_category_id > 0
    }
    return true
}, {
    message: "Supplier category is required",
    path: ["supplier_category_id"],
}).refine((data) => {
    if (data.types.includes("TN")) {
        return !!data.tenant_Remarks && data.tenant_Remarks.trim().length > 0
    }
    return true
}, {
    message: "Tenant remarks are required",
    path: ["tenant_Remarks"],
})

export type RegisterFormInputs = z.infer<typeof registerSchema>

interface LookupItem {
    value: string
    description: string
}

interface Country {
    id: number
    code: string
    name: string
}

interface Locality {
    id: number
    name: string
}

interface SupplierCategory {
    id: number
    name: string
}

interface LookupsData {
    businessType: LookupItem[]
    gender: LookupItem[]
}

interface MetadataState {
    countries: Country[]
    businessTypes: LookupItem[]
    supplierCategories: SupplierCategory[]
    localities: Locality[]
    genders: LookupItem[]
}

export const useRegisterForm = () => {
    const [metadata, setMetadata] = useState<MetadataState>({
        countries: [],
        businessTypes: [],
        supplierCategories: [],
        localities: [],
        genders: [],
    })
    const [isLoadingMetadata, setIsLoadingMetadata] = useState(true)
    const [metadataError, setMetadataError] = useState<string | null>(null)

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema),
        mode: "onBlur",
        defaultValues: {
            Name: "",
            TradingName: "",
            BusinessType: "Sole",
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
            supplier_category_id: undefined,
            tenant_Remarks: "",
            customer_PreferredPaymentMethod: "",
            createUser: true,
            user_FirstName: "",
            user_LastName: "",
            user_Email: "",
            user_Phone: "",
            user_Gender: "M",
            user_Password: "",
            user_Password_confirmation: ""
        }
    })

    const selectedCountryCode = form.watch("Country")
    const selectedTypes = form.watch("types")

    const fetchInitialMetadata = useCallback(async () => {
        setIsLoadingMetadata(true)
        setMetadataError(null)

        try {
            const [countriesRes, categoriesRes, lookupsRes] = await Promise.all([
                fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/metadata/countries`),
                fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/metadata/supplier-categories`),
                fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/lookups/bulk?codes=Gender,BusinessType,MaritalStatus,ThirdPartyType`)
            ])

            if (!countriesRes.ok || !categoriesRes.ok || !lookupsRes.ok) {
                throw new Error("Failed to load registration data")
            }

            const [countries, categories, lookups] = await Promise.all([
                countriesRes.json(),
                categoriesRes.json(),
                lookupsRes.json()
            ])

            const lookupsData: LookupsData = lookups.data || {}

            setMetadata({
                countries: countries.data || countries,
                supplierCategories: categories.data || categories,
                businessTypes: lookupsData.businessType || [],
                genders: lookupsData.gender || [],
                localities: []
            })
        } catch (error) {
            console.error("Failed to fetch metadata:", error)
            setMetadataError("Unable to load registration data. Please refresh the page.")
        } finally {
            setIsLoadingMetadata(false)
        }
    }, [])

    const fetchLocalities = useCallback(async (countryCode: string) => {
        if (!countryCode) return

        const country = metadata.countries.find(c => c.code === countryCode)
        if (!country?.id) return

        try {
            const res = await fetch(
                `${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/metadata/localities/${country.id}`
            )

            if (!res.ok) throw new Error("Failed to load cities")

            const result = await res.json()
            setMetadata(prev => ({
                ...prev,
                localities: result.data || result || []
            }))
        } catch (error) {
            console.error("Failed to fetch localities:", error)
        }
    }, [metadata.countries])

    useEffect(() => {
        fetchInitialMetadata()
    }, [fetchInitialMetadata])

    useEffect(() => {
        if (selectedCountryCode && metadata.countries.length > 0) {
            fetchLocalities(selectedCountryCode)
        }
    }, [selectedCountryCode, metadata.countries, fetchLocalities])

    useEffect(() => {
        if (!selectedTypes?.includes("SU")) {
            form.setValue("supplier_category_id", undefined)
        }
        if (!selectedTypes?.includes("TN")) {
            form.setValue("tenant_Remarks", "")
        }
        if (!selectedTypes?.includes("CU")) {
            form.setValue("customer_PreferredPaymentMethod", "")
        }
    }, [selectedTypes, form])

    const onSubmit = async (data: RegisterFormInputs) => {
        if (!data.types || data.types.length === 0) {
            form.setError("types", {
                type: "manual",
                message: "Please select at least one business role"
            })
            return
        }

        try {
            const payload = {
                ...data,
                types: data.types.filter(Boolean)
            }

            console.log("Registration payload:", payload)

            const response = await fetch(
                `${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(payload)
                }
            )

            const result = await response.json()

            if (!response.ok) {
                if (result.errors) {
                    Object.keys(result.errors).forEach((key) => {
                        form.setError(key as any, {
                            type: "manual",
                            message: Array.isArray(result.errors[key])
                                ? result.errors[key][0]
                                : result.errors[key]
                        })
                    })

                    const firstError = Object.values(result.errors)[0]
                    throw new Error(
                        Array.isArray(firstError) ? firstError[0] : String(firstError)
                    )
                }
                throw new Error(
                    result.message || "Registration failed. Please check your information and try again."
                )
            }

            return result
        } catch (error: any) {
            console.error("Registration error:", error)
            throw error
        }
    }

    const toggleType = (type: string) => {
        const current = form.getValues("types") || []
        const updated = current.includes(type)
            ? current.filter(t => t !== type)
            : [...current, type]
        form.setValue("types", updated, { shouldValidate: true })
    }

    return {
        form,
        metadata,
        isLoadingMetadata,
        metadataError,
        onSubmit: form.handleSubmit(onSubmit),
        isSubmitting: form.formState.isSubmitting,
        isValid: form.formState.isValid,
        errors: form.formState.errors,
        toggleType,
        selectedTypes,
        selectedCountryCode,
        isSupplier: selectedTypes?.includes("SU"),
        isTenant: selectedTypes?.includes("TN"),
        isCustomer: selectedTypes?.includes("CU")
    }
}