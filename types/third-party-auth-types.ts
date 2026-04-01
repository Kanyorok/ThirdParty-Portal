import type { LucideIcon } from "lucide-react"
import type { ProfileType } from "@/store/use-profile-store"

export type UserProfile = ProfileType

export interface UserData {
    id?: string | number | null
    user_id?: number | null
    firstName?: string | null
    first_name?: string | null
    lastName?: string | null
    last_name?: string | null
    fullName?: string | null
    full_name?: string | null
    name?: string | null
    email?: string | null
    isActive?: boolean | null
    is_active?: boolean | null
    isSupplier?: boolean | null
    is_supplier?: boolean | null
    isTenant?: boolean | null
    is_tenant?: boolean | null
    isCustomer?: boolean | null
    is_customer?: boolean | null
    imageUrl?: string | null
    image_url?: string | null
    image?: string | null
    imageId?: number | null
    image_id?: number | null
}

export interface UserNavProps {
    user?: UserData
    isLoading: boolean
    isPending: boolean
    isOpen: boolean
    onLogout: () => void
    onOpenChange: (open: boolean) => void
}

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

export interface NavGroup {
    readonly id: string
    readonly label?: string
    readonly items: readonly NavMainItem[]
    readonly collapsible?: boolean
    readonly defaultOpen?: boolean
}

export interface NavMainProps {
    readonly items: readonly NavGroup[]
    readonly onItemClick?: (item: NavMainItem | NavSubItem) => void
    readonly className?: string
    readonly variant?: "primary" | "secondary"
}

export interface SearchableNavItem {
    readonly label: string
    readonly group: string
    readonly href: string
    readonly icon?: LucideIcon
}