"use client"

import * as React from "react"
import { Moon, Sun } from "lucide-react"
import { useTheme } from "next-themes"

import { Button } from "@/components/common/button"

export function ThemeToggle() {
    const { setTheme, resolvedTheme } = useTheme()
    const [mounted, setMounted] = React.useState(false)

    React.useEffect(() => {
        setMounted(true)
    }, [])

    if (!mounted) {
        return <Button size="icon" aria-label="Loading..." disabled />
    }

    const isDarkMode = resolvedTheme === "dark"
    const nextTheme = isDarkMode ? "light" : "dark"
    const currentIcon = isDarkMode ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />

    return (
        <Button
            variant="ghost"
            size="icon"
            onClick={() => setTheme(nextTheme)}
            aria-label={`Toggle theme to ${nextTheme}`}
            title={`Switch to ${nextTheme} mode`}
        >
            {currentIcon}
            <span className="sr-only">Toggle theme</span>
        </Button>
    )
}