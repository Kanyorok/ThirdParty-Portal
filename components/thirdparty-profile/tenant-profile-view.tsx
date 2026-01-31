"use client"

import { Card } from "@/components/common/card"
import { Separator } from "@/components/common/separator"
import { Activity, Calendar, Layers } from "lucide-react"

export function TenantProfileView() {
  return (
    <div className="space-y-6">
      {/* Status Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <Layers className="w-5 h-5 text-primary" />
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Classification</p>
            </div>
            <p className="text-sm font-bold text-foreground">Residential</p>
          </div>
        </Card>

        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <Activity className="w-5 h-5 text-success" />
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Account Status</p>
            </div>
            <p className="text-sm font-bold text-foreground">Active</p>
          </div>
        </Card>

        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <Calendar className="w-5 h-5 text-muted-foreground/50" />
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Onboarded</p>
            </div>
            <p className="text-sm font-bold text-foreground text-nowrap">Jan 01, 2024</p>
          </div>
        </Card>
      </div>

      <Separator />

      {/* Tenant Details */}
      <Card className="p-6">
        <h3 className="text-lg font-semibold mb-6">Tenant Information</h3>
        <div className="space-y-4">
          <p className="text-muted-foreground text-sm">
            Additional tenant profile details will be displayed here as data becomes available.
          </p>
        </div>
      </Card>
    </div>
  )
}
