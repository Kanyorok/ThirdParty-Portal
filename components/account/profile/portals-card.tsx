"use client"

import { Building2 } from "lucide-react"

import { Button } from "@/components/common/button"

type PortalsCardProps = {
  profile: any
  onSwitchTo: (next: "Supplier" | "Tenant" | "Customer") => void
}

export default function PortalsCard({ profile, onSwitchTo }: PortalsCardProps) {
  return (
    <section className="border border-border/60 bg-background">
      <div className="border-b border-border/60 px-5 py-4">
        <h2 className="text-base font-semibold text-foreground">Portals</h2>
        <p className="text-sm text-muted-foreground">Quick access to your enabled portals.</p>
      </div>

      <div className="space-y-3 px-5 py-5">
        {(["Supplier", "Tenant", "Customer"] as const).map((key) => {
          const enabled =
            key === "Supplier"
              ? Boolean(profile?.isSupplier)
              : key === "Tenant"
                ? Boolean(profile?.isTenant)
                : Boolean(profile?.isCustomer)

          return (
            <div key={key} className="flex items-center gap-3">
              <div className="flex size-10 items-center justify-center rounded-xl bg-primary/10 border border-primary/20">
                <Building2 className="h-4 w-4 text-primary" />
              </div>
              <div className="min-w-0">
                <div className="text-sm font-semibold text-foreground">{key}</div>
                <div className="text-xs text-muted-foreground">{enabled ? "Enabled" : "Not enabled"}</div>
              </div>
              <Button
                type="button"
                disabled={!enabled}
                onClick={() => onSwitchTo(key)}
                className={
                  enabled
                    ? "ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
                    : "ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-muted text-muted-foreground shadow-none"
                }
              >
                Switch
              </Button>
            </div>
          )
        })}
      </div>
    </section>
  )
}
