"use client"

import * as React from "react"
import { ThemeProvider as NextThemesProvider } from "next-themes"
import { useProfileStore } from "@/store/use-profile-store"
import { cn } from "@/lib/utils"

export function ThemeProvider({
    children,
    ...props
}: React.ComponentProps<typeof NextThemesProvider>) {
    const { activeProfile } = useProfileStore()
    const [mounted, setMounted] = React.useState(false)

    React.useEffect(() => {
        setMounted(true)
    }, [])

    const roleThemeClass = React.useMemo(() => {
        if (!mounted) return "theme-base"
        switch (activeProfile) {
            case "Supplier":
                return "theme-supplier"
            case "Tenant":
                return "theme-tenant"
            case "Customer":
                return "theme-customer"
            default:
                return "theme-base"
        }
    }, [activeProfile, mounted])

    return (
        <NextThemesProvider {...props}>
            <div className={cn("contents transition-colors duration-500", roleThemeClass)}>
                {children}
            </div>
        </NextThemesProvider>
    )
}
