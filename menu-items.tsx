"use client";

import { memo } from "react";
import Link from "next/link";
import { DropdownMenuItem } from "@/components/common/dropdown-menu";
import { MenuItem } from "@/types/navigation";
import { cn } from "@/lib/utils";

interface MenuItemComponentProps {
    item: MenuItem;
    onSelect: () => void;
}

export const MenuItemComponent = memo<MenuItemComponentProps>(({ item, onSelect }) => (
    <DropdownMenuItem key={item.id} asChild onSelect={onSelect}>
        <Link
            href={item.href}
            className={cn(
                "flex w-full items-center cursor-pointer rounded-lg p-3 text-sm transition-colors",
                "hover:bg-accent focus:bg-accent focus:outline-none"
            )}
        >
            {item.icon && <item.icon className="mr-3 h-4 w-4 text-muted-foreground" />}
            {item.shortcut && (
                <kbd className="ml-auto rounded bg-muted px-2 py-1 text-xs text-muted-foreground font-mono">
                    {item.shortcut}
                </kbd>
            )}
        </Link>
    </DropdownMenuItem>
));

MenuItemComponent.displayName = "MenuItemComponent";