import { Metadata } from "next"
import { DocumentationViewer } from "@/components/help/documentation-viewer"

export const metadata: Metadata = {
  title: "Tenant Guide | Help Center",
  description: "Complete guide for property management, leases, maintenance, and billing",
}

export default function TenantGuidePage() {
  return (
    <DocumentationViewer
      filePath="/help/tenant-guide.md"
      title="Tenant Guide"
      backHref="/dashboard/help"
    />
  )
}
