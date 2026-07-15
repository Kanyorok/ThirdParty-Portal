import type { ReactNode } from "react"

export default function MyApplicationsLayout({ children }: { children: ReactNode }) {
    return (
        <div className="dashboard-readable w-full antialiased">{children}</div>
    )
}
