"use server"

import { cookies } from "next/headers"
import { revalidatePath, revalidateTag } from "next/cache"

export enum AppCookieKeys {
  LAYOUT_SIDEBAR_VARIANT = "sidebar_variant",
  LAYOUT_SIDEBAR_COLLAPSIBLE = "sidebar_collapsible",
  LAYOUT_CONTENT_LAYOUT = "content_layout",
  USER_THEME_PREFERENCE = "theme_preference",
  USER_LANGUAGE = "user_language",
  FORM_DRAFT_PREFIX = "form_draft_",
}

export interface CookieOptions {
  path?: string
  maxAge?: number
  httpOnly?: boolean
  secure?: boolean
  sameSite?: "strict" | "lax" | "none"
  revalidatePaths?: string[]
  revalidateTags?: string[]
}

export interface CookieResult<T = string> {
  success: boolean
  data?: T
  error?: string
}

const DEFAULT_COOKIE_OPTIONS: Required<Omit<CookieOptions, "revalidatePaths" | "revalidateTags">> = {
  httpOnly: true,
  secure: process.env.NODE_ENV === "production",
  sameSite: "lax",
  path: "/",
  maxAge: 60 * 60 * 24 * 365, // 1 year
}

const REVALIDATION_MAP: Record<string, { paths: string[]; tags: string[] }> = {
  [AppCookieKeys.LAYOUT_SIDEBAR_VARIANT]: {
    paths: ["/dashboard"],
    tags: ["layout-preferences", "sidebar-config"],
  },
  [AppCookieKeys.LAYOUT_SIDEBAR_COLLAPSIBLE]: {
    paths: ["/dashboard"],
    tags: ["layout-preferences", "sidebar-config"],
  },
  [AppCookieKeys.LAYOUT_CONTENT_LAYOUT]: {
    paths: ["/dashboard", "/dashboard/profile"],
    tags: ["layout-preferences"],
  },
  [AppCookieKeys.USER_THEME_PREFERENCE]: {
    paths: ["/"],
    tags: ["theme-config"],
  },
  [AppCookieKeys.USER_LANGUAGE]: {
    paths: ["/"],
    tags: ["i18n-config"],
  },
}

function validateCookieKey(key: string): boolean {
  if (!key || typeof key !== "string") return false
  if (key.length > 100) return false
  if (!/^[a-zA-Z0-9_-]+$/.test(key)) return false
  return true
}

function validateCookieValue(value: string): boolean {
  if (typeof value !== "string") return false
  if (value.length > 4096) return false
  return true
}

async function performAutoRevalidation(key: string): Promise<void> {
  const revalidationConfig = REVALIDATION_MAP[key]
  if (!revalidationConfig) return

  try {
    for (const path of revalidationConfig.paths) {
      revalidatePath(path)
    }

    for (const tag of revalidationConfig.tags) {
      revalidateTag(tag)
    }
  } catch (error) {
    console.warn(`Warning: Auto-revalidation failed for key "${key}":`, error)
  }
}

export async function getCookieValue(key: string): Promise<CookieResult<string>> {
  try {
    if (!validateCookieKey(key)) {
      return {
        success: false,
        error: `Invalid cookie key format: "${key}"`,
      }
    }

    const cookieStore = await cookies()
    const cookie = cookieStore.get(key)

    if (!cookie) {
      return {
        success: true,
        data: undefined,
      }
    }

    return {
      success: true,
      data: cookie.value,
    }
  } catch (error) {
    const errorMessage = `Failed to retrieve cookie "${key}": ${error instanceof Error ? error.message : "Unknown error"}`
    console.error("Cookie retrieval error:", errorMessage)

    return {
      success: false,
      error: errorMessage,
    }
  }
}

export async function getCookieJSON<T = any>(key: string): Promise<CookieResult<T>> {
  try {
    const result = await getCookieValue(key)

    if (!result.success || !result.data) {
      return result as CookieResult<T>
    }

    const parsedData = JSON.parse(result.data) as T
    return {
      success: true,
      data: parsedData,
    }
  } catch (error) {
    return {
      success: false,
      error: `Failed to parse JSON cookie "${key}": ${error instanceof Error ? error.message : "Invalid JSON"}`,
    }
  }
}

export async function setCookieValue(
  key: string,
  value: string,
  options: CookieOptions = {},
): Promise<CookieResult<void>> {
  try {
    if (!validateCookieKey(key)) {
      return {
        success: false,
        error: `Invalid cookie key format: "${key}"`,
      }
    }

    if (!validateCookieValue(value)) {
      return {
        success: false,
        error: `Invalid cookie value: too large or invalid format`,
      }
    }

    const cookieStore = await cookies()
    const finalOptions = {
      ...DEFAULT_COOKIE_OPTIONS,
      ...options,
    }

    cookieStore.set(key, value, finalOptions)

    if (options.revalidatePaths?.length) {
      options.revalidatePaths.forEach((path) => revalidatePath(path))
    }

    if (options.revalidateTags?.length) {
      options.revalidateTags.forEach((tag) => revalidateTag(tag))
    }

    await performAutoRevalidation(key)

    return { success: true }
  } catch (error) {
    const errorMessage = `Failed to set cookie "${key}": ${error instanceof Error ? error.message : "Unknown error"}`
    console.error("Cookie setting error:", errorMessage)

    return {
      success: false,
      error: errorMessage,
    }
  }
}

