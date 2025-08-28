import { sidebarItems } from "@/navigation/sidebar/sidebar-items";
import type { LucideIcon } from "lucide-react";

export interface SearchableNavItem {
    readonly label: string;
    readonly group: string;
    readonly href: string;
    readonly icon?: LucideIcon;
}

export function getFlatNavItems(): SearchableNavItem[] {
    return sidebarItems.flatMap((group) =>
        group.items.flatMap((item) => {
            const allItems: SearchableNavItem[] = [];

            if (!item.disabled && !item.comingSoon) {
                allItems.push({
                    label: item.title,
                    group: group.label || "General",
                    href: item.url,
                    icon: item.icon,
                });
            }

            if (item.subItems) {
                const searchableSubItems = item.subItems
                    .filter((subItem) => !subItem.disabled && !subItem.comingSoon)
                    .map((subItem) => ({
                        label: subItem.title,
                        group: item.title,
                        href: subItem.url,
                        icon: subItem.icon,
                    }));

                allItems.push(...searchableSubItems);
            }

            return allItems;
        })
    );
}
