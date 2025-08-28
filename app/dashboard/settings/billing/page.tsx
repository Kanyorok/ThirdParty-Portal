import BankDetailsForm from "@/components/thirdParty/bank-details"
import { Separator } from "@/components/common/separator"

export default function BillingDetails() {
    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">Billing Information</h2>
            </div>
            <Separator />
            <BankDetailsForm />
            <Separator />
        </div>
    )
}