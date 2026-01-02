import {
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

export const sidebarItems: readonly NavSection[] = [
    {
        id: "general",
        title: "Platform Overview",
        items: [
            {
                title: "Dashboard",
                url: "/dashboard",
                icon: LayoutDashboard,
                allowedProfiles: allProfiles,
            },
            {
                title: "Notifications",
                url: "/dashboard/notifications",
                icon: Bell,
                allowedProfiles: allProfiles,
                badge: "2 Unread",
            },
        ],
    },
    {
        id: "customer",
        title: "Client Experience",
        allowedProfiles: ["Customer"],
        items: [
            {
                title: "Service Orders",
                url: "/dashboard/customer/orders",
                icon: ShoppingBag,
                allowedProfiles: ["Customer"],
            },
            {
                title: "Invoices & Billing",
                url: "/dashboard/customer/invoices",
                icon: Receipt,
                allowedProfiles: ["Customer"],
            },
            {
                title: "Insurance",
                url: "/dashboard/customer/insurance",
                icon: Shield,
                allowedProfiles: ["Customer"],
                comingSoon: true,
            },
        ],
    },
    {
        id: "supplier",
        title: "Vendor",
        allowedProfiles: ["Supplier"],
        items: [
            {
                title: "Prequalification",
                url: "/dashboard/supplier/prequalification",
                icon: ShieldCheck,
                allowedProfiles: ["Supplier"],
            },
            {
                title: "Find RFQs",
                url: "/dashboard/supplier/rfqs",
                icon: ClipboardList,
                allowedProfiles: ["Supplier"],
            },
            {
                title: "Find Tenders",
                url: "/dashboard/supplier/tenders",
                icon: FileText,
                allowedProfiles: ["Supplier"],
                badge: "New Bids",
            },
            {
                title: "Orders & Invoices",
                url: "/dashboard/supplier/orders",
                icon: Receipt,
                allowedProfiles: ["Supplier"],
            },
            {
                title: "All Documents",
                url: "/dashboard/supplier/documents",
                icon: FolderOpen,
                allowedProfiles: ["Supplier"],
            },
        ],
    },
    {
        id: "tenant",
        title: "Property Management",
        allowedProfiles: ["Tenant"],
        items: [
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
        ],
    },
    {
        id: "utility",
        title: "Account & Help",
        items: [
            {
                title: "Settings",
                url: "/dashboard/account",
                icon: Settings,
                allowedProfiles: allProfiles,
            },
            {
                title: "Help Center",
                url: "/dashboard/help",
                icon: HelpCircle,
                allowedProfiles: allProfiles,
            },
            {
                title: "Send Feedback",
                url: "/dashboard/feedback",
                icon: Send,
                allowedProfiles: allProfiles,
                newTab: true,
            },
        ],
    },
] as const