import type { LucideIcon } from "lucide-react"
import type { UserProfile as ProfileType } from "@/types/third-party-auth-types"

export type UserProfile = ProfileType

interface NavItemBase {
    readonly title: string
    readonly url: string
    readonly icon?: LucideIcon
    readonly allowedProfiles: readonly UserProfile[]
    readonly description?: string
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
    readonly allowedProfiles: readonly UserProfile[]
    readonly items: readonly NavMainItem[]
}
