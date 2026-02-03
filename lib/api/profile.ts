type DeleteAccountResponse = {
  success?: boolean
  message?: string
  error?: string
}

type UpdateProfileResponse = {
  success?: boolean
  message?: string
  error?: string
  data?: unknown
}

export const apiService = {
  async deleteAccount(password: string, accessToken: string): Promise<DeleteAccountResponse> {
    const baseUrl = process.env.NEXT_PUBLIC_API_URL
    const endpoint = process.env.NEXT_PUBLIC_API_URL

    if (!baseUrl || !endpoint) {
      throw new Error("Delete account endpoint is not configured")
    }

    const res = await fetch(`${baseUrl}${endpoint}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${accessToken}`,
      },
      body: JSON.stringify({ password }),
      cache: "no-store",
    })

    const body = (await res.json().catch(() => ({}))) as DeleteAccountResponse

    if (!res.ok || body.success === false) {
      throw new Error(body.message || "Account deletion failed")
    }

    return body
  },
}

export const profileService = {
  async updateProfile(target: "me" | number | string, payload: Record<string, unknown>, accessToken: string) {
    const baseUrl = process.env.NEXT_PUBLIC_API_URL
    if (!baseUrl) throw new Error("NEXT_PUBLIC_API_URL is not configured")

    const body =
      target === "me" || target === "" || target == null
        ? payload
        : { ...payload, third_party_id: target }

    const res = await fetch(`${baseUrl}/api/v1/portal/profile/update`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${accessToken}`,
      },
      body: JSON.stringify(body),
      cache: "no-store",
    })

    const json = (await res.json().catch(() => ({}))) as UpdateProfileResponse
    if (!res.ok || json.success === false) {
      throw new Error(json.message || "Failed to update profile")
    }
    return json
  },
}
