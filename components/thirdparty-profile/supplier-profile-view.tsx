"use client"

import { Card } from "@/components/common/card"
import { Badge } from "@/components/common/badge"
import { Separator } from "@/components/common/separator"
import { CheckCircle2, Clock, ShieldCheck, Hash, AlertCircle } from "lucide-react"

interface SupplierProfileViewProps {
    supplierId?: string | null
    approvalStatus?: string | null
}

export function SupplierProfileView({ supplierId, approvalStatus }: SupplierProfileViewProps) {
    // Based on your backend response, approvalStatus "P" usually means Pending
    const isApproved = ["active", "approved", "a"].includes(approvalStatus?.toLowerCase() || "")
    const isPending = ["p", "pending"].includes(approvalStatus?.toLowerCase() || "")

    return (
        <div className="space-y-6">
            {/* Overview Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card className="p-6">
                    <div className="space-y-3">
                        <div className="flex items-center gap-2">
                            {isApproved ? (
                                <CheckCircle2 className="w-5 h-5 text-green-600" />
                            ) : isPending ? (
                                <Clock className="w-5 h-5 text-amber-500" />
                            ) : (
                                <AlertCircle className="w-5 h-5 text-muted-foreground" />
                            )}
                            <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Verification Status</p>
                        </div>
                        <p className="text-sm font-bold text-foreground">
                            {isApproved ? "Verified Active" : isPending ? "In Review" : "Unknown"}
                        </p>
                    </div>
                </Card>

                <Card className="p-6">
                    <div className="space-y-3">
                        <div className="flex items-center gap-2">
                            <ShieldCheck className="w-5 h-5 text-primary" />
                            <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Certification</p>
                        </div>
                        <p className="text-sm font-bold text-foreground">Pending Review</p>
                    </div>
                </Card>

                <Card className="p-6">
                    <div className="space-y-3">
                        <div className="flex items-center gap-2">
                            <Hash className="w-5 h-5 text-muted-foreground/50" />
                            <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Supplier ID</p>
                        </div>
                        <p className="text-sm font-bold text-foreground font-mono">{supplierId || "N/A"}</p>
                    </div>
                </Card>
            </div>

            <Separator className="opacity-50" />

            {/* Supplier Details */}
            <Card className="p-6">
                <h3 className="text-lg font-semibold mb-6">Supplier Information</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-2">Approval Status</p>
                        <Badge 
                            variant={isApproved ? "default" : isPending ? "secondary" : "outline"}
                            className={isApproved ? "bg-green-600 hover:bg-green-700" : ""}
                        >
                            {isApproved ? "Approved" : isPending ? "Pending Approval" : "Under Review"}
                        </Badge>
                    </div>
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-2">Categories</p>
                        <p className="text-sm text-foreground">To be configured</p>
                    </div>
                </div>
            </Card>
        </div>
    )
}