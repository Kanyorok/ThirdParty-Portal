import { z } from "zod";

export const RawThirdPartySchema = z.object({
    Id: z.number().optional(),
    ThirdPartyName: z.string().optional(),
    TradingName: z.string().nullable().optional(),
    BusinessType: z.union([z.string(), z.number()]).optional(),
    RegistrationNumber: z.string().nullable().optional(),
    TaxPIN: z.string().nullable().optional(),
    VATNumber: z.string().nullable().optional(),
    CountryId: z.union([z.string(), z.number()]).nullable().optional(),
    Country: z.string().nullable().optional(),
    PhysicalAddress: z.string().nullable().optional(),
    Email: z.string().optional(),
    Phone: z.string().nullable().optional(),
    Website: z.string().nullable().optional(),
    ApprovalStatus: z.string().nullable().optional(),
    Status: z.string().nullable().optional(),
    ThirdPartyType: z.string().nullable().optional(),
    IsPrequalified: z.boolean().nullable().optional(),
    IDNumber: z.string().nullable().optional(),
    PassportNo: z.string().nullable().optional(),
    CreatedOn: z.string().nullable().optional(),
    ModifiedOn: z.string().nullable().optional(),
    IsActive: z.boolean().optional(),
});

export type RawThirdParty = z.infer<typeof RawThirdPartySchema>;

export function toPascalPayload(frontend: any) {
    const out: Record<string, unknown> = {};
    if (frontend.thirdPartyName !== undefined) out.ThirdPartyName = frontend.thirdPartyName;
    if (frontend.tradingName !== undefined) out.TradingName = frontend.tradingName ?? null;
    if (frontend.businessType !== undefined) out.BusinessType = frontend.businessType;
    if (frontend.registrationNumber !== undefined) out.RegistrationNumber = frontend.registrationNumber ?? null;
    if (frontend.taxPin !== undefined) out.TaxPIN = frontend.taxPin ?? null;
    if (frontend.vatNumber !== undefined) out.VATNumber = frontend.vatNumber ?? null;
    if (frontend.countryId !== undefined) out.CountryId = frontend.countryId ?? null;
    if (frontend.physicalAddress !== undefined) out.PhysicalAddress = frontend.physicalAddress ?? null;
    if (frontend.email !== undefined) out.Email = frontend.email;
    if (frontend.phone !== undefined) out.Phone = frontend.phone ?? null;
    if (frontend.website !== undefined) out.Website = frontend.website ?? null;
    if (frontend.idNumber !== undefined) out.IDNumber = frontend.idNumber ?? null;
    if (frontend.passportNo !== undefined) out.PassportNo = frontend.passportNo ?? null;
    if (frontend.isPrequalified !== undefined) out.IsPrequalified = frontend.isPrequalified ?? null;
    return out;
}

export function normalizeRawToDomain(raw: RawThirdParty) {
    const parsed = RawThirdPartySchema.parse(raw);
    const id = parsed.Id ?? null;
    const thirdPartyName = parsed.ThirdPartyName ?? "";
    const tradingName = parsed.TradingName ?? null;
    const businessType = parsed.BusinessType ?? null;
    const registrationNumber = parsed.RegistrationNumber ?? null;
    const taxPin = parsed.TaxPIN ?? null;
    const vatNumber = parsed.VATNumber ?? null;
    const kraNo = parsed.TaxPIN ?? parsed.RegistrationNumber ?? null;
    const idNumber = parsed.IDNumber ?? null;
    const passportNo = parsed.PassportNo ?? null;
    const countryId = parsed.CountryId ?? null;
    const country = parsed.Country ?? null;
    const physicalAddress = parsed.PhysicalAddress ?? null;
    const email = parsed.Email ?? "";
    const phone = parsed.Phone ?? null;
    const website = parsed.Website ?? null;
    const approvalStatus = parsed.ApprovalStatus ?? null;
    const status = parsed.Status ?? null;
    const thirdPartyType = parsed.ThirdPartyType ?? null;
    const isPrequalified = parsed.IsPrequalified ?? null;
    const createdOn = parsed.CreatedOn ?? null;
    const modifiedOn = parsed.ModifiedOn ?? null;
    const isActive = parsed.IsActive ?? false;
    return {
        organization: {
            id,
            name: thirdPartyName,
            tradingName,
            type: businessType,
            isActive,
        },
        compliance: {
            registrationNumber,
            taxPin,
            vatNumber,
            kraNo,
        },
        identity: {
            idNumber,
            passportNo,
            isPrequalified,
        },
        contact: {
            email,
            phone,
            website,
            physicalAddress,
        },
        location: {
            countryId,
            country,
        },
        meta: {
            approvalStatus,
            status,
            thirdPartyType,
            createdOn,
            modifiedOn,
        },
    };
}

export function normalizeExternalProfileResponse(payload: any) {
    if (!payload) return null;
    const userProfile = payload.userProfile ?? payload.user_profile ?? payload;
    const third = userProfile?.thirdParty ?? userProfile?.third_party ?? userProfile;
    if (!third) return null;
    return normalizeRawToDomain(third as RawThirdParty);
}
