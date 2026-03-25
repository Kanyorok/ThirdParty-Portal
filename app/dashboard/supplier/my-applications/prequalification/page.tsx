import { Suspense } from "react"
import MyApplicationsView from "@/components/prequalification/my-applications-view"
import { Toaster } from "@/components/common/sonner"

export default function Page() {
    return (
        <div className="w-full antialiased">
            <Toaster position="top-right" richColors closeButton />
            <Suspense fallback={<div className="flex items-center justify-center py-12 text-sm text-muted-foreground">Loading prequalification applications…</div>}>
                <MyApplicationsView
                    forcedFilter="prequalification"
                    breadcrumbLabel="Applications / Prequalification"
                    title="Prequalification applications"
                />
            </Suspense>
        </div>
    )
}
