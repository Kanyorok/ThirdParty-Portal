type UserProfileLike = Record<string, unknown>

type SuspendAccountResponse = {
  message?: string
  status?: string
}

type UpdateProfileResponse = {
  success?: boolean
  message?: string
  error?: string
  data?: unknown
  user_profile?: unknown
  userProfile?: unknown
}

type PasswordPayload = {
  currentPassword: string
  newPassword: string
}

type UploadProfilePictureResponse = {
  imageUrl?: string
  message?: string
}

function getApiBaseUrl() {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL
  if (!baseUrl) throw new Error("NEXT_PUBLIC_API_URL is not configured")
  return baseUrl
}

async function parseJson<T>(res: Response): Promise<T | Record<string, unknown>> {
  const text = await res.text().catch(() => "")
  if (!text) return {}
  try {
    return JSON.parse(text) as T
  } catch {
    return { message: text }
  }
}

function authHeaders(accessToken?: string): HeadersInit {
  const headers: HeadersInit = {
    Accept: "application/json",
  }

  if (accessToken) {
    headers.Authorization = `Bearer ${accessToken}`
  }

  return headers
}

export const apiService = {
  async getProfile(accessToken?: string): Promise<UserProfileLike> {
    const res = await fetch(`${getApiBaseUrl()}/api/third-party-profile`, {
      method: "GET",
      headers: authHeaders(accessToken),
      cache: "no-store",
    })

    const body = (await parseJson<UpdateProfileResponse>(res)) as UpdateProfileResponse
    if (!res.ok) {
      throw new Error(body?.message || "Failed to fetch profile")
    }

    return (body?.user_profile || body?.userProfile || body?.data || body) as UserProfileLike
  },

  async changePassword(payload: PasswordPayload, accessToken?: string) {
    const res = await fetch(`${getApiBaseUrl()}/api/third-party-profile/password`, {
      method: "PUT",
      headers: {
        ...authHeaders(accessToken),
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        current_password: payload.currentPassword,
        new_password: payload.newPassword,
        new_password_confirmation: payload.newPassword,
      }),
      cache: "no-store",
    })

    const body = (await parseJson<{ message?: string }>(res)) as { message?: string }
    if (!res.ok) {
      throw new Error(body?.message || "Failed to change password")
    }

    return body
  },

  async uploadProfilePicture(file: File, accessToken?: string): Promise<UploadProfilePictureResponse> {
    const formData = new FormData()
    formData.append("image", file)

    const headers: HeadersInit = accessToken ? { Authorization: `Bearer ${accessToken}` } : {}
    const res = await fetch("/api/profile/image", {
      method: "POST",
      headers,
      body: formData,
      cache: "no-store",
    })

    const body = (await parseJson<any>(res)) as any
    if (!res.ok) {
      throw new Error(body?.message || "Failed to upload profile picture")
    }

    const imageUrl =
      body?.imageUrl ??
      body?.image_url ??
      body?.image ??
      body?.data?.imageUrl ??
      body?.data?.image_url ??
      body?.data?.image ??
      body?.data?.url

    return {
      imageUrl,
      message: body?.message,
    }
  },

  async suspendAccount(accessToken?: string): Promise<SuspendAccountResponse> {
    const headers = authHeaders(accessToken)

    const res = await fetch("/api/third-party-profile", {
      method: "DELETE",
      headers,
      cache: "no-store",
    })

    const body = (await parseJson<SuspendAccountResponse>(res)) as SuspendAccountResponse

    if (!res.ok) {
      throw new Error(body.message || "Failed to suspend account")
    }

    return body
  },

  // Backward-compatible alias for legacy consumers.
  async deleteAccount(_password: string, accessToken?: string): Promise<SuspendAccountResponse> {
    return this.suspendAccount(accessToken)
  },
}

export const profileService = {
  async updateProfile(target: "me" | number | string, payload: Record<string, unknown>, accessToken: string) {
    const baseUrl = getApiBaseUrl()

    const portalFields = [
      "ThirdPartyName",
      "TradingName",
      "BusinessType",
      "RegistrationNumber",
      "TaxPIN",
      "CountryId",
      "LocationId",
      "PhysicalAddress",
      "Website",
    ]
    const hasPortalPayload = portalFields.some((key) => Object.prototype.hasOwnProperty.call(payload, key))
    const endpoint = hasPortalPayload ? "/api/v1/profile" : "/api/third-party-profile"

    const body =
      target === "me" || target === "" || target == null || hasPortalPayload
        ? payload
        : { ...payload, third_party_id: target }

    const res = await fetch(`${baseUrl}${endpoint}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: `Bearer ${accessToken}`,
      },
      body: JSON.stringify(body),
      cache: "no-store",
    })

    const json = (await parseJson<UpdateProfileResponse>(res)) as UpdateProfileResponse
    if (!res.ok || json.success === false) {
      throw new Error(json.message || "Failed to update profile")
    }
    return json
  },
}
