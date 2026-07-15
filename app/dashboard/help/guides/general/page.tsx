import { Metadata } from "next"
import { DocumentationViewer } from "@/components/help/documentation-viewer"

export const metadata: Metadata = {
  title: "General Portal Guide | Help Center",
  description: "Complete guide for navigation, dashboard, settings, and common features",
}

export default function GeneralGuidePage() {
  return (
    <DocumentationViewer
      filePath="/help/general-guide.md"
      title="General Portal Guide"
      backHref="/dashboard/help"
    />
  )
}
