"use client"

import { useState, useMemo, useCallback } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { registerSchema, type RegisterFormInputs } from "@/lib/validation"

export function useRegisterForm() {
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
            businessType: "",
            countryId: "",
            thirdPartyType: "1",
        } as RegisterFormInputs,
    })

    const [pwdShown, setPwdShown] = useState(false)
    const [confirmShown, setConfirmShown] = useState(false)

    const togglePwd = useCallback(() => setPwdShown(v => !v), [])
    const toggleConfirm = useCallback(() => setConfirmShown(v => !v), [])

    const phoneHandlers = useMemo(() => ({
        onInput: (e: React.FormEvent<HTMLInputElement>) => {
            const target = e.target as HTMLInputElement
            target.value = target.value.replace(/[^\d+]/g, "")
        },
    }), [])

    const triggerFields = useCallback(async (fields: (keyof RegisterFormInputs)[]) => {
        return await form.trigger(fields)
    }, [form])

    const handleSubmitAsync = useCallback(
        async (submitFn: (data: any) => Promise<void>) => {
            const values = form.getValues()
            const payload = {
                FirstName: values.firstName,
                LastName: values.lastName,
                Email: values.email,
                Phone: values.phone,
                Password: values.password,
                Password_confirmation: values.confirmPassword,
                ThirdPartyName: values.thirdPartyName,
                TradingName: values.tradingName || values.thirdPartyName,
                RegistrationNumber: values.registrationNumber,
                TaxPIN: values.taxPIN,
                BusinessType: values.businessType,
                CountryId: values.countryId ? Number(values.countryId) : null,
                ThirdPartyType: values.thirdPartyType,
            }
            await submitFn(payload)
        },
        [form]
    )

    return {
        form,
        errors: form.formState.errors,
        pwdShown,
        confirmShown,
        togglePwd,
        toggleConfirm,
        isSubmitting: form.formState.isSubmitting,
        isValid: form.formState.isValid,
        phoneHandlers,
        register: form.register,
        control: form.control,
        setValue: form.setValue,
        setError: form.setError,
        triggerFields,
        handleSubmitAsync
    }
}