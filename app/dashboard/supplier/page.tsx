import { redirect } from "next/navigation"

export const dynamic = "force-dynamic"

export default async function SupplierDashboardPage() {
    redirect("/dashboard?profile=Supplier")
}
