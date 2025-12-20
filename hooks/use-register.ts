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

            // Backend returns: { message, token, user: { id, userId, ... } }
            // So we check for result.user
            if (result.status === "success" || (result as any).user) {

                toast.success(result.message || "Registration successful!");
                reset();

                // Extract userId from the nested user object
                const u = (result as any).user;
                const userId = u?.id || u?.userId || (result as any).userId || null;



                setTimeout(() => {
                    // For the 'check-email' flow, we don't strictly need userId in the URL,
                    // but we check it to confirm we have a valid registration.
                    if (userId) {

                        router.push('/check-email');
                    } else {

                        // Fallback to signin if something is weird, but we should show the check email page ideally.
                        router.push("/signin");
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