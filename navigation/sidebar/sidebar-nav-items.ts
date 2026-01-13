import { UserProfile, NavSection, NavMainItem } from "@/types/profile-types"
import {
    LayoutDashboard, Receipt, FileText,
    ClipboardList, FolderOpen, ShieldCheck, Home, BookOpen,
    Settings, HelpCircle, Send,
    Construction
} from "lucide-react"

const allProfiles: readonly UserProfile[] = ["base", "Supplier", "Tenant", "Customer"]

function withProfiles(
    item: Omit<NavMainItem, 'allowedProfiles'> & { allowedProfiles?: readonly UserProfile[] },
    sectionProfiles?: readonly UserProfile[]
): NavMainItem {
    const profiles = item.allowedProfiles ?? sectionProfiles ?? allProfiles

    const subItems = item.subItems?.map(sub => ({
        ...sub,
        allowedProfiles: sub.allowedProfiles ?? profiles,
    }))

    return {
        ...item,
        allowedProfiles: profiles,
        subItems
    } as NavMainItem
}

export const sidebarItems: readonly NavSection[] = [
    {
        id: "general",
        title: "Overview",
        allowedProfiles: allProfiles,
        items: [
            withProfiles({ title: "Dashboard", url: "/dashboard", icon: LayoutDashboard }, allProfiles),
        ],
    },
    {
        id: "supplier",
        title: "Vendor Management",
        allowedProfiles: ["Supplier"],
        items: [
            withProfiles({ title: "Prequalification", url: "/dashboard/procurement/rounds", icon: ShieldCheck }, ["Supplier"]),
            withProfiles({ title: "Find RFQs", url: "/dashboard/supplier/rfqs", icon: ClipboardList }, ["Supplier"]),
            withProfiles({ title: "Find Tenders", url: "/dashboard/supplier/tenders", icon: FileText }, ["Supplier"]),
            withProfiles({ title: "All Documents", url: "/dashboard/supplier/documents", icon: FolderOpen }, ["Supplier"]),
        ],
    },
    {
        id: "tenant",
        title: "Property Management",
        allowedProfiles: ["Tenant"],
        items: [
            withProfiles({ title: "Rentable Properties", url: "/dashboard/tenant/properties", icon: Home }, ["Tenant"]),
            withProfiles({ title: "Leases", url: "/dashboard/tenant/leases", icon: BookOpen }, ["Tenant"]),
            withProfiles({ title: "Invoices", url: "/dashboard/tenant/invoices", icon: Receipt }, ["Tenant"]),
            withProfiles({ title: "Maintenance", url: "/dashboard/tenant/maintenance", icon: Construction }, ["Tenant"])
        ],
    },
    {
        id: "customer",
        title: "Insurance Services",
        allowedProfiles: ["Customer"],
        items: [
            withProfiles({ title: "My Policies", url: "/dashboard/customer/policies", icon: ShieldCheck }, ["Customer"]),
        ],
    },
    {
        id: "utility",
        title: "Account & Help",
        allowedProfiles: allProfiles,
        items: [
            withProfiles({ title: "Settings", url: "/dashboard/account", icon: Settings }, allProfiles),
            withProfiles({ title: "Help Center", url: "/dashboard/help", icon: HelpCircle }, allProfiles),
            withProfiles({ title: "Send Feedback", url: "/dashboard/feedback", icon: Send, newTab: true }, allProfiles),
        ],
    },
]