"use client"

import { useState, useCallback } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { registerSchema, type RegisterFormInputs } from "@/lib/validation"

export function useRegisterForm() {
    const [pwdShown, setPwdShown] = useState(false)
    const [confirmShown, setConfirmShown] = useState(false)

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema),
        mode: "onTouched",
        defaultValues: {
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            password: "",
            confirmPassword: "",
            thirdPartyName: "",
            tradingName: "",
            registrationNumber: "",
            taxPIN: "",
            businessType: 0,
            countryId: 0,
            thirdPartyType: "Supplier",
            physicalAddress: "",
            website: ""
        },
    })

    const togglePwd = useCallback(() => setPwdShown((v) => !v), [])
    const toggleConfirm = useCallback(() => setConfirmShown((v) => !v), [])

    const handleSubmitAsync = useCallback(
        async (step: "form" | "profile", submitFn: (payload: any) => Promise<void>) => {
            const values = form.getValues()

            if (step === "form") {
                const stepOnePayload = {
                    FirstName: values.firstName,
                    LastName: values.lastName,
                    Email: values.email,
                    Phone: values.phone,
                    Password: values.password,
                    Password_confirmation: values.confirmPassword,
                }
                await submitFn(stepOnePayload)
            } else {
                const stepTwoPayload = {
                    companyName: values.thirdPartyName,
                    tradingName: values.tradingName || values.thirdPartyName,
                    businessType: Number(values.businessType),
                    registrationNumber: values.registrationNumber,
                    taxPIN: values.taxPIN,
                    countryId: Number(values.countryId),
                    physicalAddress: values.physicalAddress,
                    companyEmail: values.email,
                    companyPhone: values.phone,
                    website: values.website || null,
                    accountType: values.thirdPartyType.toLowerCase(),
                    supplierCategories: [],
                }
                await submitFn(stepTwoPayload)
            }
        },
        [form]
    )

    return {
        form,
        pwdShown,
        confirmShown,
        togglePwd,
        toggleConfirm,
        handleSubmitAsync,
        triggerFields: form.trigger,
    }
}