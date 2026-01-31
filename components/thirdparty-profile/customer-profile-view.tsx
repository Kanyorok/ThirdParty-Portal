"use client"

import { Card } from "@/components/common/card"
import { Separator } from "@/components/common/separator"
import { Calendar, User2, Heart, Briefcase, Clock } from "lucide-react"

export function CustomerProfileView() {
  return (
    <div className="space-y-6">
      {/* Customer Details */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <Calendar className="w-5 h-5 text-muted-foreground/50" />
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Date of Birth</p>
            </div>
            <p className="text-sm font-bold text-foreground">N/A</p>
          </div>
        </Card>

        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <User2 className="w-5 h-5 text-muted-foreground/50" />
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Gender</p>
            </div>
            <p className="text-sm font-bold text-foreground">N/A</p>
          </div>
        </Card>

        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <Heart className="w-5 h-5 text-muted-foreground/50" />
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Marital Status</p>
            </div>
            <p className="text-sm font-bold text-foreground">N/A</p>
          </div>
        </Card>

        <Card className="p-6">
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <Briefcase className="w-5 h-5 text-muted-foreground/50" />
              <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Occupation</p>
            </div>
            <p className="text-sm font-bold text-foreground">N/A</p>
          </div>
        </Card>
      </div>

      <Separator />

      {/* Customer Info */}
      <Card className="p-6">
        <h3 className="text-lg font-semibold mb-6">Customer Profile</h3>
        <div className="flex items-center gap-3 text-muted-foreground text-sm">
          <Clock className="w-4 h-4" />
          <span>Member since Jan 01, 2024</span>
        </div>
      </Card>
    </div>
  )
}
