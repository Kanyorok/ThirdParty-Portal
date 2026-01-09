import type { LucideIcon } from "lucide-react"
import { ProfileType } from "@/store/profile-store"

export type UserProfile = ProfileType

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
    readonly allowedProfiles: readonly UserProfile[]
}

export interface SearchableNavItem {
    readonly label: string
    readonly group: string
    readonly href: string
    readonly icon?: LucideIcon
}