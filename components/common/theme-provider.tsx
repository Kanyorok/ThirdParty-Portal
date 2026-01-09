"use client"

import * as React from "react"
import { ThemeProvider as NextThemesProvider } from "next-themes"
import { useProfileStore } from "@/store/profile-store"
import { cn } from "@/lib/utils"

export function ThemeProvider({
    children,
    ...props
}: React.ComponentProps<typeof NextThemesProvider>) {
    const { activeProfile } = useProfileStore()

    const roleThemeClass = React.useMemo(() => {
        switch (activeProfile) {
            case "Supplier": return "theme-supplier"
            case "Tenant": return "theme-tenant"
            case "Customer": return "theme-customer"
            default: return "theme-base"
        }
    }, [activeProfile])

    return (
        <NextThemesProvider {...props}>
            <div className={cn("contents transition-colors duration-500", roleThemeClass)}>
                {children}
            </div>
        </NextThemesProvider>
    )
}