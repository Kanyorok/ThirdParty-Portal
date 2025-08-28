"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/components/common/button";

interface NavItem {
    href: string;
    title: string;
}

interface SettingsNavProps extends React.ComponentPropsWithoutRef<"nav"> {
    items: NavItem[];
}

export default function SettingsNav({
    className,
    items,
    ...props
}: SettingsNavProps) {
    const pathname = usePathname();

    return (
        <nav
            className={cn(
                "flex space-x-2 lg:flex-col lg:space-x-0 lg:space-y-1",
                className,
            )}
            aria-label="Settings navigation"
            {...props}
        >
            {items.map((item) => (
                <Link
                    key={item.href}
                    href={item.href}
                    className={cn(
                        buttonVariants({ variant: "ghost" }),
                        pathname === item.href
                            ? "bg-muted hover:bg-muted"
                            : "hover:bg-transparent hover:underline",
                        "justify-start",
                    )}
                    aria-current={pathname === item.href ? "page" : undefined}
                >
                    {item.title}
                </Link>
            ))}
        </nav>
    );
}