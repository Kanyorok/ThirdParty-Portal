import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"
import { useState } from "react"

const registerSchema = z.object({
    firstName: z.string().min(2, "Required"),
    lastName: z.string().min(2, "Required"),
    email: z.string().email("Invalid email"),
    phone: z.string().min(10, "Invalid phone"),
    password: z.string().min(8, "Too short"),
    confirmPassword: z.string(),
    thirdPartyName: z.string().min(2, "Company name required"),
    registrationNumber: z.string().min(2, "Reg number required"),
    taxPIN: z.string().min(2, "Tax PIN required"),
    businessType: z.union([z.string(), z.number()]).refine(val => val !== "", "Required"),
    countryId: z.union([z.string(), z.number()]).refine(val => val !== "", "Required"),
    physicalAddress: z.string().min(1, "Address required"),
    website: z.string().optional().or(z.literal("")),
}).refine((data) => data.password === data.confirmPassword, {
    message: "Passwords don't match",
    path: ["confirmPassword"],
})

export type RegisterFormValues = z.infer<typeof registerSchema>

export const useRegisterForm = () => {
    const [pwdShown, setPwdShown] = useState(false)
    const [confirmShown, setConfirmShown] = useState(false)

    const form = useForm<RegisterFormValues>({
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
            registrationNumber: "",
            taxPIN: "",
            businessType: "",
            countryId: "",
            physicalAddress: "",
            website: ""
        }
    })

    return {
        form,
        pwdShown,
        confirmShown,
        togglePwd: () => setPwdShown(prev => !prev),
        toggleConfirm: () => setConfirmShown(prev => !prev),
        triggerFields: (fields: any) => form.trigger(fields),
    }
}