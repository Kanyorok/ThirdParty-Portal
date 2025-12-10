"use client"

import { useState, useCallback, useMemo } from "react"
import { useRouter } from "next/navigation"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { toast } from "sonner"

import type { ApiResponse } from "@/lib/api-types"
import {
    getFieldStatus,
    transformRegisterFormDataForApi,
    mapRegisterServerErrorsToFormFields,
} from "@/lib/form-utils"
import { RegisterFormInputs, registerSchema } from "@/lib/validation"

const API_BASE_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || ""

interface RegisterApiResponse {
    success: boolean
    message: string
    data?: {
        user: {
            id: number
            userId: string
            firstName: string
            lastName: string
            email: string
            phone: string
        }
    }
    errors?: Record<string, string[]>
}

export const useRegisterForm = () => {
    const router = useRouter()
    const [showPassword, setShowPassword] = useState(false)
    const [showConfirmPassword, setShowConfirmPassword] = useState(false)

    const form = useForm<RegisterFormInputs>({
        resolver: zodResolver(registerSchema),
        mode: "onChange",
        defaultValues: {
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            password: "",
            confirmPassword: "",
        },
    })

    const { setError, reset, formState, watch } = form
    const { errors, isSubmitting, touchedFields, isValid } = formState
    const watchedFields = watch()

    const fieldStatuses = useMemo(() => {
        const fields: (keyof RegisterFormInputs)[] = [
            "firstName",
            "lastName",
            "email",
            "phone",
            "password",
            "confirmPassword",
        ]

        return fields.reduce(
            (acc, field) => {
                acc[field] = getFieldStatus(field, errors, touchedFields, watchedFields)
                return acc
            },
            {} as Record<keyof RegisterFormInputs, string>
        )
    }, [errors, touchedFields, watchedFields])

    const handleRegistrationSuccess = useCallback(
        (result: RegisterApiResponse) => {
            const userId = result.data?.user?.userId

            toast.success(result.message || "Registration successful! Please check your email to verify your account.")
            reset()

            setTimeout(() => {
                if (userId) {
                    router.push(`/verify-email?userId=${userId}`)
                } else {
                    router.push("/signin")
                }
            }, 1500)
        },
        [router, reset]
    )

    const handleRegistrationError = useCallback(
        (result: RegisterApiResponse, status: number) => {
            if (status === 422 && result.errors) {
                const mappedErrors = mapRegisterServerErrorsToFormFields(result.errors)

                Object.entries(mappedErrors).forEach(([field, messages]) => {
                    setError(field as keyof RegisterFormInputs, {
                        type: "server",
                        message: Array.isArray(messages) ? messages.join(", ") : messages,
                    })
                })

                toast.error(result.message || "Please fix the validation errors.")
                return
            }

            if (status === 409) {
                toast.error(result.message || "An account with this email already exists.")
                return
            }

            setError("root", {
                type: "server",
                message: result.message || "Registration failed. Please try again.",
            })
            toast.error(result.message || "An unexpected error occurred.")
        },
        [setError]
    )

    const onSubmit = useCallback(
        async (data: RegisterFormInputs) => {
            if (!API_BASE_URL) {
                toast.error("Configuration error. Please contact support.")
                return
            }

            const apiData = transformRegisterFormDataForApi(data)

            try {
                const response = await fetch(`${API_BASE_URL}/api/v1/portal/auth/register`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify(apiData),
                })

                const result: RegisterApiResponse = await response.json()

                if (response.ok && result.success) {
                    handleRegistrationSuccess(result)
                } else {
                    handleRegistrationError(result, response.status)
                }
            } catch (error) {
                console.error("Registration error:", error)
                setError("root", {
                    type: "manual",
                    message: "Unable to connect to the server. Please try again.",
                })
                toast.error("Network error. Please check your connection.")
            }
        },
        [handleRegistrationSuccess, handleRegistrationError, setError]
    )

    return {
        form,
        showPassword,
        setShowPassword,
        showConfirmPassword,
        setShowConfirmPassword,
        onSubmit,
        fieldStatuses,
        isSubmitting,
        isValid,
        errors,
        watchedFields,
        touchedFields,
    }
}