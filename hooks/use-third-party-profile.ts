import useSWR from "swr"
import { ThirdPartyInputs, ThirdPartyProfile } from "@/types/third-party"
import { toast } from "sonner"

const fetcher = (url: string) => fetch(url, { cache: "no-store" }).then((res) => res.json())

export function useThirdPartyProfile() {
    const { data, mutate, isLoading } = useSWR<{ userProfile: ThirdPartyProfile }>("/api/third-party-profile", fetcher)

    const updateProfile = async (values: ThirdPartyInputs) => {
        if (!data?.userProfile) return
        return toast.promise(
            (async () => {
                const res = await fetch("/api/third-party-profile", {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(values),
                })
                if (!res.ok) throw new Error("Failed to update profile")
                const json = await res.json()
                mutate({ userProfile: json.userProfile }, { revalidate: true })
                return "Profile updated successfully"
            })(),
            { loading: "Saving...", success: (m) => m, error: (e) => String(e) }
        )
    }

    const createProfile = async (values: ThirdPartyInputs) => {
        return toast.promise(
            (async () => {
                const res = await fetch("/api/third-party-profile", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(values),
                })
                if (!res.ok) throw new Error("Failed to create profile")
                const json = await res.json()
                mutate({ userProfile: json.userProfile }, { revalidate: true })
                return "Profile created successfully"
            })(),
            { loading: "Creating...", success: (m) => m, error: (e) => String(e) }
        )
    }

    return { profile: data?.userProfile, createProfile, updateProfile, mutateProfile: mutate, isLoading }
}
