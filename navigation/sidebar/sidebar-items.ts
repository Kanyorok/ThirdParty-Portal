import type { LucideIcon } from "lucide-react";
import {
    LayoutDashboard,
    FileText,
    ReceiptText,
    FolderOpen,
    ClipboardList,
    Building2,
    ClipboardCheck,
    HelpCircle,
    User,
    Receipt,
} from "lucide-react";

export interface NavItemBase {
    readonly title: string;
    readonly url: string;
    readonly icon?: LucideIcon;
    readonly comingSoon?: boolean;
    readonly newTab?: boolean;
    readonly badge?: string;
    readonly disabled?: boolean;
}

export interface NavSubItem extends NavItemBase { }

export interface NavMainItem extends NavItemBase {
    readonly subItems?: readonly NavSubItem[];
}

export interface NavSection {
    readonly id: string;
    readonly items: readonly NavMainItem[];
}

export const sidebarItems: readonly NavSection[] = [
    {
        id: "general",
        items: [
            {
                title: "Dashboard",
                url: "/dashboard",
                icon: LayoutDashboard,
            },
        ],
    },
    {
        id: "procurement",
        items: [
            {
                title: "Tenders",
                url: "/tenders",
                icon: FileText,
            },
            {
                title: "RFQs",
                url: "/rfqs",
                icon: ClipboardList,
            },
            {
                title: "Prequalification",
                url: "/prequalification",
                icon: ClipboardCheck,
            },
            {
                title: "Orders & Invoices",
                url: "/orders-invoices",
                icon: ReceiptText,
            },
            {
                title: 'Purchase Orders',
                url: '/po',
                icon: Receipt,
            },
            {
                title: "My Documents",
                url: "/documents",
                icon: FolderOpen,
            },
            {
                title: "Buyers Directory",
                url: "/buyers",
                icon: Building2,
            },
        ],
    },
    {
        id: "utility",
        items: [
            {
                title: "My Account",
                url: "/account",
                icon: User,
            },
            {
                title: "Help & Support",
                url: "/help",
                icon: HelpCircle,
            },
        ],
    },
] as const;