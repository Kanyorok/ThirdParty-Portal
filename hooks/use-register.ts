import { useState, useEffect } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"

import { LookupData } from "@/types/lookups"

const registerSchema = z.object({
    Name: z.string().min(2, "Company name required"),
    TradingName: z.string().optional(),
    BusinessType: z.string().min(1, "Required"),
    RegistrationNumber: z.string().min(2, "Reg number required"),
    TaxPIN: z.string().min(2, "Tax PIN required"),
    VATNumber: z.string().optional(),
    Country: z.string().min(1, "Country code required"),
    Location: z.string().min(1, "Location required"),
    Email: z.string().email("Invalid email").or(z.literal("")),
    Phone: z.string().min(10, "Invalid phone"),
    PhysicalAddress: z.string().optional(),
    Website: z.string().url("Invalid URL").optional().or(z.literal("")),
    types: z.array(z.string()).min(1, "Select at least one type"),
    createUser: z.boolean(),
    user_FirstName: z.string().optional(),
    user_LastName: z.string().optional(),
    user_Email: z.string().email().optional().or(z.literal("")),
    user_Phone: z.string().optional(),
    user_Gender: z.string().optional(),
}).refine((data) => {
    if (data.createUser) {
        return !!data.user_FirstName && !!data.user_LastName && !!data.user_Email
    }
    return true
}, {
    message: "User details are required when creating an account",
    path: ["user_FirstName"],
})

export type RegisterFormValues = z.infer<typeof registerSchema>

export const useRegisterForm = () => {
    const [lookups, setLookups] = useState<LookupData>({})
    const [loadingLookups, setLoadingLookups] = useState(true)
    const [isSubmitting, setIsSubmitting] = useState(false)

    const form = useForm<RegisterFormValues>({
        resolver: zodResolver(registerSchema),
        mode: "onTouched",
        defaultValues: {
            Name: "",
            TradingName: "",
            BusinessType: "",
            RegistrationNumber: "",
            TaxPIN: "",
            VATNumber: "",
            Country: "",
            Location: "",
            Email: "",
            Phone: "",
            PhysicalAddress: "",
            Website: "",
            types: ["Supplier"],
            createUser: true,
            user_FirstName: "",
            user_LastName: "",
            user_Email: "",
            user_Phone: "",
            user_Gender: ""
        }
    })

    useEffect(() => {
        const fetchRequiredData = async () => {
            try {
                const codes = "Gender,BusinessType,IdentificationType"
                const [lookupRes, countryRes] = await Promise.all([
                    fetch(`/api/v1/portal/auth/lookups/bulk?codes=${codes}`),
                    fetch(`/api/v1/portal/auth/metadata/countries`)
                ])

                const lookupJson = await lookupRes.json()
                const countryJson = await countryRes.json()

                setLookups({
                    ...lookupJson.data,
                    Countries: countryJson.map((c: any) => ({ Value: c.id, Description: c.name }))
                })
            } catch (error) {
                console.error("Initialization error:", error)
            } finally {
                setLoadingLookups(false)
            }
        }

        fetchRequiredData()
    }, [])

    const onSubmit = async (data: RegisterFormValues) => {
        setIsSubmitting(true)
        try {
            const response = await fetch('/api/v1/portal/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data),
            })

            const result = await response.json()
            if (!response.ok) throw result

            return result
        } catch (error: any) {
            if (error.errors) {
                Object.keys(error.errors).forEach((key) => {
                    form.setError(key as any, { message: error.errors[key][0] })
                })
            }
            throw error
        } finally {
            setIsSubmitting(false)
        }
    }

    return {
        form,
        lookups,
        loadingLookups,
        isSubmitting,
        onSubmit: form.handleSubmit(onSubmit),
        isValid: form.formState.isValid,
        errors: form.formState.errors
    }
}