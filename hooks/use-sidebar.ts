import { useMemo } from 'react'
import { useProfileStore } from '@/store/use-profile-store'
import { sidebarItems } from '@/navigation/sidebar/sidebar-nav-items'

export function useSidebar() {
    const activeProfile = useProfileStore((state) => state.activeProfile)

    // hook to ensure that we show the user data based on their current profile
    const filteredNavigation = useMemo(() => {
        return sidebarItems
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
    }, [activeProfile])

    return {
        navigation: filteredNavigation,
        activeProfile
    }
}