import { useState, useEffect, useCallback } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

const registerSchema = z.object({
    Name: z.string().min(2, "Company name required"),
    TradingName: z.string().nullable().optional(),
    BusinessType: z.string().min(1, "Required"),
    RegistrationNumber: z.string().min(2, "Required"),
    TaxPIN: z.string().min(2, "Required"),
    VATNumber: z.string().nullable().optional(),
    Country: z.string().min(1, "Required"),
    Location: z.coerce.number().min(1, "Required"),
    Email: z.string().email("Invalid email"),
    Phone: z.string().min(10, "Invalid phone"),
    PhysicalAddress: z.string().nullable().optional(),
    Website: z.string().url().optional().or(z.literal("")).nullable(),
    types: z.array(z.string()).min(1, "Selection required"),
    user_SupplierCategoryId: z.preprocess((val) => val === "" ? null : val, z.coerce.number().nullable().optional()),
    user_Remarks: z.preprocess((val) => val === "" ? null : val, z.string().nullable().optional()),
    user_DateOfBirth: z.string().nullable().optional(),
    user_MaritalStatus: z.string().nullable().optional(),
    user_Occupation: z.string().nullable().optional(),
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
})

export type RegisterFormInputs = z.infer<typeof registerSchema>

export const useRegisterForm = () => {
    const [metadata, setMetadata] = useState({
        countries: [] as any[],
        businessTypes: [] as any[],
        supplierCategories: [] as any[],
        localities: [] as any[],
        genders: [] as any[],
        maritalStatuses: [] as any[],
        occupations: [] as any[]
    })
    const [isLoadingMetadata, setIsLoadingMetadata] = useState(true)
    const [metadataError, setMetadataError] = useState<string | null>(null)

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema) as any,
        mode: "onBlur",
        defaultValues: {
            Name: "", TradingName: null, BusinessType: "", RegistrationNumber: "", TaxPIN: "", VATNumber: null,
            Country: "KE", Location: 0, Email: "", Phone: "", PhysicalAddress: null, Website: "", types: [],
            user_SupplierCategoryId: null, user_Remarks: null, user_DateOfBirth: null, user_MaritalStatus: null,
            user_Occupation: null, createUser: true, user_FirstName: "", user_LastName: "", user_Email: "",
            user_Phone: "", user_Gender: "", user_Password: "", user_Password_confirmation: ""
        }
    })

    const selectedCountryCode = form.watch("Country")
    const selectedTypes = form.watch("types")

    const fetchInitialMetadata = useCallback(async () => {
        setIsLoadingMetadata(true)
        try {
            const [countriesRes, categoriesRes, lookupsRes] = await Promise.all([
                fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/metadata/countries`),
                fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/metadata/supplier-categories`),
                fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/lookups/bulk?codes=Gender,BusinessType,MaritalStatus,Occupation`)
            ])
            const countries = await countriesRes.json()
            const categories = await categoriesRes.json()
            const lookups = await lookupsRes.json()

            const lData = lookups.data || {}
            setMetadata(prev => ({
                ...prev,
                countries: countries.data || countries,
                supplierCategories: categories.data || categories,
                businessTypes: lData.businessType || [],
                genders: lData.gender || [],
                maritalStatuses: lData.maritalStatus || [],
                occupations: lData.occupation || [],
            }))
        } catch (error) {
            setMetadataError("Unable to load registration data.")
        } finally {
            setIsLoadingMetadata(false)
        }
    }, [])

    const fetchLocalities = useCallback(async (countryCode: string) => {
        const country = metadata.countries.find(c => c.code === countryCode)
        if (!country?.id) return
        try {
            const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/metadata/localities/${country.id}`)
            const result = await res.json()
            setMetadata(prev => ({ ...prev, localities: result.data || result || [] }))
        } catch (error) { console.error(error) }
    }, [metadata.countries])

    useEffect(() => { fetchInitialMetadata() }, [fetchInitialMetadata])
    useEffect(() => { if (selectedCountryCode) fetchLocalities(selectedCountryCode) }, [selectedCountryCode, fetchLocalities])

    const onSubmitHandler = async (values: RegisterFormInputs) => {
        const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`, {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            body: JSON.stringify(values)
        })
        const result = await response.json()
        if (!response.ok) throw new Error(result.message || "Registration failed")
        return result
    }

    return {
        form,
        errors: form.formState.errors,
        metadata,
        isLoadingMetadata,
        metadataError,
        onSubmit: form.handleSubmit(onSubmitHandler as any),
        isSubmitting: form.formState.isSubmitting,
        toggleType: (type: string) => {
            const current = form.getValues("types") || []
            const updated = current.includes(type) ? current.filter(t => t !== type) : [...current, type]
            form.setValue("types", updated, { shouldValidate: true })
        },
        selectedTypes,
        isSupplier: selectedTypes?.includes("SU"),
        isTenant: selectedTypes?.includes("TN"),
        isCustomer: selectedTypes?.includes("CU")
    }
}