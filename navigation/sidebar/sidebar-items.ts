import type { LucideIcon } from "lucide-react";
import {
    LayoutDashboard,
    FileText,
    ReceiptText,
    FolderOpen,
    ClipboardList,
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
                url: "/dashboard/tenders",
                icon: FileText,
            },
            {
                title: "RFQs",
                url: "/dashboard/rfqs",
                icon: ClipboardList,
            },
            {
                title: "Prequalification",
                url: "/dashboard/prequalification",
                icon: ClipboardCheck,
            },
            {
                title: "My Documents",
                url: "/dashboard/documents",
                icon: FolderOpen,
            },
        ],
    },
    {
        id: "utility",
        items: [
            {
                title: "My Account",
                url: "/dashboard/account",
                icon: User,
            },
            {
                title: "Help & Support",
                url: "/dashboard/help",
                icon: HelpCircle,
            },
        ],
    },
] as const;