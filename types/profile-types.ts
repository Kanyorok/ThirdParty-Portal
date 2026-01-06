import type { LucideIcon } from "lucide-react"

export const USER_TYPES = [
    { value: "Customer", id: 1, label: "Customer" },
    { value: "Tenant", id: 2, label: "Tenant" },
    { value: "Supplier", id: 3, label: "Supplier" },
] as const

export type UserProfile = (typeof USER_TYPES)[number]["value"]

export interface NavItemBase {
    readonly title: string
    readonly url: string
    readonly icon?: LucideIcon
    readonly allowedProfiles: readonly UserProfile[]
    readonly comingSoon?: boolean
    readonly newTab?: boolean
    readonly badge?: string
    readonly disabled?: boolean
}

export interface NavSubItem extends NavItemBase { }

export interface NavMainItem extends NavItemBase {
    readonly subItems?: readonly NavSubItem[]
}

export interface NavSection {
    readonly id: string
    readonly title: string
    readonly items: readonly NavMainItem[]
    readonly allowedProfiles?: readonly UserProfile[]
}

export interface SearchableNavItem {
    readonly label: string
    readonly group: string
    readonly href: string
    readonly icon?: LucideIcon
}