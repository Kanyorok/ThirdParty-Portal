import { NextResponse } from "next/server"

import { sanitizeFieldErrors } from "@/app/api/portal/auth/_utils"
import { getBaseUrl } from "@/lib/api-base"
import { canonicalizeBusinessTypeValue, normalizePhoneNumber } from "@/lib/register-shared"

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

const REGISTRATION_ERROR_FIELD_MAP = Object.fromEntries(
    Object.entries(REGISTRATION_ALIASES).flatMap(([canonical, aliases]) => [
        [canonical, canonical],
        ...aliases.map((alias) => [alias, canonical] as const),
    ])
) as Record<string, string>

REGISTRATION_ERROR_FIELD_MAP.legalForm = "BusinessType"

const REGISTRATION_UPSTREAM_DUPLICATE_INDEX_PATTERNS: Array<{
    pattern: RegExp
    field: string
    message: string
}> = [
        {
            pattern: /t_thirdparties.*registrationnumber|registrationnumber.*t_thirdparties|countryid_registrationnumber/i,
            field: "RegistrationNumber",
            message: "This registration number is already registered.",
        },
        {
            pattern: /t_thirdparties.*taxpin|taxpin.*t_thirdparties/i,
            field: "TaxPIN",
            message: "This tax PIN is already registered.",
        },
        {
            pattern: /t_thirdparties.*vatnumber|vatnumber.*t_thirdparties/i,
            field: "VATNumber",
            message: "This VAT number is already registered.",
        },
        {
            pattern: /t_thirdparties.*email|email.*t_thirdparties/i,
            field: "Email",
            message: "This business email is already registered.",
        },
        {
            pattern: /t_thirdparties.*phone|phone.*t_thirdparties/i,
            field: "Phone",
            message: "This business phone number is already registered.",
        },
        {
            pattern: /t_thirdpartyusers.*email|email.*t_thirdpartyusers/i,
            field: "user_Email",
            message: "This admin email is already registered.",
        },
        {
            pattern: /t_thirdpartyusers.*phone|phone.*t_thirdpartyusers/i,
            field: "user_Phone",
            message: "This admin phone number is already registered.",
        },
    ]

const normalizeRegistrationErrors = (errors: unknown) => {
    if (!errors || typeof errors !== "object" || Array.isArray(errors)) return {}

    return Object.fromEntries(
        Object.entries(errors as Record<string, unknown>).map(([field, value]) => [
            REGISTRATION_ERROR_FIELD_MAP[field] ?? field,
            value,
        ])
    )
}

const inferRegistrationErrorsFromUpstreamMessage = (message: unknown) => {
    if (typeof message !== "string") return {} as Record<string, string[]>

    const normalizedMessage = message.replace(/\s+/g, " ").trim()
    if (!normalizedMessage) return {}

    for (const candidate of REGISTRATION_UPSTREAM_DUPLICATE_INDEX_PATTERNS) {
        if (candidate.pattern.test(normalizedMessage)) {
            return {
                [candidate.field]: [candidate.message],
            }
        }
    }

    if (/the phone field is required/i.test(normalizedMessage)) {
        return {
            Phone: ["Please enter a valid business phone number."],
        }
    }

    if (/the user[_\s-]*phone field is required/i.test(normalizedMessage)) {
        return {
            user_Phone: ["Please enter a valid admin phone number."],
        }
    }

    return {}
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
    const next = applyBusinessTypeFallbacks(
        applyRegistrationContactFallbacks(normalizeRegistrationPhoneFields({ ...payload }))
    )

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

const PHONE_FORM_KEYS = ["Phone", "phone", "user_Phone", "userPhone", "contactPersonPhone"] as const

const normalizeRegistrationPhoneFields = <T extends FormData | Record<string, unknown>>(source: T): T => {
    if (source instanceof FormData) {
        PHONE_FORM_KEYS.forEach((key) => {
            const current = source.get(key)
            if (typeof current !== "string") return

            source.set(key, normalizePhoneNumber(current) ?? current.trim())
        })

        return source
    }

    PHONE_FORM_KEYS.forEach((key) => {
        const current = source[key]
        if (typeof current !== "string") return

        source[key] = normalizePhoneNumber(current) ?? current.trim()
    })

    return source
}

const applyRegistrationContactFallbacks = <T extends FormData | Record<string, unknown>>(source: T): T => {
    if (source instanceof FormData) {
        const createUserValue = String(source.get("createUser") ?? source.get("create_user") ?? "").trim().toLowerCase()
        const usesSeparateUser = ["1", "true", "yes", "on"].includes(createUserValue)

        if (!usesSeparateUser) return source

        const phone = String(source.get("Phone") ?? "").trim()
        const userPhone = String(source.get("user_Phone") ?? source.get("userPhone") ?? "").trim()
        const email = String(source.get("Email") ?? "").trim()
        const userEmail = String(source.get("user_Email") ?? source.get("userEmail") ?? "").trim()

        if (!phone && userPhone) source.set("Phone", userPhone)
        if (!email && userEmail) source.set("Email", userEmail)

        return source
    }

    const createUserValue = source.createUser ?? source.create_user
    const usesSeparateUser = createUserValue === true || createUserValue === 1 || createUserValue === "1" || createUserValue === "true"

    if (!usesSeparateUser) return source

    const phone = typeof source.Phone === "string" ? source.Phone.trim() : ""
    const userPhone = typeof source.user_Phone === "string"
        ? source.user_Phone.trim()
        : typeof source.userPhone === "string"
            ? source.userPhone.trim()
            : ""
    const email = typeof source.Email === "string" ? source.Email.trim() : ""
    const userEmail = typeof source.user_Email === "string"
        ? source.user_Email.trim()
        : typeof source.userEmail === "string"
            ? source.userEmail.trim()
            : ""

    if (!phone && userPhone) source.Phone = userPhone
    if (!email && userEmail) source.Email = userEmail

    return source
}

const applyBusinessTypeFallbacks = <T extends FormData | Record<string, unknown>>(source: T): T => {
    if (source instanceof FormData) {
        const businessType = String(source.get("BusinessType") ?? source.get("businessType") ?? source.get("legalForm") ?? "").trim()
        const canonicalBusinessType = canonicalizeBusinessTypeValue(businessType)

        if (canonicalBusinessType) {
            source.set("BusinessType", canonicalBusinessType)

            const legalForm = String(source.get("legalForm") ?? "").trim()
            if (!legalForm) source.set("legalForm", canonicalBusinessType)
        }

        return source
    }

    const businessType = source.BusinessType ?? source.businessType ?? source.legalForm
    const canonicalBusinessType = canonicalizeBusinessTypeValue(businessType)

    if (canonicalBusinessType) {
        source.BusinessType = canonicalBusinessType
        if (source.legalForm == null || source.legalForm === "") {
            source.legalForm = canonicalBusinessType
        }
    }

    return source
}

const withRegistrationAliasesFormData = (source: FormData) => {
    const next = applyBusinessTypeFallbacks(
        applyRegistrationContactFallbacks(normalizeRegistrationPhoneFields(normalizeRegistrationFormData(source)))
    )

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
    const text = normalizePhoneNumber(value) ?? (typeof value === "string" ? value.trim() : "")
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

        const companyPhoneIssues = validatePhone(getValue("Phone"), !requiresAdminPhone, "Phone number");
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
            const safeErrors = {
                ...inferRegistrationErrorsFromUpstreamMessage(data?.message),
                ...sanitizeFieldErrors(normalizeRegistrationErrors(data?.errors)),
            }
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
