import BankDetailsForm from "@/components/thirdParty/bank-details"

export default function BillingDetails() {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-semibold tracking-tight text-foreground">Bank details</h2>
        <p className="mt-1 text-sm text-muted-foreground">Manage business settlement accounts.</p>
      </div>
      <BankDetailsForm />
    </div>
  )
}
