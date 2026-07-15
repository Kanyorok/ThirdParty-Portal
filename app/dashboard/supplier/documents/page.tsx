import { Suspense } from "react"
import { cookies } from "next/headers"
import AllDocuments from "@/components/documents/all-documents"
import { DocumentsLoadingState } from "@/components/documents/documents-loading-state"
import { Toaster } from "@/components/common/sonner"

export default async function Documents() {
    await cookies()

    return (
        <div className="w-full antialiased">
            <Toaster position="top-right" richColors closeButton />
            <Suspense fallback={<DocumentsLoadingState />}>
                <AllDocuments />
            </Suspense>
        </div>
    )
}
