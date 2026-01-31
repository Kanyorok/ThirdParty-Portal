import { FieldErrors, FieldValues, DeepRequired } from "react-hook-form";
import {
    RegisterFormInputs,
    ThirdPartyDetailsFormInputs,
    mapUserTypeToApi,
} from "@/lib/validation";

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

const E164_MIN_LENGTH = 8;
const E164_MAX_LENGTH = 15;

export const normalizeToE164 = (input: string): string => {
    if (!input) return input;

    const digits = input.replace(/\D+/g, "");

    if (digits.length >= E164_MIN_LENGTH && digits.length <= E164_MAX_LENGTH) {
        return `+${digits}`;
    }

    return input;
};

export const transformRegisterFormDataForApi = (formData: RegisterFormInputs) => {
    return {
        ThirdPartyType: mapUserTypeToApi(formData.userType),
        FirstName: formData.firstName,
        LastName: formData.lastName,
        Email: formData.email,
        Phone: normalizeToE164(formData.phone),
        Password: formData.password,
        Password_confirmation: formData.confirmPassword,
    };
};

export const transformThirdPartyDetailsForApi = (formData: ThirdPartyDetailsFormInputs) => {
    return {
        ThirdPartyName: formData.thirdPartyName,
        TradingName: formData.tradingName,
        BusinessType: formData.businessType,
        RegistrationNumber: formData.registrationNumber,
        TaxPIN: formData.taxPIN,
        VATNumber: formData.vatNumber,
        Country: formData.country,
        PhysicalAddress: formData.physicalAddress,
        Email: formData.email,
        Phone: normalizeToE164(formData.phone),
        Website: formData.website,
        ThirdPartyType: mapUserTypeToApi(formData.userType),
    };
};

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

const registerFieldMap: ServerErrorMap<RegisterFormInputs> = {
    FirstName: "firstName",
    LastName: "lastName",
    Email: "email",
    Phone: "phone",
    Password: "password",
    Password_confirmation: "confirmPassword",
    ThirdPartyType: "userType",
};

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

export const mapRegisterServerErrorsToFormFields = createErrorMapper<RegisterFormInputs>(registerFieldMap);
export const mapThirdPartyServerErrorsToFormFields = createErrorMapper<ThirdPartyDetailsFormInputs>(thirdPartyFieldMap);
