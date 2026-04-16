import { sidebarItems } from "@/navigation/sidebar/sidebar-nav-items"

type NavigationLookupOptions = {
    title: string
    parentTitle?: string
}

function findSidebarHref(options: NavigationLookupOptions) {
    for (const section of sidebarItems) {
        for (const item of section.items) {
            if (item.title === options.title && !item.disabled && !item.comingSoon) {
                return item.url
            }

            if (!item.subItems?.length) continue

            const parentMatches = !options.parentTitle || item.title === options.parentTitle
            if (!parentMatches) continue

            const subItem = item.subItems.find(
                (candidate) => candidate.title === options.title && !candidate.disabled && !candidate.comingSoon
            )

            if (subItem) return subItem.url
        }
    }

    return null
}

export const SEARCH_ROUTE_REFERENCES = {
    tenders: findSidebarHref({ title: "Find Tenders" }) ?? "/dashboard/supplier/tenders",
    rfqs: findSidebarHref({ title: "Find RFQs" }) ?? "/dashboard/supplier/rfqs",
    tenderApplications:
        findSidebarHref({ title: "Tenders", parentTitle: "My Applications" }) ?? "/dashboard/supplier/my-applications/tenders",
    rfqApplications:
        findSidebarHref({ title: "RFQs", parentTitle: "My Applications" }) ?? "/dashboard/supplier/my-applications/rfqs",
} as const