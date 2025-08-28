import ThirdPartyDetailsForm from "@/components/thirdParty/third-party-info";
import { Separator } from "@/components/common/separator"

export default function BusinessDetails() {
    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">Business Information</h2>
            </div>
            <Separator />
            <ThirdPartyDetailsForm />
            <Separator />
        </div>
    )
}