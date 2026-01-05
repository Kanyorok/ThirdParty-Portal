import { Separator } from "@/components/common/separator"
import ThirdPartyDashboard from "@/components/thirdParty/third-party-dashboard"

export default function BusinessDetails() {
    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">Business Information</h2>
            </div>
            <Separator />
            <ThirdPartyDashboard />
            <Separator />
        </div>
    )
}