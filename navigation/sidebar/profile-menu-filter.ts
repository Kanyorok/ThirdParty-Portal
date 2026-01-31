import { NavSection, UserProfile } from "@/types/profile-types"

export function getFilteredMenus(
    activeProfile: UserProfile,
    items: readonly NavSection[]
): NavSection[] {
    return items
        .filter((section) => {
            if (activeProfile === 'base') {
                return section.id === 'general' || section.id === 'utility'
            }
            return section.allowedProfiles.includes(activeProfile)
        })
        .map((section) => ({
            ...section,
            items: section.items.filter((item) => {
                if (activeProfile === 'base') return true
                return item.allowedProfiles.includes(activeProfile)
            })
        }))
}