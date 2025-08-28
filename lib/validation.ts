// lib/validation.ts
import { z } from "zod";
import { FieldErrors, FieldValues, DeepRequired } from "react-hook-form";

export const registerSchema = z.object({
    firstName: z.string().min(1, "First Name is required"),
    lastName: z.string().min(1, "Last Name is required"),
    email: z.string().email("Invalid email address").min(1, "Email is required"),
    phone: z.string().min(10, "Phone number must be at least 10 digits").max(15, "Phone number cannot exceed 15 digits"),
    password: z
        .string()
        .min(8, "Password must be at least 8 characters")
        .regex(/[A-Z]/, "Password must contain at least one uppercase letter")
        .regex(/[a-z]/, "Password must contain at least one lowercase letter")
        .regex(/[0-9]/, "Password must contain at least one number")
        .regex(/[^a-zA-Z0-9]/, "Password must contain at least one special character"),
    confirmPassword: z.string().min(1, "Confirm Password is required"),
}).refine((data) => data.password === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
});

export type RegisterFormInputs = z.infer<typeof registerSchema>;

export type ThirdPartyDetailsFormInputs = {
    thirdPartyName: string;
    tradingName?: string;
    businessType: string;
    registrationNumber: string;
    taxPIN: string;
    vatNumber?: string;
    country: string;
    physicalAddress: string;
    email: string;
    phone: string;
    website?: string;
    thirdPartyType: "" | "S" | "T";
};

export const thirdPartyDetailsSchema = z.object({
    thirdPartyName: z.string().min(1, "Company Name is required"),
    tradingName: z.string().optional(),
    businessType: z.string().min(1, "Business Type is required"),
    registrationNumber: z.string().min(1, "Registration Number is required"),
    taxPIN: z.string().min(1, "Tax PIN is required"),
    vatNumber: z.string().optional(),
    country: z.string().min(1, "Country is required"),
    physicalAddress: z.string().min(1, "Physical Address is required"),
    email: z.string().email("Invalid email address").min(1, "Email is required"),
    phone: z.string().min(10, "Phone number must be at least 10 digits").max(15, "Phone number cannot exceed 15 digits"),
    website: z.string().url("Invalid URL format").optional().or(z.literal('')),
    thirdPartyType: z.union([
        z.literal(""),
        z.enum(["S", "T"])
    ]).refine(value => value !== "", {
        message: "Third Party Type is required",
    }),
}) satisfies z.ZodType<ThirdPartyDetailsFormInputs>;

// Re-exporting these for use in components/hooks
export type LoginFormInputs = {
    email: string;
    password: string;
};

export const loginSchema = z.object({
    email: z.string().email("Invalid email address").min(1, "Email is required"),
    password: z.string().min(1, "Password is required"),
});

export type UserProfileUpdateInputs = {
    firstName: string;
    lastName: string;
    phone: string;
    gender?: string;
};

export const userProfileUpdateSchema = z.object({
    firstName: z.string().min(1, "First Name is required"),
    lastName: z.string().min(1, "Last Name is required"),
    phone: z.string().min(1, "Phone Number is required").regex(/^\+?\d{10,15}$/, "Invalid phone number format"),
    gender: z.string().optional(),
});

export type CompanyDetailsUpdateInputs = {
    thirdPartyName: string;
    tradingName?: string;
    businessType: string;
    registrationNumber: string;
    taxPIN: string;
    vatNumber?: string;
    country: string;
    physicalAddress: string;
    companyEmail: string; // Renamed to avoid conflict with user email in combined profile
    companyPhone: string; // Renamed to avoid conflict with user phone in combined profile
    website?: string;
    thirdPartyType: "" | "S" | "T";
    status: string;
    approvalStatus: string;
};

export const companyDetailsUpdateSchema = z.object({
    thirdPartyName: z.string().min(1, "Company Name is required"),
    tradingName: z.string().optional(),
    businessType: z.string().min(1, "Business Type is required"),
    registrationNumber: z.string().min(1, "Registration Number is required"),
    taxPIN: z.string().min(1, "Tax PIN is required"),
    vatNumber: z.string().optional(),
    country: z.string().min(1, "Country is required"),
    physicalAddress: z.string().min(1, "Physical Address is required"),
    companyEmail: z.string().email("Invalid email address").min(1, "Email is required"),
    companyPhone: z.string().min(1, "Phone Number is required").regex(/^\+?\d{10,15}$/, "Invalid phone number format"),
    website: z.string().url("Invalid URL format").optional().or(z.literal('')),
    thirdPartyType: z.union([
        z.literal(""),
        z.enum(["S", "T"])
    ]).refine(value => value !== "", {
        message: "Third Party Type is required",
    }),
    status: z.string().min(1, "Status is required"),
    approvalStatus: z.string().min(1, "Approval Status is required"),
});

