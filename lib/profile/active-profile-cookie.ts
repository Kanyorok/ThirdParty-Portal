import type { ProfileType } from "@/store/use-profile-store"

export const ACTIVE_PROFILE_COOKIE_NAME = "profile:active"
export const ACTIVE_PROFILE_COOKIE_MAX_AGE_SECONDS = 60 * 60 * 24 * 180 // ~6 months

const VALID_PROFILES: readonly ProfileType[] = ["base", "Supplier", "Tenant", "Customer"] as const

export function isProfileType(value: unknown): value is ProfileType {
  return typeof value === "string" && (VALID_PROFILES as readonly string[]).includes(value)
}

export function parseActiveProfileCookie(value: string | undefined | null): ProfileType | null {
  if (!value) return null
  return isProfileType(value) ? value : null
}

export function readActiveProfileFromDocumentCookie(): ProfileType | null {
  if (typeof document === "undefined") return null

  const cookie = document.cookie || ""
  const parts = cookie.split(";")
  for (const part of parts) {
    const [rawName, ...rest] = part.trim().split("=")
    if (!rawName) continue
    if (rawName !== ACTIVE_PROFILE_COOKIE_NAME) continue
    const value = rest.join("=")
    try {
      return parseActiveProfileCookie(decodeURIComponent(value))
    } catch {
      return parseActiveProfileCookie(value)
    }
  }
  return null
}

export function writeActiveProfileCookie(profile: ProfileType) {
  if (typeof document === "undefined") return
  const value = encodeURIComponent(profile)
  const maxAge = ACTIVE_PROFILE_COOKIE_MAX_AGE_SECONDS
  document.cookie = `${ACTIVE_PROFILE_COOKIE_NAME}=${value}; Path=/; Max-Age=${maxAge}; SameSite=Lax`
}

