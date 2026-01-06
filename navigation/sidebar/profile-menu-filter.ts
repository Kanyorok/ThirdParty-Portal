import { NavSection } from "@/types/profile-types"

export function getAllAllowedMenus(
    activeProfiles: any[],
    sections: readonly NavSection[]
): NavSection[] {
    return sections.map(section => ({
        ...section,
        items: [...section.items]
    }));
}