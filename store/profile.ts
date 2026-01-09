import { z } from "zod"

export const profileSchema = z.object({
    Name: z.string().min(3, "Company/Full Name is required"),
    TradingName: z.string().optional(),
    Email: z.string().email("Invalid email address"),
    Phone: z.string().min(10, "Valid phone number required"),
    PhysicalAddress: z.string().min(5, "Address is required"),
    Website: z.string().url().optional().or(z.literal("")),

    supplier_category_id: z.coerce.number().optional(),
    tenant_type: z.coerce.number().optional(),
    remarks: z.string().optional(),
    user_DateOfBirth: z.string().optional(),
    user_Gender: z.coerce.number().optional(),
    user_MaritalStatus: z.coerce.number().optional(),
    user_Occupation: z.coerce.number().optional(),
})

export type ProfileFormValues = z.infer<typeof profileSchema>