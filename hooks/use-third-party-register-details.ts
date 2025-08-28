"use client";

import { useCallback, useMemo } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { toast } from "sonner";

import type { ApiResponse } from "@/lib/api-types";
import { getFieldStatus, transformThirdPartyDetailsForApi, mapThirdPartyServerErrorsToFormFields } from "@/lib/form-utils";
import { ThirdPartyDetailsFormInputs, thirdPartyDetailsSchema } from "@/lib/validation";

export const useThirdPartyRegisterDetailsForm = (p0: { userId: string | null; onSuccess: (thirdPartyId: number) => void; }) => {
    const router = useRouter();
    const searchParams = useSearchParams();
    const userId = searchParams.get('user_id');

    const form = useForm<ThirdPartyDetailsFormInputs>({
        resolver: zodResolver(thirdPartyDetailsSchema),
        mode: "onChange",
        defaultValues: {
            thirdPartyName: "",
            tradingName: "",
            businessType: "",
            registrationNumber: "",
            taxPIN: "",
            vatNumber: "",
            country: "",
            physicalAddress: "",
            email: "",
            phone: "",
            website: "",
            thirdPartyType: "",
        },
    });

    const { setError, reset, formState, watch } = form;
    const { errors, isSubmitting, isValid } = formState;
    const watchedFields = watch();

    const fieldStatuses = useMemo(() => {
        const fields: (keyof ThirdPartyDetailsFormInputs)[] = [
            "thirdPartyName", "tradingName", "businessType", "registrationNumber",
            "taxPIN", "vatNumber", "country", "physicalAddress", "email",
            "phone", "website", "thirdPartyType",
        ];
        return fields.reduce(
            (acc, field) => {
                acc[field] = getFieldStatus(field, errors, formState.touchedFields, watchedFields);
                return acc;
            },
            {} as Record<keyof ThirdPartyDetailsFormInputs, string>,
        );
    }, [errors, formState.touchedFields, watchedFields]);

    const handleSubmitSuccess = useCallback(
        async (result: ApiResponse) => {
            if (result.status === "success") {
                toast.success(result.message || "Third-party details submitted successfully! Your account is pending approval.");
                reset();
                setTimeout(() => {
                    router.push("/auth/registration-pending");
                }, 2000);
            }
        },
        [router, reset],
    );

    const handleSubmitError = useCallback(
        (result: ApiResponse) => {
            if (result.status === "error" && result.errors) {
                const mappedErrors = mapThirdPartyServerErrorsToFormFields(result.errors);

                Object.entries(mappedErrors).forEach(([field, messages]) => {
                    setError(field as keyof ThirdPartyDetailsFormInputs, {
                        type: "server",
                        message: messages.join(", "),
                    });
                });
                toast.error(result.message || "Submission failed due to validation errors.");
            } else {
                setError("root", {
                    type: "server",
                    message: result.message || "Submission failed. Please try again.",
                });
                toast.error(result.message || "An unexpected error occurred during submission.");
            }
        },
        [setError],
    );

    const onSubmit = useCallback(
        async (data: ThirdPartyDetailsFormInputs) => {
            if (!userId) {
                toast.error("User ID is missing. Please restart the registration process.");
                router.push("/auth/register");
                return;
            }

            const apiData = {
                ...transformThirdPartyDetailsForApi(data),
                user_id: userId,
            };

            try {
                const response = await fetch(`${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/third-parties/register-details`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify(apiData),
                });

                const result: ApiResponse = await response.json();

                if (response.ok) {
                    await handleSubmitSuccess(result);
                } else {
                    handleSubmitError(result);
                }
            } catch (error) {
                setError("root", {
                    type: "manual",
                    message: "Network error. Please check your connection and try again.",
                });
                toast.error("Network error. Please check your connection and try again.");
            }
        },
        [userId, handleSubmitSuccess, handleSubmitError, setError, router],
    );

    return {
        form,
        onSubmit,
        fieldStatuses,
        isSubmitting,
        isValid,
        errors,
        watchedFields,
    };
};