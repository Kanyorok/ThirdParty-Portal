import { NextResponse } from "next/server"

import { sanitizeFieldErrors } from "@/app/api/portal/auth/_utils"
import { getBaseUrl } from "@/lib/api-base"

const PHONE_REGEX = /^\+?[0-9]{8,15}$/
const BOOLEAN_FORM_KEYS = ["createUser", "create_user"] as const

const cloneFormData = (source: FormData) => {
    const next = new FormData()

    source.forEach((value, key) => {
        if (typeof value === "string") {
            next.append(key, value)
            return
        }

        next.append(key, value, value.name)
    })

    return next
}

const normalizeBooleanFormValue = (value: string) => {
    const normalized = value.trim().toLowerCase()
    if (["1", "true", "yes", "on"].includes(normalized)) return "1"
    if (["0", "false", "no", "off"].includes(normalized)) return "0"
    return value
}

const normalizeRegistrationFormData = (source: FormData) => {
    const next = cloneFormData(source)
    const createUserValue = next.get("createUser")

    if (typeof createUserValue === "string") {
        next.set("create_user", normalizeBooleanFormValue(createUserValue))
        next.delete("createUser")
    }

    BOOLEAN_FORM_KEYS.forEach((key) => {
        const current = next.get(key)
        if (typeof current !== "string") return
        next.set(key, normalizeBooleanFormValue(current))
    })

    return next
}

const REGISTRATION_ALIASES: Record<string, string[]> = {
    Name: ["name"],
    TradingName: ["tradingName"],
    BusinessType: ["businessType"],
    RegistrationNumber: ["registrationNumber"],
    TaxPIN: ["taxPin"],
    VATNumber: ["vatNumber"],
    Email: ["email"],
    Phone: ["phone"],
    PhysicalAddress: ["physicalAddress"],
    Website: ["website"],
    Country: ["country"],
    Location: ["location"],
    supplier_category_id: ["supplierCategoryId"],
    user_FirstName: ["userFirstName"],
    user_LastName: ["userLastName"],
    user_Email: ["userEmail"],
    user_Phone: ["userPhone"],
    user_Gender: ["userGender"],
    user_Password: ["userPassword"],
    user_Password_confirmation: ["userPasswordConfirmation"],
}

const SENSITIVE_REGISTRATION_KEYS = new Set([
    "Email",
    "email",
    "user_Email",
    "userEmail",
    "user_Password",
    "userPassword",
    "user_Password_confirmation",
    "userPasswordConfirmation",
    "RegistrationNumber",
    "registrationNumber",
    "TaxPIN",
    "taxPin",
    "VATNumber",
    "vatNumber",
    "contactPersonEmail",
])

const toSafeScalarPreview = (value: string) => {
    const normalized = value.trim()
    if (!normalized) return ""
    if (normalized.length <= 80) return normalized
    return `${normalized.slice(0, 77)}...`
}

const summarizeRegistrationFormData = (source: FormData) => {
    const fields: Record<string, string | string[]> = {}
    const files: Record<string, Array<{ name: string; size: number; type: string }>> = {}

    source.forEach((value, key) => {
        if (typeof value === "string") {
            const preview = SENSITIVE_REGISTRATION_KEYS.has(key) ? "[REDACTED]" : toSafeScalarPreview(value)
            const existing = fields[key]
            if (existing == null) {
                fields[key] = preview
            } else if (Array.isArray(existing)) {
                existing.push(preview)
            } else {
                fields[key] = [existing, preview]
            }
            return
        }

        if (!files[key]) files[key] = []
        files[key].push({
            name: value.name,
            size: value.size,
            type: value.type,
        })
    })

    return {
        fieldKeys: Object.keys(fields).sort(),
        fields,
        fileKeys: Object.keys(files).sort(),
        files,
    }
}

const summarizeRegistrationJson = (source: Record<string, unknown>) => {
    const fields: Record<string, unknown> = {}

    Object.entries(source).forEach(([key, value]) => {
        if (value == null) return

        if (Array.isArray(value)) {
            fields[key] = value
            return
        }

        if (typeof value === "string") {
            fields[key] = SENSITIVE_REGISTRATION_KEYS.has(key) ? "[REDACTED]" : toSafeScalarPreview(value)
            return
        }

        if (typeof value === "number" || typeof value === "boolean") {
            fields[key] = value
            return
        }

        if (typeof value === "object") {
            fields[key] = "[OBJECT]"
        }
    })

    return {
        fieldKeys: Object.keys(fields).sort(),
        fields,
    }
}

const withRegistrationAliases = (payload: Record<string, unknown>) => {
    const next = { ...payload }

    if (next.createUser != null && next.create_user == null) {
        next.create_user = next.createUser
    }
    delete next.createUser

    Object.entries(REGISTRATION_ALIASES).forEach(([sourceKey, aliases]) => {
        const value = next[sourceKey]
        if (value == null || value === "") return

        aliases.forEach((alias) => {
            if (next[alias] == null || next[alias] === "") {
                next[alias] = value
            }
        })
    })

    return next
}

const appendFormValue = (target: FormData, key: string, value: unknown) => {
    if (value == null || value === "") return

    if (value instanceof File) {
        target.append(key, value, value.name)
        return
    }

    if (Array.isArray(value)) {
        value.forEach((entry) => appendFormValue(target, key, entry))
        return
    }

    target.append(key, String(value))
}

const withRegistrationAliasesFormData = (source: FormData) => {
    const next = normalizeRegistrationFormData(source)

    Object.entries(REGISTRATION_ALIASES).forEach(([sourceKey, aliases]) => {
        const value = next.getAll(sourceKey)
        if (value.length === 0) return

        aliases.forEach((alias) => {
            if (next.has(alias)) return
            value.forEach((entry) => appendFormValue(next, alias, entry))
        })
    })

    return next
}

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

const validateLocation = (value: unknown): string[] => {
    const text = typeof value === "string" ? value.trim() : String(value ?? "").trim()
    const issues: string[] = []

    if (!text) {
        issues.push("Location is required.")
        return issues
    }

    const parsed = Number(text)
    if (!Number.isInteger(parsed) || parsed <= 0) {
        issues.push("Location must be a valid selection.")
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

        const locationIssues = validateLocation(getValue("Location"))
        if (locationIssues.length > 0) {
            errors.Location = locationIssues
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

        const baseApi = getBaseUrl()
        const laravelEndpoint = `${baseApi.replace(/\/$/, '')}/api/v1/portal/auth/register`

        const upstreamBody = isMultipart
            ? withRegistrationAliasesFormData(body as FormData)
            : withRegistrationAliases(body as Record<string, unknown>)
        const upstreamRequestBody: BodyInit = isMultipart
            ? (upstreamBody as FormData)
            : JSON.stringify(upstreamBody as Record<string, unknown>)
        const upstreamPayloadSummary = isMultipart
            ? summarizeRegistrationFormData(upstreamBody as FormData)
            : summarizeRegistrationJson(upstreamBody as Record<string, unknown>)

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
            body: upstreamRequestBody,
        })

        let data: any = null
        try {
            data = await response.json().catch(() => null)
        } catch (e) {
            console.error('[Register API] Failed to parse JSON from Laravel response', e)
        }

        if (!response.ok) {
            // Log status and body for debugging in production logs (no sensitive internals)
            console.error('[Register API] Laravel responded with', {
                status: response.status,
                body: data,
                forwardedPayload: upstreamPayloadSummary,
                transport: isMultipart ? 'multipart' : 'json',
            })
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
