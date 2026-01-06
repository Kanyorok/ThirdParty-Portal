import { USER_TYPES, UserProfile, NavSection, NavMainItem } from "@/types/profile-types"
import {
<<<<<<< Updated upstream
    LayoutDashboard, Receipt, FileText,
    ClipboardList, FolderOpen, ShieldCheck, Home, BookOpen,
    Wallet, Settings, HelpCircle, Send
} from "lucide-react"

const allProfiles: readonly UserProfile[] = USER_TYPES.map(u => u.value)

function withProfiles(item: NavMainItem, sectionProfiles?: readonly UserProfile[]): NavMainItem {
    const profiles = item.allowedProfiles ?? sectionProfiles ?? allProfiles

    const subItems = item.subItems?.map(sub => ({
        ...sub,
        allowedProfiles: sub.allowedProfiles ?? profiles,
    }))

    return {
        ...item,
        allowedProfiles: profiles,
        subItems
    }
}
=======
    NavSection,
    UserProfile,
    USER_TYPES,
} from "@/types/profile-types"
import {
    LayoutDashboard,
    Bell,
    ShoppingBag,
    Receipt,
    Shield,
    FileText,
    ClipboardList,
    FolderOpen,
    ShieldCheck,
    Home,
    Wrench,
    BookOpen,
    RotateCw,
    XOctagon,
    Wallet,
    CreditCard,
    Settings,
    HelpCircle,
    Send,
} from "lucide-react"

const allProfiles: readonly UserProfile[] = USER_TYPES.map(u => u.value)
>>>>>>> Stashed changes

export const sidebarItems: readonly NavSection[] = [
    {
        id: "general",
        title: "Overview",
        allowedProfiles: allProfiles,
        items: [
            withProfiles({ title: "Dashboard", url: "/dashboard", icon: LayoutDashboard, allowedProfiles: allProfiles }),
        ],
    },
    {
        id: "supplier",
        title: "Vendor",
        allowedProfiles: ["Supplier"],
        items: [
            withProfiles({ title: "Prequalification", url: "/dashboard/supplier/prequalification", icon: ShieldCheck, allowedProfiles: ["Supplier"] }),
            withProfiles({ title: "Find RFQs", url: "/dashboard/supplier/rfqs", icon: ClipboardList, allowedProfiles: ["Supplier"] }),
            withProfiles({ title: "Find Tenders", url: "/dashboard/supplier/tenders", icon: FileText, allowedProfiles: ["Supplier"] }),
            withProfiles({ title: "All Documents", url: "/dashboard/supplier/documents", icon: FolderOpen, allowedProfiles: ["Supplier"] }),
        ],
    },
    {
        id: "tenant",
        title: "Property",
        allowedProfiles: ["Tenant"],
        items: [
<<<<<<< Updated upstream
            withProfiles({ title: "Rentable Properties", url: "/dashboard/tenant/properties", icon: Home, allowedProfiles: ["Tenant"] }),
            withProfiles({ title: "Leases", url: "/dashboard/tenant/leases", icon: BookOpen, allowedProfiles: ["Tenant"] }),
            withProfiles({ title: "Invoices", url: "/dashboard/tenant/invoices", icon: Receipt, allowedProfiles: ["Tenant"] }),
=======
            {
                title: "My Properties",
                url: "/dashboard/tenant/properties",
                icon: Home,
                allowedProfiles: ["Tenant"],
            },
            {
                title: "Maintenance Requests",
                url: "/dashboard/tenant/maintenance",
                icon: Wrench,
                allowedProfiles: ["Tenant"],
            },
            {
                title: "Lease Actions",
                url: "/dashboard/tenant/lease-actions",
                icon: BookOpen,
                allowedProfiles: ["Tenant"],
                subItems: [
                    {
                        title: "Renewal",
                        url: "/dashboard/tenant/lease-actions/renewal",
                        icon: RotateCw,
                        allowedProfiles: ["Tenant"],
                    },
                    {
                        title: "Termination",
                        url: "/dashboard/tenant/lease-actions/termination",
                        icon: XOctagon,
                        allowedProfiles: ["Tenant"],
                    },
                ],
            },
            {
                title: "Invoices",
                url: "/dashboard/tenant/invoices",
                icon: FileText,
                allowedProfiles: ["Tenant"],
            },
            {
                title: "Finances",
                url: "/dashboard/tenant/finances",
                icon: Wallet,
                allowedProfiles: ["Tenant"],
                subItems: [
                    {
                        title: "Invoices & Receipts",
                        url: "/dashboard/tenant/invoices-receipts",
                        icon: Receipt,
                        allowedProfiles: ["Tenant"],
                    },
                    {
                        title: "Direct Payments",
                        url: "/dashboard/tenant/payments",
                        icon: CreditCard,
                        allowedProfiles: ["Tenant"],
                    },
                ],
            },
>>>>>>> Stashed changes
        ],
    },
    {
        id: "utility",
        title: "Account & Help",
        allowedProfiles: allProfiles,
        items: [
            withProfiles({ title: "Settings", url: "/dashboard/account", icon: Settings, allowedProfiles: allProfiles }),
            withProfiles({ title: "Help Center", url: "/dashboard/help", icon: HelpCircle, allowedProfiles: allProfiles }),
            withProfiles({ title: "Send Feedback", url: "/dashboard/feedback", icon: Send, newTab: true, allowedProfiles: allProfiles }),
        ],
    },
] as const