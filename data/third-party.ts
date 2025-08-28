import { z } from 'zod';

export enum BusinessTypeEnum {
    SoleProprietorship = 1,
    Partnership = 2,
    LimitedCompany = 3,
    NonProfit = 4,
    Other = 5,
}

export const thirdPartySchema = z.object({
    id: z.number().optional(),
    thirdPartyName: z.string().min(1, { message: "Legal Name is required." }),
    tradingName: z.string().nullable().optional().transform(e => e === "" ? null : e),
    businessType: z.nativeEnum(BusinessTypeEnum, { invalid_type_error: "Invalid business type selected." }),
    registrationNumber: z.string().min(1, { message: "Registration Number is required." }),
    taxPIN: z.string().min(1, { message: "Tax PIN is required." }),
    vatNumber: z.string().nullable().optional().transform(e => e === "" ? null : e),
    country: z.string().min(1, { message: "Country is required." }),
    physicalAddress: z.string().min(1, { message: "Physical Address is required." }),
    email: z.string().email({ message: "Invalid email address." }),
    phone: z.string().min(1, { message: "Phone number is required." }).regex(/^\+?[0-9()\s-]+$/, { message: "Invalid phone number format." }),
    website: z.string().url({ message: "Invalid URL format." }).nullable().optional().transform(e => e === "" ? null : e),
});

export type ThirdPartyInputs = z.infer<typeof thirdPartySchema>;

export interface ThirdPartyProfile {
    id: number;
    thirdPartyName: string;
    tradingName: string | null;
    businessType: BusinessTypeEnum;
    registrationNumber: string;
    taxPIN: string;
    vatNumber: string | null;
    country: string;
    physicalAddress: string;
    email: string;
    phone: string;
    website: string | null;
    createdOn: string;
    modifiedOn: string | null;
    status: number;
    thirdPartyType: number;
    approvalStatus: number;
}