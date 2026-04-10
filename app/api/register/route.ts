import { NextResponse } from "next/server";

const PHONE_REGEX = /^\+?[0-9]{8,15}$/;
const SENSITIVE_ERROR_PATTERN = /(exception|stack|trace|sql|syntax|internal server|undefined|vendor|route|line\s+\d+)/i;

const FIELD_FALLBACK_MESSAGES: Record<string, string> = {
    Name: "Please enter a valid legal company name.",
    TradingName: "Please enter a valid trading name.",
    BusinessType: "Please select a valid business type.",
    RegistrationNumber: "Please enter a valid registration number.",
    TaxPIN: "Please enter a valid tax PIN.",
    VATNumber: "Please enter a valid VAT number.",
    legalForm: "Please select a legal form.",
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
};

const sanitizeFieldErrorMessage = (field: string, candidate: unknown) => {
    const fallback = FIELD_FALLBACK_MESSAGES[field] ?? "Please provide a valid value.";
    if (typeof candidate !== "string") return fallback;

    const normalized = candidate.replace(/\s+/g, " ").trim();
    if (!normalized || normalized.length > 140 || SENSITIVE_ERROR_PATTERN.test(normalized)) {
        return fallback;
    }

    return normalized;
};

const sanitizeFieldErrors = (errors: unknown): Record<string, string[]> => {
    if (!errors || typeof errors !== "object" || Array.isArray(errors)) return {};

    const safe: Record<string, string[]> = {};
    Object.entries(errors as Record<string, unknown>).forEach(([field, value]) => {
        const first = Array.isArray(value) ? value[0] : value;
        safe[field] = [sanitizeFieldErrorMessage(field, first)];
    });
    return safe;
};

const validatePhone = (value: unknown, required: boolean, label: string): string[] => {
    const text = typeof value === "string" ? value.trim() : "";
    const issues: string[] = [];

    if (!text) {
        if (required) issues.push(`${label} is required.`);
        return issues;
    }

    if (!PHONE_REGEX.test(text)) {
        issues.push(`${label} must be 8 to 15 digits and may start with +.`);
    }

    return issues;
};

export async function POST(request: Request) {
    try {
        const body = await request.json();
        const errors: Record<string, string[]> = {};
        const requiresAdminPhone = body?.createUser === true;

        const companyPhoneIssues = validatePhone(body?.Phone, true, "Phone number");
        if (companyPhoneIssues.length > 0) {
            errors.Phone = companyPhoneIssues;
        }

        if (requiresAdminPhone) {
            const adminPhoneIssues = validatePhone(body?.user_Phone, true, "Admin phone");
            if (adminPhoneIssues.length > 0) {
                errors.user_Phone = adminPhoneIssues;
            }
        }

        if (typeof body?.contactPersonPhone === "string" && body.contactPersonPhone.trim()) {
            const contactPhoneIssues = validatePhone(body.contactPersonPhone, false, "Contact person phone");
            if (contactPhoneIssues.length > 0) {
                errors.contactPersonPhone = contactPhoneIssues;
            }
        }

        if (Object.keys(errors).length > 0) {
            return NextResponse.json(
                {
                    message: "Please correct the highlighted fields and try again.",
                    errors
                },
                { status: 422 }
            );
        }

        const laravelEndpoint = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`;

        const response = await fetch(laravelEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
            body: JSON.stringify(body),
        });

        const data = await response.json();

        if (!response.ok) {
            const safeErrors = sanitizeFieldErrors(data?.errors);
            return NextResponse.json(
                {
                    message: Object.keys(safeErrors).length > 0
                        ? "Please correct the highlighted fields and try again."
                        : "We couldn't complete registration right now. Please try again.",
                    errors: safeErrors
                },
                { status: response.status }
            );
        }

        return NextResponse.json(data, { status: 200 });
    } catch (error: unknown) {
        console.error("[Register API] Failed to process registration request.", error);
        return NextResponse.json(
            { message: "We couldn't complete registration right now. Please try again." },
            { status: 500 }
        );
    }
}
