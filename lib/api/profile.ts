type UserProfileLike = Record<string, unknown>

type DeactivateAccountResponse = {
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

import { getBaseUrl, apiFetch } from "../api-base"

function getApiBaseUrl() {
  // prefer runtime or build-time configured base; if absent return empty so
  // apiFetch will use relative paths
  return getBaseUrl()
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

function defaultHeaders(): HeadersInit {
  return { Accept: "application/json" }
}

export const apiService = {
  async getProfile(): Promise<UserProfileLike> {
    const body = await apiFetch<UpdateProfileResponse>(`/api/third-party-profile`, { allowError: true })
    if (!body) throw new Error("Failed to fetch profile")
    return (body?.user_profile || body?.userProfile || body?.data || body) as UserProfileLike
  },

  async changePassword(payload: PasswordPayload) {
    const result = await apiFetch(`/api/third-party-profile/password`, {
      method: "PUT",
      headers: {
        ...defaultHeaders(),
        "Content-Type": "application/json",
      },
      credentials: "same-origin",
      body: JSON.stringify({
        current_password: payload.currentPassword,
        new_password: payload.newPassword,
        new_password_confirmation: payload.newPassword,
      }),
      cache: "no-store",
      allowError: true,
    })

    if (!result) throw new Error("Failed to change password")
    return result
  },

  async uploadProfilePicture(file: File): Promise<UploadProfilePictureResponse> {
    const formData = new FormData()
    formData.append("image", file)

    const res = await fetch("/api/v1/profile/user-image", {
      method: "POST",
      credentials: "same-origin",
      body: formData,
      cache: "no-store",
    })

    const body = (await parseJson<any>(res)) as any
    if (!res.ok) {
      throw new Error(body?.message || "Failed to upload profile picture")
    }

    const imageUrl =
      body?.data?.image?.src ??
      body?.data?.imageUrl ??
      body?.data?.image_url ??
      body?.data?.image ??
      body?.image?.src ??
      body?.imageUrl ??
      body?.image_url ??
      body?.image ??
      body?.data?.url

    return {
      imageUrl,
      message: body?.message,
    }
  },

  async suspendAccount(): Promise<DeactivateAccountResponse> {
    const res = await fetch("/api/third-party-profile", {
      method: "DELETE",
      headers: defaultHeaders(),
      credentials: "same-origin",
      cache: "no-store",
    })

    const body = (await parseJson<DeactivateAccountResponse>(res)) as DeactivateAccountResponse

    if (!res.ok) {
      throw new Error(body.message || "Failed to deactivate account")
    }

    return body
  },

  async deactivateAccount(password: string): Promise<DeactivateAccountResponse> {
    const trimmedPassword = String(password ?? "").trim()
    if (!trimmedPassword) {
      throw new Error("Password is required to deactivate account")
    }

    const res = await fetch("/api/third-party-profile", {
      method: "DELETE",
      headers: {
        ...defaultHeaders(),
        "Content-Type": "application/json",
      },
      credentials: "same-origin",
      body: JSON.stringify({
        password: trimmedPassword,
        current_password: trimmedPassword,
        confirm_password: trimmedPassword,
      }),
      cache: "no-store",
    })

    const body = (await parseJson<DeactivateAccountResponse>(res)) as DeactivateAccountResponse
    if (!res.ok) {
      throw new Error(body.message || "Failed to deactivate account")
    }

    return body
  },

  async deleteAccount(password: string): Promise<DeactivateAccountResponse> {
    return apiService.deactivateAccount(password)
  },
}

export const profileService = {
  async updateProfile(target: "me" | number | string, payload: Record<string, unknown>) {
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
      "Email",
      "Phone",
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
      },
      credentials: "same-origin",
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
