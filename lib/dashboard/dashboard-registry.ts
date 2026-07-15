import type { ProfileType } from "@/store/use-profile-store"

export type DashboardAction = { label: string; href: string }

export type DashboardRegistryEntry = {
  contextLabel: string
  primaryAction: DashboardAction
  secondaryAction: DashboardAction
}

const BASE_ENTRY: DashboardRegistryEntry = {
  contextLabel: "Dashboard",
  primaryAction: { label: "Complete profile", href: "/dashboard/settings/profile" },
  secondaryAction: { label: "Account settings", href: "/dashboard/settings/profile" },
}

const REGISTRY: Record<Exclude<ProfileType, "base">, DashboardRegistryEntry> = {
  Supplier: {
    contextLabel: "Supplier Profile",
    primaryAction: { label: "Find tenders", href: "/dashboard/supplier/tenders" },
    secondaryAction: { label: "Prequalification", href: "/dashboard/supplier/prequalification" },
  },
  Tenant: {
    contextLabel: "Tenant Profile",
    primaryAction: { label: "Browse properties", href: "/dashboard/tenant/properties" },
    secondaryAction: { label: "Maintenance", href: "/dashboard/tenant/maintenance" },
  },
  Customer: {
    contextLabel: "Customer Profile",
    primaryAction: { label: "View policies", href: "/dashboard/customer/policies" },
    secondaryAction: { label: "Profile settings", href: "/dashboard/settings/profile" },
  },
}

export function getDashboardRegistryEntry(profile: ProfileType): DashboardRegistryEntry {
  if (profile === "base") return BASE_ENTRY
  return REGISTRY[profile] ?? BASE_ENTRY
}
