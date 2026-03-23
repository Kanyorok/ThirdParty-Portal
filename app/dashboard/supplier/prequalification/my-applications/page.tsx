import { Suspense } from "react"
import { cookies } from "next/headers"
import MyApplicationsView from "@/components/prequalification/my-applications-view"
import { Toaster } from "@/components/common/sonner"

export default async function Page() {
    await cookies()

    return (
        <div className="w-full antialiased">
            <Toaster position="top-right" richColors closeButton />
            <Suspense fallback={<div className="flex items-center justify-center py-12 text-sm text-muted-foreground">Loading applications…</div>}>
                <MyApplicationsView />
            </Suspense>
        </div>
    )
}
