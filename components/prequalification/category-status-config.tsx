"use client"

import { ReactNode } from "react"
import { AlertTriangle, CheckCircle2, Clock, Eye, FileCheck, Plus, XCircle } from "lucide-react"
import { normalizeCategoryStatus } from "@/lib/rounds"

export type CategoryStatusConfig = {
    label: string
    icon: ReactNode
    variant: "default" | "outline" | "secondary" | "destructive"
    color: string
}

export function getCategoryStatusConfig(status?: string): CategoryStatusConfig {
    const normalized = normalizeCategoryStatus(status)

    switch (normalized) {
        case "NOT_APPLIED":
            return {
                label: "Not Applied",
                icon: <Plus className="w-3 h-3" />,
                variant: "outline",
                color: "bg-slate-50 text-slate-600 border-slate-200"
            }
        case "DRAFT":
            return {
                label: "Draft",
                icon: <FileCheck className="w-3 h-3" />,
                variant: "secondary",
                color: "bg-amber-50 text-amber-700 border-amber-200"
            }
        case "SUBMITTED":
            return {
                label: "Submitted",
                icon: <Clock className="w-3 h-3" />,
                variant: "default",
                color: "bg-blue-50 text-blue-700 border-blue-200"
            }
        case "UNDER_REVIEW":
            return {
                label: "Under Review",
                icon: <Eye className="w-3 h-3" />,
                variant: "default",
                color: "bg-purple-50 text-purple-700 border-purple-200"
            }
        case "APPROVED":
            return {
                label: "Prequalified",
                icon: <CheckCircle2 className="w-3 h-3" />,
                variant: "default",
                color: "bg-emerald-50 text-emerald-700 border-emerald-200"
            }
        case "REJECTED":
            return {
                label: "Failed",
                icon: <XCircle className="w-3 h-3" />,
                variant: "destructive",
                color: "bg-rose-50 text-rose-700 border-rose-200"
            }
        default:
            return {
                label: "Unknown",
                icon: <AlertTriangle className="w-3 h-3" />,
                variant: "secondary",
                color: "bg-slate-50 text-slate-600 border-slate-200"
            }
    }
}
