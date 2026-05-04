import { getBaseUrl } from "@/lib/api-base"

const CANDIDATE_BASE_URLS = [
    // Force use of EXTERNAL_API_URL for all backend requests
    () => process.env.EXTERNAL_API_URL ?? "",
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