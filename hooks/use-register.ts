// src/hooks/use-register.ts
"use client";

import { useState, useCallback, useMemo } from "react";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { toast } from "sonner";

import type { ApiResponse } from "@/lib/api-types";
import { getFieldStatus, transformRegisterFormDataForApi, mapRegisterServerErrorsToFormFields } from "@/lib/form-utils";
import { RegisterFormInputs, registerSchema } from "@/lib/validation";

export const useRegisterForm = () => {
    const router = useRouter();
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

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
    });

    const { setError, reset, formState, watch } = form;
    const { errors, isSubmitting, touchedFields, isValid } = formState;
    const watchedFields = watch();

    const fieldStatuses = useMemo(() => {
        const fields: (keyof RegisterFormInputs)[] = [
            "firstName",
            "lastName",
            "email",
            "phone",
            "password",
            "confirmPassword",
        ];

        return fields.reduce(
            (acc, field) => {
                acc[field] = getFieldStatus(field, errors, touchedFields, watchedFields);
                return acc;
            },
            {} as Record<keyof RegisterFormInputs, string>,
        );
    }, [errors, touchedFields, watchedFields]);

    const handleRegistrationSuccess = useCallback(
        async (result: ApiResponse) => {
            console.log("--Haha--")
            if (result.status === "success" || (result as any).userId) {
                console.log("Register success:", result)
                toast.success(result.message || "Registration successful!");
                reset();
                const userId = (result as any).userId || null;
                console.log("Extracted userId for redirect:", userId);

                setTimeout(() => {
                    if (userId) {
                        console.log("setTimeout callback triggered, attempting redirect...");
                        router.push(`/third-party-details?user_id=${userId}`);
                    } else {
                        console.error("UserId is null or undefined, cannot redirect to third-party details.")
                        toast.error("User ID not received. Please contact support.");
                        router.push("/auth/signin");
                    }
                }, 2000);
            }
        },
        [router, reset],
    );

    const handleRegistrationError = useCallback(
        (result: ApiResponse) => {
            if (result.status === "error" && result.errors) {
                const mappedErrors = mapRegisterServerErrorsToFormFields(result.errors);

                Object.entries(mappedErrors).forEach(([field, messages]) => {
                    setError(field as keyof RegisterFormInputs, {
                        type: "server",
                        message: messages.join(", "),
                    });
                });

                toast.error(result.message || "Registration failed due to validation errors.");
            } else {
                setError("root", {
                    type: "server",
                    message: result.message || "Registration failed. Please try again.",
                });
                toast.error(result.message || "An unexpected error occurred during registration.");
            }
        },
        [setError],
    );

    const onSubmit = useCallback(
        async (data: RegisterFormInputs) => {
            const apiData = transformRegisterFormDataForApi(data);

            try {
                const response = await fetch(`${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/third-party-auth/register`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify(apiData),
                });

                const result: ApiResponse = await response.json();

                if (response.ok) {
                    await handleRegistrationSuccess(result);
                } else {
                    handleRegistrationError(result);
                }
            } catch (error) {
                console.error("Registration network error:", error);
                setError("root", {
                    type: "manual",
                    message: "Network error. Please check your connection and try again.",
                });
                toast.error("Network error. Please check your connection and try again.");
            }
        },
        [handleRegistrationSuccess, handleRegistrationError, setError],
    );

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
    };
};