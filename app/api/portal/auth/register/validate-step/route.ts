import { NextResponse } from "next/server"

import { sanitizeFieldErrors } from "@/app/api/portal/auth/_utils"
import { getBaseUrl } from "@/lib/api-base"
import { normalizePhoneNumber } from "@/lib/register-shared"

const STEP_META_KEYS = new Set(["step", "fields", "createUser", "create_user", "types"])

const BOOLEAN_FORM_KEYS = ["createUser", "create_user"] as const

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

const normalizeRegistrationErrors = (errors: unknown) => {
    if (!errors || typeof errors !== "object" || Array.isArray(errors)) return {}

    return Object.fromEntries(
        Object.entries(errors as Record<string, unknown>).map(([field, value]) => [
            REGISTRATION_ERROR_FIELD_MAP[field] ?? field,
            value,
        ])
    )
}

const PHONE_FORM_KEYS = ["Phone", "phone", "user_Phone", "userPhone", "contactPersonPhone"] as const

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

const normalizeBooleanFormValue = (value: string) => {
    const normalized = value.trim().toLowerCase()
    if (["1", "true", "yes", "on"].includes(normalized)) return "1"
    if (["0", "false", "no", "off"].includes(normalized)) return "0"
    return value
}

const normalizePhoneFields = <T extends FormData | Record<string, unknown>>(source: T): T => {
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

    return normalizePhoneFields(next)
}

const withRegistrationAliases = (payload: Record<string, unknown>) => {
    const next = normalizePhoneFields({ ...payload })

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

const toAliasSet = (fields: string[]) => {
    const allowed = new Set(fields)

    fields.forEach((field) => {
        REGISTRATION_ALIASES[field]?.forEach((alias) => allowed.add(alias))
        if (field === "BusinessType") allowed.add("legalForm")
    })

    STEP_META_KEYS.forEach((key) => allowed.add(key))

    return allowed
}

const filterErrorsForStep = (errors: unknown, fields: string[]) => {
    if (!errors || typeof errors !== "object" || Array.isArray(errors)) return {}

    const allowed = toAliasSet(fields)

    return Object.fromEntries(
        Object.entries(errors as Record<string, unknown>).filter(([field]) => allowed.has(field))
    )
}

const hasStepRelevantErrors = (errors: Record<string, unknown>) => Object.keys(errors).length > 0

export async function POST(request: Request) {
    try {
        const apiBase = getBaseUrl()
        if (!apiBase) {
            return NextResponse.json({ message: "Registration service is not configured." }, { status: 500 })
        }

        const contentType = request.headers.get("content-type") ?? ""
        const isMultipart = contentType.includes("multipart/form-data")
        const upstreamUrl = `${apiBase.replace(/\/$/, "")}/api/v1/portal/auth/register/validate-step`
        let stepFields: string[] = []
        let upstreamRequestBody: BodyInit

        if (isMultipart) {
            const incomingBody = await request.formData()
            stepFields = incomingBody.getAll("fields").filter((value: FormDataEntryValue): value is string => typeof value === "string")
            upstreamRequestBody = withRegistrationAliasesFormData(incomingBody)
        } else {
            const incomingBody = await request.json() as Record<string, unknown>
            stepFields = Array.isArray(incomingBody.fields)
                ? incomingBody.fields.filter((value: unknown): value is string => typeof value === "string")
                : []
            upstreamRequestBody = JSON.stringify(withRegistrationAliases(incomingBody))
        }

        const response = await fetch(upstreamUrl, {
            method: "POST",
            headers: isMultipart ? { Accept: "application/json" } : { Accept: "application/json", "Content-Type": "application/json" },
            body: upstreamRequestBody,
        })

        const body = await response.json().catch(() => null)

        if (!response.ok) {
            const stepErrors = filterErrorsForStep(normalizeRegistrationErrors(body?.errors), stepFields)
            if (!hasStepRelevantErrors(stepErrors)) {
                return NextResponse.json({ success: true, ignoredErrors: true }, { status: 200 })
            }

            const safeErrors = sanitizeFieldErrors(stepErrors)
            return NextResponse.json(
                {
                    message: body?.message ?? "Validation failed.",
                    errors: safeErrors,
                },
                { status: response.status }
            )
        }

        return NextResponse.json(body ?? { success: true }, { status: response.status })
    } catch {
        return NextResponse.json({ message: "Unable to validate registration step right now." }, { status: 500 })
    }
}