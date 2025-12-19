import { NavSection, NavMainItem, UserProfile } from "@/types/profile-types"

export function getProfileMenu(
    userProfile: UserProfile,
    menuData: readonly NavSection[]
): readonly NavSection[] {
    const filteredSections = menuData.filter((section) => {
        if (section.allowedProfiles) {
            return section.allowedProfiles.includes(userProfile)
        }
        return true
    })

    return filteredSections
        .map((section) => {
            const filteredItems = section.items.filter((item) =>
                item.allowedProfiles.includes(userProfile)
            ) as NavMainItem[]

            return {
                ...section,
                items: filteredItems,
            }
        })
        .filter((section) => section.items.length > 0)
}