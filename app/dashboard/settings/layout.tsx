"use client"

import type React from "react"

interface SettingsLayoutProps {
    children: React.ReactNode
}

export default function SettingsLayout({ children }: SettingsLayoutProps) {
    return (
        <div className="dashboard-readable w-full min-h-screen text-slate-900 antialiased">
            <div className="max-w-[1600px] mx-auto px-4 py-6 md:px-8 md:py-8">
                {children}
            </div>
        </div>
    )
}
