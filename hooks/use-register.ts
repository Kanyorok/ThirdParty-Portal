import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"

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
    user_Email: z.string().email().optional(),
    user_Phone: z.string().optional(),
    user_Gender: z.string().optional(),
}).refine((data) => {
    if (data.createUser) {
        return !!data.user_FirstName && !!data.user_LastName && !!data.user_Email;
    }
    return true;
}, {
    message: "User details are required when creating an account",
    path: ["user_FirstName"],
})

export type RegisterFormValues = z.infer<typeof registerSchema>

export const useRegisterForm = () => {
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

<<<<<<< HEAD
=======
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

            const apiData = {
                ...transformRegisterFormDataForApi(data),
                verification_base_url: typeof window !== 'undefined' ? window.location.origin : ''
            }

            try {
                const response = await fetch(`${API_BASE_URL}/api/third-party-auth/register`, {
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

>>>>>>> dev
    return {
        form,
        triggerFields: (fields: any) => form.trigger(fields),
        isValid: form.formState.isValid,
        errors: form.formState.errors
    }
}