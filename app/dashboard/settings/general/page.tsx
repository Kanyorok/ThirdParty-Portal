import { Separator } from "@/components/common/separator"
import { GeneralSettings } from "@/components/thirdParty/general-settings"

export default function GeneralSettingsPage() {
    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-xl font-semibold tracking-tight">
                    General
                </h2>
            </div>
            <Separator />
            <GeneralSettings />
            <Separator />
        </div>
    )
}