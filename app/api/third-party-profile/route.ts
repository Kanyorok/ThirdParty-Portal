import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

function getBaseApiUrl() {
    return process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
}

async function parseBody(res: Response) {
    const text = await res.text()
    if (!text) return null
    try {
        return JSON.parse(text)
    } catch {
        return { message: text }
    }
}

function asRecord(value: unknown): Record<string, unknown> | null {
    if (!value || typeof value !== "object" || Array.isArray(value)) return null
    return value as Record<string, unknown>
}

function asArray(value: unknown): unknown[] {
    return Array.isArray(value) ? value : []
}

function asPositiveInteger(value: unknown): number | null {
    if (typeof value === "number" && Number.isInteger(value) && value > 0) return value
    if (typeof value === "string") {
        const parsed = Number(value)
        if (Number.isInteger(parsed) && parsed > 0) return parsed
    }
    return null
}

function asBoolean(value: unknown): boolean | null {
    if (typeof value === "boolean") return value
    if (typeof value === "number") {
        if (value === 1) return true
        if (value === 0) return false
        return null
    }
    if (typeof value !== "string") return null
    const normalized = value.trim().toLowerCase()
    if (!normalized) return null
    if (["1", "true", "active", "enabled", "yes", "on"].includes(normalized)) return true
    if (["0", "false", "inactive", "disabled", "no", "off"].includes(normalized)) return false
    return null
}

function getNested(value: unknown, path: string[]): unknown {
    let current: unknown = value
    for (const key of path) {
        const record = asRecord(current)
        if (!record) return undefined
        current = record[key]
    }
    return current
}

function extractRoleEntries(payload: unknown) {
    const roleMap = new Map<number, boolean | null>()
    const candidatePaths = [
        ["roles"],
        ["profiles"],
        ["availableProfiles"],
        ["data", "roles"],
        ["data", "profiles"],
        ["data", "availableProfiles"],
        ["user_profile", "roles"],
        ["userProfile", "roles"],
        ["user_profile", "thirdParty", "types"],
        ["user_profile", "third_party", "types"],
        ["userProfile", "thirdParty", "types"],
        ["userProfile", "third_party", "types"],
        ["thirdParty", "types"],
        ["third_party", "types"],
        ["data", "thirdParty", "types"],
        ["data", "third_party", "types"],
    ]

    const addRole = (id: number, active: boolean | null) => {
        if (!roleMap.has(id)) {
            roleMap.set(id, active)
            return
        }
        const existing = roleMap.get(id)
        if (existing === false || active === false) {
            roleMap.set(id, false)
            return
        }
        if (existing === null && active !== null) {
            roleMap.set(id, active)
        }
    }

    const hasRoleMarker = (record: Record<string, unknown>) => {
        const code = typeof record.code === "string" ? record.code.trim().toUpperCase() : ""
        const label = typeof record.label === "string" ? record.label.trim().toUpperCase() : ""
        const role = typeof record.role === "string" ? record.role.trim().toUpperCase() : ""
        const type = typeof record.type === "string" ? record.type.trim().toUpperCase() : ""
        const markerValues = [code, label, role, type]
        return markerValues.some((value) =>
            ["SU", "SUPPLIER", "TN", "TENANT", "CU", "CS", "CUSTOMER"].includes(value)
        )
    }

    const hasActivationFlag = (record: Record<string, unknown>) =>
        [
            "isActive",
            "is_active",
            "active",
            "enabled",
            "enable",
            "status",
            "hasProfile",
            "has_profile",
        ].some((key) => Object.prototype.hasOwnProperty.call(record, key))

    for (const path of candidatePaths) {
        const nodes = asArray(getNested(payload, path))
        for (const node of nodes) {
            const record = asRecord(node)
            if (!record) continue

            const explicitRoleId =
                asPositiveInteger(record.roleId) ??
                asPositiveInteger(record.role_id) ??
                asPositiveInteger(record.roleID) ??
                asPositiveInteger(record.RoleID)

            const fallbackId = asPositiveInteger(record.id)
            const roleId = explicitRoleId ?? fallbackId
            if (!roleId) continue

            if (!explicitRoleId && !hasActivationFlag(record) && !hasRoleMarker(record)) continue

            const active =
                asBoolean(record.isActive) ??
                asBoolean(record.is_active) ??
                asBoolean(record.active) ??
                asBoolean(record.enabled) ??
                asBoolean(record.enable) ??
                asBoolean(record.status)

            addRole(roleId, active)
        }
    }

    return Array.from(roleMap.entries()).map(([roleId, isActive]) => ({ roleId, isActive }))
}

function normalizeProfileResponse(body: unknown) {
    if (!body || typeof body !== "object" || Array.isArray(body)) {
        return { user_profile: body }
    }

    const bodyRecord = body as Record<string, unknown>
    const userProfile = bodyRecord.user_profile ?? bodyRecord.userProfile ?? bodyRecord.data ?? bodyRecord
    const normalized: Record<string, unknown> = { user_profile: userProfile }
    if (typeof bodyRecord.message === "string" && bodyRecord.message.trim().length > 0) {
        normalized.message = bodyRecord.message
    }
    return normalized
}

function getResponseMessage(body: unknown): string | null {
    const record = asRecord(body)
    if (!record) return null
    const message = record.message
    if (typeof message !== "string") return null
    const trimmed = message.trim()
    return trimmed.length > 0 ? trimmed : null
}