export async function setCookieJSON<T = any>(
  key: string,
  value: T,
  options: CookieOptions = {},
): Promise<CookieResult<void>> {
  try {
    const serializedValue = JSON.stringify(value)
    return await setCookieValue(key, serializedValue, options)
  } catch (error) {
    return {
      success: false,
      error: `Failed to serialize JSON for cookie "${key}": ${error instanceof Error ? error.message : "Serialization error"}`,
    }
  }
}

export async function deleteCookie(key: string): Promise<CookieResult<void>> {
  try {
    if (!validateCookieKey(key)) {
      return {
        success: false,
        error: `Invalid cookie key format: "${key}"`,
      }
    }

    const cookieStore = await cookies()
    cookieStore.delete(key)

    await performAutoRevalidation(key)

    return { success: true }
  } catch (error) {
    const errorMessage = `Failed to delete cookie "${key}": ${error instanceof Error ? error.message : "Unknown error"}`
    console.error("Cookie deletion error:", errorMessage)

    return {
      success: false,
      error: errorMessage,
    }
  }
}

export async function cookieExists(key: string): Promise<CookieResult<boolean>> {
  try {
    const result = await getCookieValue(key)
    return {
      success: true,
      data: result.success && result.data !== undefined,
    }
  } catch (error) {
    return {
      success: false,
      error: `Failed to check cookie existence "${key}": ${error instanceof Error ? error.message : "Unknown error"}`,
    }
  }
}

export async function getMultipleCookies(keys: string[]): Promise<CookieResult<Record<string, string | undefined>>> {
  try {
    const results: Record<string, string | undefined> = {}

    for (const key of keys) {
      const result = await getCookieValue(key)
      results[key] = result.success ? result.data : undefined
    }

    return {
      success: true,
      data: results,
    }
  } catch (error) {
    return {
      success: false,
      error: `Failed to retrieve multiple cookies: ${error instanceof Error ? error.message : "Unknown error"}`,
    }
  }
}

export async function setMultipleCookies(
  cookies: Record<string, string>,
  options: CookieOptions = {},
): Promise<CookieResult<void>> {
  try {
    const results = await Promise.allSettled(
      Object.entries(cookies).map(([key, value]) => setCookieValue(key, value, options)),
    )

    const failures = results.filter((result) => result.status === "rejected" || !result.value.success)

    if (failures.length > 0) {
      return {
        success: false,
        error: `Failed to set ${failures.length} out of ${results.length} cookies`,
      }
    }

    return { success: true }
  } catch (error) {
    return {
      success: false,
      error: `Failed to set multiple cookies: ${error instanceof Error ? error.message : "Unknown error"}`,
    }
  }
}


export const LayoutCookies = {
  async getSidebarVariant(): Promise<string | undefined> {
    const result = await getCookieValue(AppCookieKeys.LAYOUT_SIDEBAR_VARIANT)
    return result.success ? result.data : undefined
  },

  async setSidebarVariant(variant: "sidebar" | "floating" | "inset"): Promise<boolean> {
    const result = await setCookieValue(AppCookieKeys.LAYOUT_SIDEBAR_VARIANT, variant)
    return result.success
  },

  async getSidebarCollapsible(): Promise<string | undefined> {
    const result = await getCookieValue(AppCookieKeys.LAYOUT_SIDEBAR_COLLAPSIBLE)
    return result.success ? result.data : undefined
  },

  async setSidebarCollapsible(collapsible: "offcanvas" | "icon" | "none"): Promise<boolean> {
    const result = await setCookieValue(AppCookieKeys.LAYOUT_SIDEBAR_COLLAPSIBLE, collapsible)
    return result.success
  },

  async getContentLayout(): Promise<string | undefined> {
    const result = await getCookieValue(AppCookieKeys.LAYOUT_CONTENT_LAYOUT)
    return result.success ? result.data : undefined
  },

  async setContentLayout(layout: string): Promise<boolean> {
    const result = await setCookieValue(AppCookieKeys.LAYOUT_CONTENT_LAYOUT, layout)
    return result.success
  },
}

export const UserPreferenceCookies = {
  async getTheme(): Promise<"light" | "dark" | "system" | undefined> {
    const result = await getCookieValue(AppCookieKeys.USER_THEME_PREFERENCE)
    return result.success ? (result.data as "light" | "dark" | "system") : undefined
  },

  async setTheme(theme: "light" | "dark" | "system"): Promise<boolean> {
    const result = await setCookieValue(AppCookieKeys.USER_THEME_PREFERENCE, theme)
    return result.success
  },

  async getLanguage(): Promise<string | undefined> {
    const result = await getCookieValue(AppCookieKeys.USER_LANGUAGE)
    return result.success ? result.data : undefined
  },

  async setLanguage(language: string): Promise<boolean> {
    const result = await setCookieValue(AppCookieKeys.USER_LANGUAGE, language)
    return result.success
  },
}
