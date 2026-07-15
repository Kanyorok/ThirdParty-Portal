import { z } from "zod";
import { FieldErrors, FieldValues, DeepRequired } from "react-hook-form";

const USER_TYPE_VALUES = ["tenant", "supplier", "customer"] as const;
type UserTypeValue = typeof USER_TYPE_VALUES[number];

const USER_TYPE_API_VALUES = ["T", "S", "C"] as const;
type UserTypeApiValue = typeof USER_TYPE_API_VALUES[number];

const USER_TYPE_MAP: Record<UserTypeValue, UserTypeApiValue> = {
    tenant: "T",
    supplier: "S",
    customer: "C",
} as const;

const REVERSE_USER_TYPE_MAP: Record<UserTypeApiValue, UserTypeValue> = {
    T: "tenant",
    S: "supplier",
    C: "customer",
} as const;

const phoneField = (requiredMessage: string) =>
    z.string()
        .trim()
        .min(1, requiredMessage)
        .regex(/^\+?[0-9]{8,15}$/, "Phone number must be 8 to 15 digits and may start with +");

// export const userTypeSchema = z.enum(USER_TYPE_VALUES, {
//     required_error: "Select whether you are a tenant or supplier",
//     invalid_type_error: "Select a valid user type",
// });

// export const userTypeApiSchema = z.enum(USER_TYPE_API_VALUES, {
//     required_error: "User type is required",
//     invalid_type_error: "Invalid user type",
// });

export const registerSchema = z.object({
    Name: z.string().min(2, "Company name required"),
    TradingName: z.string().optional(),
    BusinessType: z.string().min(1, "Required"),
    RegistrationNumber: z.string().min(2, "Required"),
    TaxPIN: z.string().min(2, "Required"),
    VATNumber: z.string().optional(),
    Country: z.string().min(1, "Required"),
    Location: z.coerce.number().min(1, "Required"),
    Email: z.string().email("Invalid email"),
    Phone: phoneField("Phone number is required"),
    PhysicalAddress: z.string().optional(),
    Website: z.string().url().optional().or(z.literal("")),
    types: z.array(z.string()).min(1, "Selection required"),
    supplier_category_id: z.coerce.number().optional(),
    tenant_Remarks: z.string().optional(),
    createUser: z.boolean(),
    user_FirstName: z.string().min(2, "Required"),
    user_LastName: z.string().min(2, "Required"),
    user_Email: z.string().email("Invalid email"),
    user_Phone: phoneField("Admin phone is required"),
    user_Gender: z.string().min(1, "Required"),
    user_Password: z.string().min(8, "Min 8 chars"),
    user_Password_confirmation: z.string()
}).refine((data) => data.user_Password === data.user_Password_confirmation, {
    message: "Passwords mismatch",
    path: ["user_Password_confirmation"],
})

export type RegisterFormInputs = z.infer<typeof registerSchema>;
// export type RegisterFormInputs = z.infer<typeof registerSchema>;

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
    userType: UserTypeValue;
};

// export const thirdPartyDetailsSchema = z.object({
//     thirdPartyName: z.string().min(1, "Company Name is required"),
//     tradingName: z.string().optional(),
//     businessType: z.string().min(1, "Business Type is required"),
//     registrationNumber: z.string().min(1, "Registration Number is required"),
//     taxPIN: z.string().min(1, "Tax PIN is required"),
//     vatNumber: z.string().optional(),
//     country: z.string().min(1, "Country is required"),
//     physicalAddress: z.string().min(1, "Physical Address is required"),
//     email: z.string().email("Invalid email address").min(1, "Email is required"),
//     phone: z.string().min(10, "Phone number must be at least 10 digits").max(15, "Phone number cannot exceed 15 digits"),
//     website: z.string().url("Invalid URL format").optional().or(z.literal('')),
//     userType: userTypeSchema,
// }) satisfies z.ZodType<ThirdPartyDetailsFormInputs>;

export type LoginFormInputs = {
    email: string;
    password: string;
};

export const loginSchema = z.object({
    email: z.string().email("Invalid email address").min(1, "Email is required"),
    password: z.string().min(1, "Password is required"),
});

// export type UserProfileUpdateInputs = {
//     firstName: string;
//     lastName: string;
//     phone: string;
//     gender?: string;
// };

