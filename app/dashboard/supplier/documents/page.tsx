import { Suspense } from "react"
import { cookies } from "next/headers"
import AllDocuments from "@/components/documents/all-documents"
import { Toaster } from "@/components/common/sonner"

export default async function Documents() {
    await cookies()

    return (
        <div className="w-full antialiased">
            <Toaster position="top-right" richColors closeButton />
            <Suspense fallback={<div className="flex items-center justify-center py-12 text-sm text-muted-foreground">Loading documents…</div>}>
                <AllDocuments />
            </Suspense>
        </div>
    )
}