// --- Form Utilities (moved from form-utils.ts into validation.ts as per your structure) ---

type TouchedFields<T> = {
    [K in keyof T]?: T[K] extends object ? TouchedFields<T[K]> : boolean;
};

export const getFieldStatus = <T extends FieldValues>(
    fieldName: keyof T,
    errors: FieldErrors<T>,
    touchedFields: TouchedFields<DeepRequired<T>>,
    watchedFields: T
): string => {
    if (errors[fieldName] && touchedFields[fieldName]) {
        return "error";
    }
    if (touchedFields[fieldName] && watchedFields[fieldName] !== "" && watchedFields[fieldName] !== null && watchedFields[fieldName] !== undefined && !errors[fieldName]) {
        return "success";
    }
    return "default";
};

export const transformRegisterFormDataForApi = (formData: RegisterFormInputs) => {
    const { firstName, lastName, email, phone, password, confirmPassword } = formData;
    return {
        FirstName: firstName,
        LastName: lastName,
        Email: email,
        Phone: phone,
        Password: password,
        Password_confirmation: confirmPassword,
    };
};

export const transformThirdPartyDetailsForApi = (formData: ThirdPartyDetailsFormInputs) => {
    const {
        thirdPartyName, tradingName, businessType, registrationNumber,
        taxPIN, vatNumber, country, physicalAddress, email,
        phone, website, thirdPartyType,
    } = formData;

    const mappedThirdPartyType = (() => {
        switch (thirdPartyType) {
            case "S": return "S";
            case "T": return "T";
            default: return "";
        }
    })();

    return {
        ThirdPartyName: thirdPartyName,
        TradingName: tradingName,
        BusinessType: businessType,
        RegistrationNumber: registrationNumber,
        TaxPIN: taxPIN,
        VATNumber: vatNumber,
        Country: country,
        PhysicalAddress: physicalAddress,
        Email: email,
        Phone: phone,
        Website: website,
        ThirdPartyType: mappedThirdPartyType,
    };
};

export const mapRegisterServerErrorsToFormFields = (serverErrors: Record<string, string[]>): Record<keyof RegisterFormInputs, string[]> => {
    const mappedErrors: Record<keyof RegisterFormInputs, string[]> = {} as Record<keyof RegisterFormInputs, string[]>;

    const fieldMap: { [key: string]: keyof RegisterFormInputs } = {
        FirstName: "firstName",
        LastName: "lastName",
        Email: "email",
        Phone: "phone",
        Password: "password",
        Password_confirmation: "confirmPassword",
    };

    for (const serverField in serverErrors) {
        if (Object.prototype.hasOwnProperty.call(serverErrors, serverField)) {
            const clientField = fieldMap[serverField];
            if (clientField) {
                mappedErrors[clientField] = serverErrors[serverField];
            }
        }
    }
    return mappedErrors;
};

export const mapThirdPartyServerErrorsToFormFields = (serverErrors: Record<string, string[]>): Record<keyof ThirdPartyDetailsFormInputs, string[]> => {
    const mappedErrors: Record<keyof ThirdPartyDetailsFormInputs, string[]> = {} as Record<keyof ThirdPartyDetailsFormInputs, string[]>;

    const fieldMap: { [key: string]: keyof ThirdPartyDetailsFormInputs } = {
        ThirdPartyName: "thirdPartyName",
        TradingName: "tradingName",
        BusinessType: "businessType",
        RegistrationNumber: "registrationNumber",
        TaxPIN: "taxPIN",
        VATNumber: "vatNumber",
        Country: "country",
        PhysicalAddress: "physicalAddress",
        Email: "email",
        Phone: "phone",
        Website: "website",
        ThirdPartyType: "thirdPartyType",
    };

    for (const serverField in serverErrors) {
        if (Object.prototype.hasOwnProperty.call(serverErrors, serverField)) {
            const clientField = fieldMap[serverField];
            if (clientField) {
                mappedErrors[clientField] = serverErrors[serverField];
            }
        }
    }
    return mappedErrors;
};
