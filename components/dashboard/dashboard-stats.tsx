import { getDashboardData } from "@/lib/dashboard-summary-data"

export default async function DashboardStats() {
    const data = await getDashboardData()

    if (!data) return <div>Unauthorized</div>

    return (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="p-4 border rounded shadow">
                <h3>Active Requests</h3>
                <p className="text-2xl font-bold">{data.summary.activePreq}</p>
            </div>
            <div className="p-4 border rounded shadow">
                <h3>Tenders Available</h3>
                <p className="text-2xl font-bold">{data.summary.tendersAvailable}</p>
            </div>
        </div>
    )
}