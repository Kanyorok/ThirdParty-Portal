import useSWR from "swr"
import { ThirdPartyInputs, ThirdPartyProfile } from "@/types/third-party"
import { toast } from "sonner"

const fetcher = (url: string) => fetch(url, { cache: "no-store" }).then((res) => res.json())

export function useThirdPartyProfile() {
    const { data, mutate, isLoading } = useSWR<{ data: ThirdPartyProfile }>("/api/third-party-details", fetcher)

    const updateProfile = async (values: ThirdPartyInputs) => {
        if (!data?.data) return
        toast.promise(
            (async () => {
                const res = await fetch("/api/third-party-details", {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(values),
                })
                if (!res.ok) throw new Error("Failed to update profile")
                const json = await res.json()
                mutate({ data: json.data }, { revalidate: true })
                return "Profile updated successfully"
            })(),
            { loading: "Saving...", success: (m) => m, error: (e) => String(e) }
        )
    }

    const createProfile = async (values: ThirdPartyInputs) => {
        const res = await fetch("/api/third-party-details", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(values),
        })
        const created = await res.json()
        mutate(created, { revalidate: true })
    }

    return { profile: data?.data, createProfile, updateProfile, mutateProfile: mutate, isLoading }
}
