import { LucideIcon } from "lucide-react";

export interface UserData {
    firstName?: string | null;
    lastName?: string | null;
    email?: string | null;
    isActive: boolean;
    imageUrl?: string | null;
}

export interface UserNavProps {
    user?: UserData;
    isLoading: boolean;
    isPending: boolean;
    isOpen: boolean;
    onLogout: () => void;
    onOpenChange: (open: boolean) => void;
    className?: string;
}

export interface MenuItem {
    id: string;
    label: string;
    icon: LucideIcon;
    href: string;
    shortcut?: string;
}

export interface MenuItemWithChildren extends MenuItem {
    children: Omit<MenuItem, 'icon' | 'shortcut'>[];
}