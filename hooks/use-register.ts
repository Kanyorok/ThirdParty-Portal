"use client"

import { useState, useMemo, useCallback } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { RegisterFormInputs, registerSchema } from "@/lib/validation"
import { UserTypeValue } from "@/types/types"

export function useRegisterForm(userType: UserTypeValue) {
    const schema = registerSchema
    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(schema),
        mode: "onChange",
        reValidateMode: "onChange",
        defaultValues: {
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            password: "",
            confirmPassword: "",
            userType: userType.value as RegisterFormInputs["userType"],
        },
    })

    const { watch, formState } = form
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

    const isSubmitting = formState.isSubmitting
    const isValid = formState.isValid
    const errors = formState.errors

    const handleSubmitAsync = useCallback(
        async (submitFn: (data: RegisterFormInputs) => Promise<void>) => {
            await form.handleSubmit(async (data) => {
                await submitFn(data)
            })()
        },
        [form]
    )

    return {
        form,
        errors,
        pwdShown,
        confirmShown,
        togglePwd,
        toggleConfirm,
        isSubmitting,
        isValid,
        phoneHandlers,
        watch,
        handleSubmitAsync
    }
}
