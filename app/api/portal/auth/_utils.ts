import { NextResponse } from "next/server"

const SENSITIVE_ERROR_PATTERN = /(exception|stack|trace|sql|syntax|internal server|undefined|vendor|route|line\s+\d+)/i
const TRANSLATION_KEY_PATTERN = /^(validation|auth|passwords)\.[a-z0-9_.-]+$/i
const DOCUMENT_KEY_PATTERN = /^registration_documents\.(\d+)$/
const DOCUMENT_NOTE_KEY_PATTERN = /^registration_document_notes\.(\d+)$/

const FIELD_FALLBACK_MESSAGES: Record<string, string> = {
    Name: "Please enter a valid legal company name.",
    TradingName: "Please enter a valid trading name.",
    BusinessType: "Please select a valid business type.",
    RegistrationNumber: "Please enter a valid registration number.",
    TaxPIN: "Please enter a valid tax PIN.",
    VATNumber: "Please enter a valid VAT number.",
    legalForm: "Please select a valid legal form.",
    contactPersonName: "Please enter the contact person's name.",
    contactPersonEmail: "Please enter a valid contact person email address.",
    contactPersonPhone: "Please enter a valid contact person phone number.",
    Country: "Please select a valid country.",
    Location: "Please select a valid location.",
    Email: "Please enter a valid business email address.",
    Phone: "Please enter a valid business phone number.",
    PhysicalAddress: "Please enter a valid physical address.",
    Website: "Please enter a valid website URL.",
    types: "Please select at least one business role.",
    supplier_category_id: "Please select a supplier category.",
    category_ids: "Please select at least one supplier category.",
    user_Remarks: "Please provide tenant remarks.",
    user_DateOfBirth: "Please provide a valid date of birth.",
    user_MaritalStatus: "Please select a valid marital status.",
    user_Occupation: "Please select a valid occupation.",
    user_FirstName: "Please enter a valid first name.",
    user_LastName: "Please enter a valid last name.",
    user_Email: "Please enter a valid admin email address.",
    user_Phone: "Please enter a valid admin phone number.",
    user_Gender: "Please select a valid gender.",
    user_Password: "Please enter a valid password.",
    user_Password_confirmation: "Please confirm your password.",
    logo: "Please attach a valid logo image.",
}

export function getFallbackFieldMessage(field: string) {
    if (DOCUMENT_KEY_PATTERN.test(field)) return "Please attach a valid document file."
    if (DOCUMENT_NOTE_KEY_PATTERN.test(field)) return "Please provide a valid document note."
    return FIELD_FALLBACK_MESSAGES[field] ?? "Please provide a valid value."
}

export function sanitizeFieldErrorMessage(field: string, candidate: unknown) {
    const fallback = getFallbackFieldMessage(field)
    if (typeof candidate !== "string") return fallback

    const normalized = candidate.replace(/\s+/g, " ").trim()
    if (!normalized || normalized.length > 140 || SENSITIVE_ERROR_PATTERN.test(normalized) || TRANSLATION_KEY_PATTERN.test(normalized)) {
        return fallback
    }

    return normalized
}

export function sanitizeFieldErrors(errors: unknown): Record<string, string[]> {
    if (!errors || typeof errors !== "object" || Array.isArray(errors)) return {}

    const safe: Record<string, string[]> = {}
    Object.entries(errors as Record<string, unknown>).forEach(([field, value]) => {
        const first = Array.isArray(value) ? value[0] : value
        safe[field] = [sanitizeFieldErrorMessage(field, first)]
    })

    return safe
}

export function toUpstreamErrorResponse(body: unknown, status: number) {
    return NextResponse.json(body ?? { message: "Upstream Error" }, { status })
}

export function toServerErrorResponse(defaultData: unknown) {
    return NextResponse.json({ message: "Internal server error", data: defaultData }, { status: 500 })
}