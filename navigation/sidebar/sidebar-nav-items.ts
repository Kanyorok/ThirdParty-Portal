import { UserProfile, NavSection, NavMainItem, NavSubItem } from "@/types/profile-types"
import {
    LayoutDashboard, Receipt, FileText,
    ClipboardList, FolderOpen, ShieldCheck, BookOpen,
    Settings, HelpCircle, Ticket,
    Construction,
    LandPlot,
    FileCheck2
} from "lucide-react"

const allProfiles: readonly UserProfile[] = ["Supplier", "Tenant", "Customer"]

type DraftNavSubItem = Omit<NavSubItem, "allowedProfiles"> & {
    allowedProfiles?: readonly UserProfile[]
}

type DraftNavMainItem = Omit<NavMainItem, "allowedProfiles" | "subItems"> & {
    allowedProfiles?: readonly UserProfile[]
    subItems?: readonly DraftNavSubItem[]
}

function withProfiles(
    item: DraftNavMainItem,
    sectionProfiles?: readonly UserProfile[]
): NavMainItem {
    const profiles = item.allowedProfiles ?? sectionProfiles ?? allProfiles

    const subItems = item.subItems?.map((sub) => ({
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
            withProfiles({ title: "Dashboard", url: "/dashboard", icon: LayoutDashboard, description: "Summary & quick actions" }, allProfiles),
        ],
    },
    {
        id: "supplier",
        title: "Vendor Management",
        allowedProfiles: ["Supplier"],
        items: [
            withProfiles({ title: "Prequalification", url: "/dashboard/supplier/prequalification", icon: ShieldCheck, description: "Compliance & onboarding" }, ["Supplier"]),
            withProfiles({
                title: "My Applications",
                url: "/dashboard/supplier/my-applications",
                icon: FileCheck2,
                description: "Track your tender, prequalification, and RFQ applications",
                subItems: [
                    { title: "Tenders", url: "/dashboard/supplier/my-applications/tenders" },
                    { title: "Prequalification", url: "/dashboard/supplier/my-applications/prequalification" },
                    { title: "RFQs", url: "/dashboard/supplier/my-applications/rfqs" },
                ],
            }, ["Supplier"]),
            withProfiles({ title: "Find RFQs", url: "/dashboard/supplier/rfqs", icon: ClipboardList, description: "Browse requests for quotation" }, ["Supplier"]),
            withProfiles({ title: "Find Tenders", url: "/dashboard/supplier/tenders", icon: FileText, description: "Explore available tenders" }, ["Supplier"]),
            withProfiles({ title: "All Documents", url: "/dashboard/supplier/documents", icon: FolderOpen, description: "Contracts, files & uploads" }, ["Supplier"]),
        ],
    },
    {
        id: "tenant",
        title: "Property Management",
        allowedProfiles: ["Tenant"],
        items: [
            withProfiles({ title: "Rentable Properties", url: "/dashboard/tenant/properties", icon: LandPlot, description: "Units, availability & listings" }, ["Tenant"]),
            withProfiles({ title: "My Interests", url: "/dashboard/tenant/interests", icon: ClipboardList, description: "Track submitted property interests" }, ["Tenant"]),
            withProfiles({ title: "Leases", url: "/dashboard/tenant/leases", icon: BookOpen, description: "Lease terms & renewals" }, ["Tenant"]),
            withProfiles({ title: "Invoices", url: "/dashboard/tenant/invoices", icon: Receipt, description: "Billing history & payments" }, ["Tenant"]),
            withProfiles({ title: "Maintenance", url: "/dashboard/tenant/maintenance", icon: Construction, description: "Requests & work orders" }, ["Tenant"])
        ],
    },
    {
        id: "customer",
        title: "Insurance Services",
        allowedProfiles: ["Customer"],
        items: [
            withProfiles({ title: "My Policies", url: "/dashboard/customer/policies", icon: ShieldCheck, description: "Coverage & documents" }, ["Customer"]),
        ],
    },
    {
        id: "utility",
        title: "Account & Help",
        allowedProfiles: allProfiles,
        items: [
            withProfiles({ title: "Settings", url: "/dashboard/settings/profile", icon: Settings, description: "Profile & preferences" }, allProfiles),
            withProfiles({ title: "Help Center", url: "/dashboard/help", icon: HelpCircle, description: "Guides & support" }, allProfiles),
            withProfiles({ title: "My Tickets", url: "/dashboard/help/tickets", icon: Ticket, description: "Track and reply to support tickets" }, allProfiles),
        ],
    },
]
