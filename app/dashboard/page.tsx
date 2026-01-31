import { cookies, headers } from "next/headers"
import { getDashboardData } from "@/lib/dashboard-summary-data"
import {
  ACTIVE_PROFILE_COOKIE_NAME,
  parseActiveProfileCookie,
} from "@/lib/profile/active-profile-cookie"
import { MasterDashboardClient } from "@/components/dashboard/master-dashboard-client"
import type { ProfileType } from "@/store/use-profile-store"

export const dynamic = "force-dynamic"

function resolveFirstName(user: any): string {
  const first =
    user?.first_name ??
    user?.firstName ??
    String(user?.full_name ?? user?.fullName ?? user?.name ?? "User")
      .trim()
      .split(" ")[0]
  return String(first || "User")
}

function getAuthorizedProfiles(user: any): ProfileType[] {
  const roles: ProfileType[] = []
  if (user?.is_supplier || user?.isSupplier) roles.push("Supplier")
  if (user?.is_tenant || user?.isTenant) roles.push("Tenant")
  if (user?.is_customer || user?.isCustomer) roles.push("Customer")
  return roles
}

function pickInitialProfile(
  cookieValue: string | undefined,
  authorized: ProfileType[]
): ProfileType {
  const cookieProfile = parseActiveProfileCookie(cookieValue)
  if (
    cookieProfile &&
    cookieProfile !== "base" &&
    authorized.includes(cookieProfile)
  ) {
    return cookieProfile
  }
  return authorized[0] ?? "base"
}

async function getProfileFromRequest(): Promise<string | undefined> {
  const h = await headers()
  const url = h.get("x-url") ?? h.get("referer")
  if (!url) return undefined
  try {
    const parsed = new URL(url, "http://localhost")
    return parsed.searchParams.get("profile") ?? undefined
  } catch {
    return undefined
  }
}

export default async function Dashboard() {
  const { getServerSession } = await import("next-auth")
  const { authOptions } = await import("@/lib/auth-options")

  const session = await getServerSession(authOptions)
  const user = (session as any)?.user

  const authorizedProfiles = getAuthorizedProfiles(user)

  const cookieStore = await cookies()
  const cookieValue = cookieStore.get(ACTIVE_PROFILE_COOKIE_NAME)?.value

  const requestedParam = await getProfileFromRequest()
  const requestedProfile = parseActiveProfileCookie(requestedParam)

  const initialProfile =
    requestedProfile &&
      requestedProfile !== "base" &&
      authorizedProfiles.includes(requestedProfile)
      ? requestedProfile
      : pickInitialProfile(cookieValue, authorizedProfiles)

  const dashboardData = await getDashboardData()

  return (
    <MasterDashboardClient
      firstName={resolveFirstName(user)}
      initialProfile={initialProfile}
      dashboardData={dashboardData}
    />
  )
}
