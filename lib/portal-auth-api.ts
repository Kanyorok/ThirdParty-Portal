import { getBaseUrl } from "@/lib/api-base"

const CANDIDATE_BASE_URLS = [
    getBaseUrl,
    () => process.env.NEXT_PUBLIC_API_URL ?? "",
    () => process.env.NEXT_PUBLIC_EXTERNAL_API_URL ?? "",
    () => process.env.API_BASE_URL ?? "",
    () => process.env.EXTERNAL_API_URL ?? "",
    () => process.env.ERP_BASE_URL ?? "",
    () => process.env.NEXT_PUBLIC_ERP_BASE_URL ?? "",
]

export function resolvePortalAuthApiBaseUrl() {
    for (const resolve of CANDIDATE_BASE_URLS) {
        const value = String(resolve() ?? "").trim().replace(/\/+$/, "")
        if (value) {
            return value
        }
    }

    return ""
}

export async function fetchFirstAvailableJson(candidatePaths: string[]) {
    const apiBase = resolvePortalAuthApiBaseUrl()
    if (!apiBase) {
        return {
            apiBase,
            ok: false,
            status: 500,
            body: { message: "Registration service is not configured." } as unknown,
        }
    }

    let lastStatus = 500
    let lastBody: unknown = { message: "Upstream Error" }

    for (const path of candidatePaths) {
        const response = await fetch(`${apiBase}${path}`, {
            headers: { Accept: "application/json" },
            cache: "no-store",
        })

        const body = await response.json().catch(() => null)

        if (response.ok) {
            return {
                apiBase,
                ok: true,
                status: response.status,
                body,
            }
        }

        lastStatus = response.status
        lastBody = body ?? { message: "Upstream Error" }

        if (response.status !== 404) {
            break
        }
    }

    return {
        apiBase,
        ok: false,
        status: lastStatus,
        body: lastBody,
    }
}