function isAlreadyInactiveResponse(status: number, body: unknown) {
    if (status !== 422 && status !== 409 && status !== 400) return false
    const message = getResponseMessage(body)
    if (!message) return false
    const normalized = message.toLowerCase()
    return normalized.includes("already") && (normalized.includes("inactive") || normalized.includes("disabled"))
}

async function deactivateAssociatedProfiles(accessToken: string) {
    const baseUrl = getBaseApiUrl()
    const profileResponse = await fetch(`${baseUrl}/api/third-party-profile`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            Authorization: `Bearer ${accessToken}`,
        },
        cache: "no-store",
    })

    const profileBody = await parseBody(profileResponse)
    if (!profileResponse.ok) {
        const message = getResponseMessage(profileBody) ?? "Failed to fetch associated profiles."
        throw new Error(message)
    }

    const roles = extractRoleEntries(profileBody)
    if (roles.length === 0) {
        return { deactivatedCount: 0 }
    }

    const failures: string[] = []
    let deactivatedCount = 0

    for (const role of roles) {
        if (role.isActive === false) continue

        const response = await fetch(`${baseUrl}/api/third-party-profile/roles/toggle`, {
            method: "PUT",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ roleId: role.roleId, enable: false }),
            cache: "no-store",
        })

        const body = await parseBody(response)
        if (response.ok || isAlreadyInactiveResponse(response.status, body)) {
            deactivatedCount += 1
            continue
        }

        const message = getResponseMessage(body) ?? "Failed to deactivate profile."
        failures.push(`Role ${role.roleId}: ${message}`)
    }

    if (failures.length > 0) {
        throw new Error(failures.join(" "))
    }

    return { deactivatedCount }
}

async function proxy(request: NextRequest, method: "GET" | "PUT" | "PATCH") {
    const session = await getServerSession(authOptions)
    const accessToken = (session as any)?.accessToken as string | undefined

    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    try {
        const init: RequestInit = {
            method,
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
            cache: "no-store",
        }

        if (method === "PUT" || method === "PATCH") {
            const body = await request.json().catch(() => null)
            init.headers = { ...init.headers, "Content-Type": "application/json" }
            init.body = JSON.stringify(body)
        }

        const res = await fetch(`${getBaseApiUrl()}/api/third-party-profile`, init)
        const body = await parseBody(res)

        if (!res.ok) {
            return NextResponse.json(body ?? { message: "External API error" }, { status: res.status })
        }

        return NextResponse.json(normalizeProfileResponse(body), { status: res.status })
    } catch (error) {
        console.error("[Third Party Profile API] Error:", error)
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}

export async function GET(request: NextRequest) {
    return proxy(request, "GET")
}

export async function PUT(request: NextRequest) {
    return proxy(request, "PUT")
}

export async function PATCH(request: NextRequest) {
    return proxy(request, "PATCH")
}

export async function DELETE(_request: NextRequest) {
    const session = await getServerSession(authOptions)
    const accessToken = (session as any)?.accessToken as string | undefined

    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    try {
        const requestBody = await _request.json().catch(() => null)
        const normalizedPassword =
            requestBody?.current_password ??
            requestBody?.password ??
            requestBody?.confirm_password ??
            requestBody?.confirmPassword ??
            null
        const payload =
            normalizedPassword && typeof normalizedPassword === "string"
                ? {
                    current_password: normalizedPassword,
                    password: normalizedPassword,
                    confirm_password: normalizedPassword,
                }
                : null

        const roleUpdateResult = await deactivateAssociatedProfiles(accessToken)

        const executeDelete = async (includeBody: boolean) => {
            const headers: Record<string, string> = {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            }
            const init: RequestInit = {
                method: "DELETE",
                headers,
            }

            if (includeBody && payload) {
                headers["Content-Type"] = "application/json"
                init.body = JSON.stringify(payload)
            }

            const res = await fetch(`${getBaseApiUrl()}/api/third-party-profile`, init)
            const body = await parseBody(res)
            return { res, body }
        }

        let result = await executeDelete(Boolean(payload))
        if (!result.res.ok && payload && [400, 404, 405, 415, 422].includes(result.res.status)) {
            result = await executeDelete(false)
        }

        if (!result.res.ok) {
            return NextResponse.json(result.body ?? { message: "Action failed." }, { status: result.res.status })
        }

        const normalizedSuccessMessage =
            roleUpdateResult.deactivatedCount > 0
                ? "Account deactivated temporarily. Associated profiles were set to inactive."
                : "Account deactivated temporarily."

        if (result.body && typeof result.body === "object" && !Array.isArray(result.body)) {
            return NextResponse.json(
                {
                    ...(result.body as Record<string, unknown>),
                    message: normalizedSuccessMessage,
                    status: "deactivated",
                },
                { status: result.res.status }
            )
        }
        return NextResponse.json(
            { message: normalizedSuccessMessage, status: "deactivated" },
            { status: 200 }
        )
    } catch (error) {
        console.error("[Third Party Profile API] Delete error:", error)
        const message =
            error instanceof Error && error.message.trim().length > 0
                ? error.message
                : "Failed to deactivate account and associated profiles."
        return NextResponse.json({ message }, { status: 500 })
    }
}
