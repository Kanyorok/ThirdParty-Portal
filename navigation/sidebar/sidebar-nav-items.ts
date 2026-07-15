import { UserProfile, NavSection, NavMainItem, NavSubItem } from "@/types/profile-types"
import {
    LayoutGrid, ReceiptText, BriefcaseBusiness,
    ClipboardList, FolderKanban, ShieldCheck, BookOpen, ShoppingBag,
    Settings2, CircleHelp, Ticket,
    Construction,
    Building2, Trophy,
    Files
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
            withProfiles({ title: "Dashboard", url: "/dashboard", icon: LayoutGrid, description: "Summary & quick actions" }, allProfiles),
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
                icon: Files,
                description: "Track your tender, prequalification, and RFQ applications",
                subItems: [
                    {
                        title: "Tenders",
                        url: "/dashboard/supplier/my-applications/tenders",
                        description: "Monitor tender submissions, deadlines, and outcomes",
                    },
                    {
                        title: "Prequalification",
                        url: "/dashboard/supplier/my-applications/prequalification",
                        description: "Review category progress, scores, and approvals",
                    },
                    {
                        title: "RFQs",
                        url: "/dashboard/supplier/my-applications/rfqs",
                        description: "Manage quotes, response status, and award signals",
                    },
                ],
            }, ["Supplier"]),
            withProfiles({ title: "Find RFQs", url: "/dashboard/supplier/rfqs", icon: ClipboardList, description: "Browse requests for quotation" }, ["Supplier"]),
            withProfiles({ title: "Find Tenders", url: "/dashboard/supplier/tenders", icon: BriefcaseBusiness, description: "Explore available tenders" }, ["Supplier"]),
            withProfiles({ title: "Awards & Contracts", url: "/dashboard/supplier/contracts", icon: Trophy, description: "Track approved wins and contract stages" }, ["Supplier"]),
            withProfiles({ title: "Purchase Orders", url: "/dashboard/supplier/orders", icon: ShoppingBag, description: "View approved orders issued to your company" }, ["Supplier"]),
            withProfiles({ title: "All Documents", url: "/dashboard/supplier/documents", icon: FolderKanban, description: "Contracts, files & uploads" }, ["Supplier"]),
        ],
    },
    {
        id: "tenant",
        title: "Property Management",
        allowedProfiles: ["Tenant"],
        items: [
            withProfiles({ title: "Rentable Properties", url: "/dashboard/tenant/properties", icon: Building2, description: "Units, availability & listings" }, ["Tenant"]),
            withProfiles({ title: "My Interests", url: "/dashboard/tenant/interests", icon: ClipboardList, description: "Track submitted property interests" }, ["Tenant"]),
            withProfiles({ title: "Leases", url: "/dashboard/tenant/leases", icon: BookOpen, description: "Lease terms & renewals" }, ["Tenant"]),
            withProfiles({ title: "Invoices", url: "/dashboard/tenant/invoices", icon: ReceiptText, description: "Billing history & payments" }, ["Tenant"]),
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
            withProfiles({ title: "Settings", url: "/dashboard/settings/profile", icon: Settings2, description: "Profile & preferences" }, allProfiles),
            withProfiles({ title: "Help Center", url: "/dashboard/help", icon: CircleHelp, description: "Guides & support" }, allProfiles),
            withProfiles({ title: "My Tickets", url: "/dashboard/help/tickets", icon: Ticket, description: "Track and reply to support tickets" }, allProfiles),
        ],
    },
]
