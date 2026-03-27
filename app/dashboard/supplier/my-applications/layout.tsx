import type { ReactNode } from "react"

export default function MyApplicationsLayout({ children }: { children: ReactNode }) {
    return (
        <div className="w-full antialiased">{children}</div>
    )
}
