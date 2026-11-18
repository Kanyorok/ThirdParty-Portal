export type PartyIdentityInput = {
    IdentityKind?: string
    IdentityDocumentType?: string
    IdentityIssuingCountry?: string
    BusinessType?: string
}

export const isPersonalBusinessType = (value: string) => ["I", "Sole"].includes(value)

export function identityIssues(data: PartyIdentityInput): Array<{ field: string; message: string }> {
    const issues: Array<{ field: string; message: string }> = []
    if (!["individual", "organisation"].includes(data.IdentityKind ?? "")) {
        issues.push({ field: "IdentityKind", message: "Choose Individual or Organisation." })
        return issues
    }
    if (data.IdentityKind === "individual" && !["national_id", "passport"].includes(data.IdentityDocumentType ?? "")) {
        issues.push({ field: "IdentityDocumentType", message: "Choose National ID or Passport." })
    }
    if (!data.IdentityIssuingCountry) {
        issues.push({ field: "IdentityIssuingCountry", message: "Choose the document or registration issuing country." })
    }
    if (data.BusinessType && isPersonalBusinessType(data.BusinessType) !== (data.IdentityKind === "individual")) {
        issues.push({ field: "BusinessType", message: "Choose a business type matching Individual or Organisation." })
    }
    return issues
}