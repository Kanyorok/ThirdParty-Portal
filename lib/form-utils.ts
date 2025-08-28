import { FieldErrors, FieldValues, DeepRequired } from "react-hook-form";
import { RegisterFormInputs, ThirdPartyDetailsFormInputs } from "@/lib/validation";

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