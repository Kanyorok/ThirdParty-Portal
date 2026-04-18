import { NextResponse } from "next/server"

import { sanitizeFieldErrors } from "@/app/api/portal/auth/_utils"
import { getBaseUrl } from "@/lib/api-base"

export async function POST(request: Request) {
    try {
        const apiBase = getBaseUrl()
        if (!apiBase) {
            return NextResponse.json({ message: "Registration service is not configured." }, { status: 500 })
        }

        const contentType = request.headers.get("content-type") ?? ""
        const isMultipart = contentType.includes("multipart/form-data")
        const upstreamUrl = `${apiBase.replace(/\/$/, "")}/api/v1/portal/auth/register/validate-step`

        const response = await fetch(upstreamUrl, {
            method: "POST",
            headers: isMultipart ? { Accept: "application/json" } : { Accept: "application/json", "Content-Type": "application/json" },
            body: isMultipart ? await request.formData() : JSON.stringify(await request.json()),
        })

        const body = await response.json().catch(() => null)

        if (!response.ok) {
            const safeErrors = sanitizeFieldErrors(body?.errors)
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