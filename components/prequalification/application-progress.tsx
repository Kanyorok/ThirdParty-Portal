"use client"

import { useState } from "react"
import { CategoryProgress, Round } from "@/types/types"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Progress } from "@/components/common/progress"
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogTrigger 
} from "@/components/common/dialog"
import { 
    CheckCircle2, 
    XCircle, 
    Clock, 
    AlertTriangle, 
    FileCheck, 
    Eye,
    Calendar,
    TrendingUp
} from "lucide-react"
import { cn } from "@/lib/utils"

interface ApplicationProgressProps {
    round: Round;
    className?: string;
}

const getStatusConfig = (status: CategoryProgress['status']) => {
    switch (status) {
        case 'D': // Draft
            return {
                label: 'Draft',
                icon: <FileCheck className="w-3 h-3" />,
                variant: 'secondary' as const,
                color: 'bg-gray-100 text-gray-800'
            };
        case 'S': // Submitted
            return {
                label: 'Submitted',
                icon: <Clock className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-blue-100 text-blue-800'
            };
        case 'U': // Under Review
            return {
                label: 'Under Review',
                icon: <Eye className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-yellow-100 text-yellow-800'
            };
        case 'C': // Conditional
            return {
                label: 'Conditional',
                icon: <AlertTriangle className="w-3 h-3" />,
                variant: 'destructive' as const,
                color: 'bg-orange-100 text-orange-800'
            };
        case 'A': // Approved
            return {
                label: 'Approved',
                icon: <CheckCircle2 className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-green-100 text-green-800'
            };
        case 'R': // Rejected
            return {
                label: 'Rejected',
                icon: <XCircle className="w-3 h-3" />,
                variant: 'destructive' as const,
                color: 'bg-red-100 text-red-800'
            };
        default:
            return {
                label: 'Unknown',
                icon: <FileCheck className="w-3 h-3" />,
                variant: 'secondary' as const,
                color: 'bg-gray-100 text-gray-800'
            };
    }
};

const CategoryProgressCard = ({ category }: { category: CategoryProgress }) => {
    const config = getStatusConfig(category.status);
    
    return (
        <div className="border rounded-lg p-4 space-y-3">
            <div className="flex items-center justify-between">
                <h4 className="font-medium text-sm">{category.category_name}</h4>
                <Badge 
                    variant={config.variant}
                    className={cn("text-xs", config.color)}
                >
                    {config.icon}
                    {config.label}
                </Badge>
            </div>
            
            <div className="space-y-2">
                <div className="flex items-center justify-between text-xs text-muted-foreground">
                    <span>{category.stage_label}</span>
                    <span>{category.progress_percent}%</span>
                </div>
                <Progress value={category.progress_percent} className="h-2" />
            </div>

            {category.updated_on && (
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                    <Calendar className="w-3 h-3" />
                    <span>Updated {new Date(category.updated_on).toLocaleDateString()}</span>
                </div>
            )}

            {category.rejection_reason && (
                <div className="p-2 bg-red-50 border border-red-200 rounded text-xs text-red-800">
                    <strong>Reason:</strong> {category.rejection_reason}
                </div>
            )}
        </div>
    );
};

const ProgressSummary = ({ 
    summary, 
    overallStatus 
}: { 
    summary: Round['applicationProgress']['summary'];
    overallStatus: Round['applicationProgress']['overall_status'];
}) => {
    if (!summary) return null;

    const getOverallStatusConfig = (status: string) => {
        switch (status) {
            case 'DRAFT':
                return { label: 'Draft', color: 'bg-gray-100 text-gray-800' };
            case 'SUBMITTED':
                return { label: 'Submitted', color: 'bg-blue-100 text-blue-800' };
            case 'PARTIAL':
                return { label: 'Partially Approved', color: 'bg-yellow-100 text-yellow-800' };
            case 'COMPLETE':
                return { label: 'Complete', color: 'bg-green-100 text-green-800' };
            default:
                return { label: 'Unknown', color: 'bg-gray-100 text-gray-800' };
        }
    };

    const statusConfig = getOverallStatusConfig(overallStatus);

    return (
        <div className="space-y-4">
            <div className="flex items-center gap-3">
                <Badge className={cn("text-xs", statusConfig.color)}>
                    {statusConfig.label}
                </Badge>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <TrendingUp className="w-4 h-4" />
                    <span>{summary.overall_progress}% Overall Progress</span>
                </div>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div className="p-3 bg-green-50 rounded-lg">
                    <div className="text-lg font-semibold text-green-700">
                        {summary.approved_categories}
                    </div>
                    <div className="text-xs text-green-600">Approved</div>
                </div>
                <div className="p-3 bg-red-50 rounded-lg">
                    <div className="text-lg font-semibold text-red-700">
                        {summary.rejected_categories}
                    </div>
                    <div className="text-xs text-red-600">Rejected</div>
                </div>
                <div className="p-3 bg-yellow-50 rounded-lg">
                    <div className="text-lg font-semibold text-yellow-700">
                        {summary.pending_categories}
                    </div>
                    <div className="text-xs text-yellow-600">Pending</div>
                </div>
                <div className="p-3 bg-blue-50 rounded-lg">
                    <div className="text-lg font-semibold text-blue-700">
                        {summary.total_categories}
                    </div>
                    <div className="text-xs text-blue-600">Total</div>
                </div>
            </div>

            <Progress value={summary.overall_progress} className="h-3" />
        </div>
    );
};

export default function ApplicationProgress({ round, className }: ApplicationProgressProps) {
    if (!round.applicationProgress) {
        return (
            <span className={cn("text-xs text-muted-foreground", className)}>
                No application
            </span>
        );
    }

    const { applicationProgress } = round;
    const hasCategories = applicationProgress.categories.length > 0;

    // Simple progress bar for table view
    const SimpleProgressBar = () => (
        <div className="flex items-center gap-2 min-w-[120px]">
            <div className="flex-1 h-2 rounded bg-gray-200 dark:bg-gray-800 overflow-hidden">
                <div 
                    className="h-2 bg-emerald-500 transition-all" 
                    style={{ width: `${Math.min(100, Math.max(0, applicationProgress.summary.overall_progress))}%` }} 
                />
            </div>
            <span className="text-xs tabular-nums w-10 text-right">
                {applicationProgress.summary.overall_progress}%
            </span>
        </div>
    );

    // Detailed progress dialog
    if (hasCategories) {
        return (
            <Dialog>
                <DialogTrigger asChild>
                    <Button variant="ghost" size="sm" className={cn("h-auto p-1", className)}>
                        <SimpleProgressBar />
                    </Button>
                </DialogTrigger>
                <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Application Progress - {round.title}</DialogTitle>
                    </DialogHeader>
                    
                    <div className="space-y-6">
                        <ProgressSummary 
                            summary={applicationProgress.summary}
                            overallStatus={applicationProgress.overall_status}
                        />

                        <div className="space-y-4">
                            <h3 className="font-medium">Category Progress</h3>
                            <div className="grid gap-4">
                                {applicationProgress.categories.map((category) => (
                                    <CategoryProgressCard 
                                        key={category.category_id} 
                                        category={category} 
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        );
    }

    return <SimpleProgressBar />;
}


