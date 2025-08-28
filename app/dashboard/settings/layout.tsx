import React from "react";
import { Separator } from "@/components/common/separator";
import { Bell, Banknote, Briefcase } from "lucide-react";
import { NavLink } from "@/components/common/nav-link";

interface SettingsLayoutProps {
    children: React.ReactNode;
}

const sidebarNavItems = [
    {
        title: "Notifications",
        href: "/dashboard/settings/notifications",
        icon: Bell,
    },
    {
        title: "Bank Details",
        href: "/dashboard/settings/billing",
        icon: Banknote,
    },
    {
        title: "Company Info",
        href: "/dashboard/settings/business-info",
        icon: Briefcase,
    },
];

export default function SettingsLayout({ children }: SettingsLayoutProps) {
    return (
        <div className="hidden space-y-8 p-4 md:block md:p-8">
            <header>
                <h1 className="text-3xl font-bold tracking-tight text-foreground">Business Settings</h1>
            </header>
            <Separator />
            <div className="flex flex-col space-y-8 lg:flex-row lg:space-x-12 lg:space-y-0">
                <aside className="lg:w-1/5">
                    <nav className="flex space-x-2 lg:flex-col lg:space-x-0 lg:space-y-1">
                        {sidebarNavItems.map((item) => (
                            <NavLink
                                key={item.href}
                                href={item.href}
                                className="inline-flex items-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 justify-start"
                                activeClassName="bg-muted hover:bg-muted text-primary"
                            >
                                {item.icon && <item.icon className="mr-3 h-4 w-4" />}
                                {item.title}
                            </NavLink>
                        ))}
                    </nav>
                </aside>
                <div className="flex-1 lg:max-w-4xl">{children}</div>
            </div>
        </div>
    );
}
