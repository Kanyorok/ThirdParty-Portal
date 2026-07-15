import { getDashboardData } from "@/lib/dashboard-summary-data"
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"

export default async function DashboardStats() {
    const data = await getDashboardData()

    if (!data) return <div>Unauthorized</div>

    return (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            <Card className="border-slate-200/80 bg-white/90 py-0">
                <CardHeader className="px-4 pt-4 pb-2">
                    <CardTitle className="text-sm font-semibold text-slate-900">Active requests</CardTitle>
                    <CardDescription className="text-xs text-slate-600">
                        Conversion driver: opportunities currently in motion.
                    </CardDescription>
                </CardHeader>
                <CardContent className="px-4 pb-4">
                    <p className="text-3xl font-semibold tracking-tight text-slate-900">
                        {Number(data.summary.activePreq ?? 0).toLocaleString()}
                    </p>
                </CardContent>
            </Card>

            <Card className="border-slate-200/80 bg-white/90 py-0">
                <CardHeader className="px-4 pt-4 pb-2">
                    <CardTitle className="text-sm font-semibold text-slate-900">Open tenders</CardTitle>
                    <CardDescription className="text-xs text-slate-600">
                        Top-of-funnel availability for new conversion.
                    </CardDescription>
                </CardHeader>
                <CardContent className="px-4 pb-4">
                    <p className="text-3xl font-semibold tracking-tight text-slate-900">
                        {Number(data.summary.openTenders ?? data.summary.tendersAvailable ?? 0).toLocaleString()}
                    </p>
                </CardContent>
            </Card>
        </div>
    )
}
