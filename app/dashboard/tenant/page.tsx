import { redirect } from "next/navigation"

export const dynamic = "force-dynamic"

export default async function TenantDashboardPage() {
    redirect("/dashboard?profile=Tenant")
}
