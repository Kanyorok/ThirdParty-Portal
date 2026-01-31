"use client"

import { Card } from "@/components/common/card"
import { AlertCircle } from "lucide-react"

export function SupplierProfileSection() {
    return (
        <Card className="border-border/50 shadow-sm p-8">
            <div className="flex items-center justify-center py-12 text-center">
                <div>
                    <AlertCircle className="w-12 h-12 text-muted-foreground mx-auto mb-4 opacity-50" />
                    <h3 className="text-lg font-semibold text-foreground mb-2">Supplier Profile</h3>
                    <p className="text-muted-foreground text-sm">No supplier profile data available</p>
                </div>
            </div>
        </Card>
    )
}