export const userProfileUpdateSchema = z.object({
    firstName: z.string().min(1, "First Name is required"),
    lastName: z.string().min(1, "Last Name is required"),
    phone: z.string().min(1, "Phone Number is required").regex(/^\+?[0-9]{8,15}$/, "Phone number must be 8 to 15 digits and may start with +"),
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
    companyEmail: string;
    companyPhone: string;
    website?: string;
    userType: UserTypeValue;
    status: string;
    approvalStatus: string;
};

// export const companyDetailsUpdateSchema = z.object({
//     thirdPartyName: z.string().min(1, "Company Name is required"),
//     tradingName: z.string().optional(),
//     businessType: z.string().min(1, "Business Type is required"),
//     registrationNumber: z.string().min(1, "Registration Number is required"),
//     taxPIN: z.string().min(1, "Tax PIN is required"),
//     vatNumber: z.string().optional(),
//     country: z.string().min(1, "Country is required"),
//     physicalAddress: z.string().min(1, "Physical Address is required"),
//     companyEmail: z.string().email("Invalid email address").min(1, "Email is required"),
//     companyPhone: z.string().min(1, "Phone Number is required").regex(/^\+?\d{10,15}$/, "Invalid phone number format"),
//     website: z.string().url("Invalid URL format").optional().or(z.literal('')),
//     userType: userTypeSchema,
//     status: z.string().min(1, "Status is required"),
//     approvalStatus: z.string().min(1, "Approval Status is required"),
// });

type TouchedFields<T> = {
    [K in keyof T]?: T[K] extends object ? TouchedFields<T[K]> : boolean;
};

export const getFieldStatus = <T extends FieldValues>(
    fieldName: keyof T,
    errors: FieldErrors<T>,
    touchedFields: TouchedFields<DeepRequired<T>>,
    watchedFields: T
): string => {
    const hasError = errors[fieldName] && touchedFields[fieldName];
    const hasValue = watchedFields[fieldName] !== "" &&
        watchedFields[fieldName] !== null &&
        watchedFields[fieldName] !== undefined;
    const isValid = touchedFields[fieldName] && hasValue && !errors[fieldName];

    if (hasError) return "error";
    if (isValid) return "success";
    return "default";
};

export const mapUserTypeToApi = (userType: UserTypeValue): UserTypeApiValue => {
    return USER_TYPE_MAP[userType];
};

export const mapUserTypeFromApi = (apiUserType: UserTypeApiValue): UserTypeValue => {
    return REVERSE_USER_TYPE_MAP[apiUserType];
};

// export const transformRegisterFormDataForApi = (formData: RegisterFormInputs) => {
//     return {
//         FirstName: formData.firstName,
//         LastName: formData.lastName,
//         Email: formData.email,
//         Phone: formData.phone,
//         Password: formData.password,
//         Password_confirmation: formData.confirmPassword,
//         ThirdPartyType: mapUserTypeToApi(formData.userType),
//     };
// };

// export const transformThirdPartyDetailsForApi = (formData: ThirdPartyDetailsFormInputs) => {
//     return {
//         ThirdPartyName: formData.thirdPartyName,
//         TradingName: formData.tradingName,
//         BusinessType: formData.businessType,
//         RegistrationNumber: formData.registrationNumber,
//         TaxPIN: formData.taxPIN,
//         VATNumber: formData.vatNumber,
//         Country: formData.country,
//         PhysicalAddress: formData.physicalAddress,
//         Email: formData.email,
//         Phone: formData.phone,
//         Website: formData.website,
//         ThirdPartyType: mapUserTypeToApi(formData.userType),
//     };
// };

type ServerErrorMap<T> = {
    [key: string]: keyof T;
};

const createErrorMapper = <T extends Record<string, any>>(
    fieldMap: ServerErrorMap<T>
) => {
    return (serverErrors: Record<string, string[]>): Partial<Record<keyof T, string[]>> => {
        const mappedErrors: Partial<Record<keyof T, string[]>> = {};

        for (const serverField in serverErrors) {
            if (!Object.prototype.hasOwnProperty.call(serverErrors, serverField)) {
                continue;
            }

            const clientField = fieldMap[serverField];
            if (clientField) {
                mappedErrors[clientField] = serverErrors[serverField];
            }
        }

        return mappedErrors;
    };
};

// const registerFieldMap: ServerErrorMap<RegisterFormInputs> = {
//     FirstName: "firstName",
//     LastName: "lastName",
//     Email: "email",
//     Phone: "phone",
//     Password: "password",
//     Password_confirmation: "confirmPassword",
//     ThirdPartyType: "userType",
// };

const thirdPartyFieldMap: ServerErrorMap<ThirdPartyDetailsFormInputs> = {
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
    ThirdPartyType: "userType",
};

// export const mapRegisterServerErrorsToFormFields = createErrorMapper<RegisterFormInputs>(registerFieldMap);
export const mapThirdPartyServerErrorsToFormFields = createErrorMapper<ThirdPartyDetailsFormInputs>(thirdPartyFieldMap);

export type { UserTypeValue, UserTypeApiValue };
export { USER_TYPE_VALUES, USER_TYPE_API_VALUES, USER_TYPE_MAP, REVERSE_USER_TYPE_MAP };
