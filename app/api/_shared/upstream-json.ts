import { NextResponse } from "next/server"

import { getBaseUrl } from "@/lib/api-base"

type UpstreamJsonResult = {
    ok: boolean
    status: number
    body: unknown
    missingBase: boolean
}

export function resolveApiBaseUrl() {
    return String(getBaseUrl()).trim().replace(/\/+$/, "")
}

export async function fetchUpstreamJson(path: string, init?: RequestInit): Promise<UpstreamJsonResult> {
    const apiBase = resolveApiBaseUrl()
    if (!apiBase) {
        return {
            ok: false,
            status: 500,
            body: { message: "Upstream Error" },
            missingBase: true,
        }
    }

    const response = await fetch(`${apiBase}${path}`, init)
    const body = await response.json().catch(() => null)

    return {
        ok: response.ok,
        status: response.status,
        body,
        missingBase: false,
    }
}

export function toUpstreamErrorResponse(body: unknown, status: number, fallbackBody: unknown) {
    return NextResponse.json(body ?? fallbackBody, { status })
}

export function toServerErrorResponse(fallbackBody: unknown) {
    return NextResponse.json(fallbackBody, { status: 500 })
}
