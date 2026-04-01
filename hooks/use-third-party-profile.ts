import useSWR from "swr"
import { ThirdPartyInputs, ThirdPartyProfile } from "@/types/third-party"
import { toast } from "sonner"

type ValidationErrors = Record<string, string[]>

type ThirdPartyProfileResponse = {
    message?: string
    user_profile?: ThirdPartyProfile
    userProfile?: ThirdPartyProfile
    errors?: ValidationErrors
    status?: string
    roles?: Array<{ id: number; label: string; isActive: boolean }>
}

function pickProfile(payload: ThirdPartyProfileResponse | null): ThirdPartyProfile | undefined {
    if (!payload) return undefined
    return payload.user_profile ?? payload.userProfile
}

async function parseResponse(res: Response): Promise<ThirdPartyProfileResponse> {
    const text = await res.text()
    if (!text) return {}
    try {
        return JSON.parse(text) as ThirdPartyProfileResponse
    } catch {
        return { message: text }
    }
}

function buildApiError(payload: ThirdPartyProfileResponse, fallback: string) {
    const fieldMessages = payload.errors
        ? Object.values(payload.errors)
            .flat()
            .filter(Boolean)
            .join(" ")
        : ""
    const message = [payload.message || fallback, fieldMessages].filter(Boolean).join(" ").trim()
    return new Error(message || fallback)
}

const fetcher = async (url: string) => {
    const res = await fetch(url, { cache: "no-store" })
    const payload = await parseResponse(res)
    if (!res.ok) throw buildApiError(payload, "Failed to load profile")
    return payload
}

async function requestProfile(method: "PUT" | "PATCH" | "DELETE", values?: ThirdPartyInputs) {
    const res = await fetch("/api/third-party-profile", {
        method,
        headers: values ? { "Content-Type": "application/json" } : undefined,
        body: values ? JSON.stringify(values) : undefined,
    })
    const payload = await parseResponse(res)
    if (!res.ok) {
        throw buildApiError(payload, method === "DELETE" ? "Failed to suspend profile" : "Failed to update profile")
    }
    return payload
}

export function useThirdPartyProfile() {
    const { data, mutate, isLoading, error } = useSWR<ThirdPartyProfileResponse>("/api/third-party-profile", fetcher)

    const updateProfile = async (values: ThirdPartyInputs) => {
        return toast.promise(
            (async () => {
                const payload = await requestProfile("PUT", values)
                const profile = pickProfile(payload)
                if (profile) {
                    mutate({ ...payload, user_profile: profile }, { revalidate: true })
                }
                return payload.message || "Profile updated successfully"
            })(),
            { loading: "Saving profile", success: (m) => m, error: (e) => String(e) },
        )
    }

    const patchProfile = async (values: ThirdPartyInputs) => {
        return toast.promise(
            (async () => {
                const payload = await requestProfile("PATCH", values)
                const profile = pickProfile(payload)
                if (profile) {
                    mutate({ ...payload, user_profile: profile }, { revalidate: true })
                }
                return payload.message || "Profile updated successfully"
            })(),
            { loading: "Saving profile", success: (m) => m, error: (e) => String(e) },
        )
    }

    const createProfile = async (values: ThirdPartyInputs) => {
        return updateProfile(values)
    }

    const suspendProfile = async () => {
        return toast.promise(
            (async () => {
                const payload = await requestProfile("DELETE")
                return payload.message || "Profile suspended successfully."
            })(),
            { loading: "Suspending profile...", success: (m) => m, error: (e) => String(e) },
        )
    }

    const toggleRole = async (roleId: number, enable: boolean) => {
        return toast.promise(
            (async () => {
                const res = await fetch("/api/third-party-profile/roles/toggle", {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ roleId, enable }),
                })
                const payload = await parseResponse(res)
                if (!res.ok) throw buildApiError(payload, "Failed to toggle role")
                return payload.message || "Role updated successfully."
            })(),
            { loading: "Updating role...", success: (m) => m, error: (e) => String(e) },
        )
    }

    return {
        profile: pickProfile(data ?? null),
        createProfile,
        updateProfile,
        patchProfile,
        suspendProfile,
        toggleRole,
        mutateProfile: mutate,
        isLoading,
        error,
    }
}
