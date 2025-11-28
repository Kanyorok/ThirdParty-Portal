import React from "react"
import { Building2, Edit, Plus } from "lucide-react"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"

export interface DashboardHeaderProps {
    name?: string
    approvalStatus?: string
    status?: number
    onEdit: () => void
}

export const DashboardHeader: React.FC<DashboardHeaderProps> = ({ name, approvalStatus, status, onEdit }) => {
    const approvalBadge = (status?: string) => ({
        Approved: { label: "Approved", color: "bg-green-100 text-green-600" },
        Rejected: { label: "Rejected", color: "bg-red-100 text-red-600" },
        Pending: { label: "Pending", color: "bg-yellow-100 text-yellow-600" }
    }[status ?? "Pending"])

    const activeBadge = (status?: number) => status === 1
        ? { label: "Active", color: "bg-blue-100 text-blue-600" }
        : { label: "Inactive", color: "bg-gray-100 text-gray-600" }

    return (
        <header className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 border-b pb-4 gap-3">
            <div className="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                <h1 className="text-3xl font-bold tracking-tight text-foreground flex items-center gap-3">
                    <Building2 className="h-8 w-8 text-primary" /> {name || "Company Profile"}
                </h1>
                {approvalStatus !== undefined && status !== undefined && (
                    <div className="flex gap-2 mt-2 sm:mt-0">
                        <Badge className={`px-3 py-1 rounded-full font-semibold ${approvalBadge(approvalStatus)?.color}`}>
                            {approvalBadge(approvalStatus)?.label}
                        </Badge>
                        <Badge className={`px-3 py-1 rounded-full font-semibold ${activeBadge(status)?.color}`}>
                            {activeBadge(status)?.label}
                        </Badge>
                    </div>
                )}
            </div>
            <Button onClick={onEdit} variant="outline" className="text-primary hover:bg-primary/5">
                {name ? <Edit className="h-4 w-4 mr-2" /> : <Plus className="h-4 w-4 mr-2" />}
                {name ? "Edit Profile" : "Create Profile"}
            </Button>
        </header>
    )
}
