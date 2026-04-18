import { Metadata } from "next"
import { DocumentationViewer } from "@/components/help/documentation-viewer"

export const metadata: Metadata = {
  title: "Supplier Guide | Help Center",
  description: "Complete guide for prequalification, tenders, RFQs, and document management",
}

export default function SupplierGuidePage() {
  return (
    <DocumentationViewer
      filePath="/help/supplier-guide.md"
      title="Supplier Guide"
      backHref="/dashboard/help"
    />
  )
}
