import { Metadata } from "next"
import { DocumentationViewer } from "@/components/help/documentation-viewer"

export const metadata: Metadata = {
  title: "Customer Guide | Help Center",
  description: "Complete guide for insurance policies, claims, payments, and renewals",
}

export default function CustomerGuidePage() {
  return (
    <DocumentationViewer
      filePath="/help/customer-guide.md"
      title="Customer Guide"
      backHref="/dashboard/help"
    />
  )
}
