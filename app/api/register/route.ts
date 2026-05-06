import { NextResponse } from "next/server"

import { getBaseUrl } from "@/lib/api-base"

const BOOLEAN_FORM_KEYS = ["createUser", "create_user"] as const

const normalizeBooleanFormValue = (value: string) => {
    const normalized = value.trim().toLowerCase()
    if (["1", "true", "yes", "on"].includes(normalized)) return "1"
    if (["0", "false", "no", "off"].includes(normalized)) return "0"
    return value
}

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

const normalizeRegistrationFormData = (source: FormData) => {
    const next = cloneFormData(source)

    BOOLEAN_FORM_KEYS.forEach((key) => {
        const current = next.get(key)
        if (typeof current !== "string") return
        next.set(key, normalizeBooleanFormValue(current))
    })

    const createUserValue = next.get("createUser")
    const createUserSnakeValue = next.get("create_user")

    if (typeof createUserValue === "string" && !createUserSnakeValue) {
        next.set("create_user", createUserValue)
    }

    if (typeof createUserSnakeValue === "string" && !createUserValue) {
        next.set("createUser", createUserSnakeValue)
    }

    return next
}

const normalizeRegistrationJson = (rawBody: string) => {
    const parsed = JSON.parse(rawBody) as Record<string, unknown>

    if (parsed.createUser != null && parsed.create_user == null) {
        parsed.create_user = parsed.createUser
    }

    if (parsed.create_user != null && parsed.createUser == null) {
        parsed.createUser = parsed.create_user
    }

    return JSON.stringify(parsed)
}

const toProxyResponse = async (response: Response) => {
    const contentType = response.headers.get("content-type") ?? ""
    const rawBody = await response.text()

    if (contentType.includes("application/json")) {
        try {
            const data = rawBody ? JSON.parse(rawBody) : null
            return NextResponse.json(data ?? {}, { status: response.status })
        } catch {
            return new NextResponse(rawBody, {
                status: response.status,
                headers: { "content-type": contentType || "application/json" },
            })
        }
    }

    return new NextResponse(rawBody, {
        status: response.status,
        headers: { "content-type": contentType || "text/plain; charset=utf-8" },
    })
}

export async function POST(request: Request) {
    try {
        const baseApi = getBaseUrl()
        if (!baseApi) {
            return NextResponse.json({ message: "Registration service is not configured." }, { status: 500 })
        }

        const contentType = request.headers.get("content-type") ?? ""
        const isMultipart = contentType.includes("multipart/form-data")
        const upstreamUrl = `${baseApi.replace(/\/$/, "")}/api/v1/portal/auth/register`

        const upstreamBody: BodyInit = isMultipart
            ? normalizeRegistrationFormData(await request.formData())
            : normalizeRegistrationJson(await request.text())

        const response = await fetch(upstreamUrl, {
            method: "POST",
            headers: isMultipart
                ? { Accept: "application/json" }
                : {
                    Accept: "application/json",
                    "Content-Type": contentType || "application/json",
                },
            body: upstreamBody,
        })

        return await toProxyResponse(response)
    } catch (error: unknown) {
        console.error("[Register API] Failed to proxy registration request.", error)
        return NextResponse.json(
            { message: "We couldn't complete registration right now. Please try again." },
            { status: 500 }
        )
    }
}
