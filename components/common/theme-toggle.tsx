"use client"

import * as React from "react"
import { Moon, Sun } from "lucide-react"
import { useTheme } from "next-themes"

import { Button } from "@/components/common/button"

type ThemeToggleProps = {
    className?: string
    variant?: React.ComponentProps<typeof Button>["variant"]
    size?: React.ComponentProps<typeof Button>["size"]
}

export function ThemeToggle({ className, variant = "ghost", size = "icon" }: ThemeToggleProps) {
    const { setTheme, resolvedTheme } = useTheme()
    const [mounted, setMounted] = React.useState(false)

    React.useEffect(() => {
        setMounted(true)
    }, [])

    if (!mounted) {
        return (
            <Button size={size} variant={variant} aria-label="Loading theme switcher" className={className} disabled>
                <span className="sr-only">Loading theme switcher</span>
            </Button>
        )
    }

    const isDarkMode = resolvedTheme === "dark"
    const nextTheme = isDarkMode ? "light" : "dark"
    const icon = isDarkMode ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />

    return (
        <Button
            variant={variant}
            size={size}
            onClick={() => setTheme(nextTheme)}
            aria-label={`Switch to ${nextTheme} mode`}
            title={`Switch to ${nextTheme} mode`}
            className={className}
        >
            {icon}
            <span className="sr-only">Toggle theme</span>
        </Button>
    )
}
