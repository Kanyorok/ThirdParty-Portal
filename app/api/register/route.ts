import { NextResponse } from "next/server"

import { sanitizeFieldErrors } from "@/app/api/portal/auth/_utils"

const PHONE_REGEX = /^\+?[0-9]{8,15}$/

const validatePhone = (value: unknown, required: boolean, label: string): string[] => {
    const text = typeof value === "string" ? value.trim() : ""
    const issues: string[] = []

    if (!text) {
        if (required) issues.push(`${label} is required.`)
        return issues
    }

    if (!PHONE_REGEX.test(text)) {
        issues.push(`${label} must be 8 to 15 digits and may start with +.`)
    }

    return issues
}

export async function POST(request: Request) {
    try {
        const contentType = request.headers.get("content-type") ?? "";
        const isMultipart = contentType.includes("multipart/form-data");
        const body = isMultipart ? await request.formData() : await request.json();
        const errors: Record<string, string[]> = {};
        const getValue = (key: string) => {
            if (isMultipart) {
                const value = (body as FormData).get(key);
                return typeof value === "string" ? value : "";
            }

            return body?.[key];
        };
        const requiresAdminPhone = isMultipart
            ? ["1", "true", "yes", "on"].includes(String(getValue("createUser") ?? "").trim().toLowerCase())
            : body?.createUser === true;

        const companyPhoneIssues = validatePhone(getValue("Phone"), true, "Phone number");
        if (companyPhoneIssues.length > 0) {
            errors.Phone = companyPhoneIssues;
        }

        if (requiresAdminPhone) {
            const adminPhoneIssues = validatePhone(getValue("user_Phone"), true, "Admin phone");
            if (adminPhoneIssues.length > 0) {
                errors.user_Phone = adminPhoneIssues;
            }
        }

        const contactPersonPhone = getValue("contactPersonPhone");
        if (typeof contactPersonPhone === "string" && contactPersonPhone.trim()) {
            const contactPhoneIssues = validatePhone(contactPersonPhone, false, "Contact person phone");
            if (contactPhoneIssues.length > 0) {
                errors.contactPersonPhone = contactPhoneIssues;
            }
        }

        if (Object.keys(errors).length > 0) {
            return NextResponse.json(
                {
                    message: "Please correct the highlighted fields and try again.",
                    errors,
                },
                { status: 422 }
            )
        }

        // Prefer NEXT_PUBLIC_API_URL, fall back to other env vars that may be present in production
        const baseApi = process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || ''
        const laravelEndpoint = `${baseApi.replace(/\/$/, '')}/api/v1/portal/auth/register`

        const response = await fetch(laravelEndpoint, {
            method: "POST",
            headers: isMultipart
                ? {
                    "Accept": "application/json",
                }
                : {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
            body: isMultipart ? (body as FormData) : JSON.stringify(body),
        })

        let data: any = null
        try {
            data = await response.json().catch(() => null)
        } catch (e) {
            console.error('[Register API] Failed to parse JSON from Laravel response', e)
        }

        if (!response.ok) {
            // Log status and body for debugging in production logs (no sensitive internals)
            console.error('[Register API] Laravel responded with', { status: response.status, body: data })
            const safeErrors = sanitizeFieldErrors(data?.errors)
            return NextResponse.json(
                {
                    message: Object.keys(safeErrors).length > 0
                        ? "Please correct the highlighted fields and try again."
                        : "We couldn't complete registration right now. Please try again.",
                    errors: safeErrors,
                },
                { status: response.status }
            )
        }

        return NextResponse.json(data, { status: response.status })
    } catch (error: unknown) {
        console.error("[Register API] Failed to process registration request.", error)
        return NextResponse.json(
            { message: "We couldn't complete registration right now. Please try again." },
            { status: 500 }
        )
    }
}
