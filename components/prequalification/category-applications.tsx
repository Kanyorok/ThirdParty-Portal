"use client"

import { useState } from "react"
import { RoundCategory, Round } from "@/types/types"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
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
    TrendingUp,
    Plus
} from "lucide-react"
import { cn } from "@/lib/utils"

interface CategoryApplicationsProps {
    round: Round;
    className?: string;
}

const getCategoryStatusConfig = (status: RoundCategory['status']) => {
    switch (status) {
        case 'NOT_APPLIED':
            return {
                label: 'Not Applied',
                icon: <Plus className="w-3 h-3" />,
                variant: 'outline' as const,
                color: 'bg-slate-50 text-slate-600 border-slate-200'
            };
        case 'DRAFT':
            return {
                label: 'Draft',
                icon: <FileCheck className="w-3 h-3" />,
                variant: 'secondary' as const,
                color: 'bg-amber-50 text-amber-700 border-amber-200'
            };
        case 'SUBMITTED':
            return {
                label: 'Submitted',
                icon: <Clock className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-blue-50 text-blue-700 border-blue-200'
            };
        case 'UNDER_REVIEW':
            return {
                label: 'Under Review',
                icon: <Eye className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-purple-50 text-purple-700 border-purple-200'
            };
        case 'APPROVED':
            return {
                label: 'Approved',
                icon: <CheckCircle2 className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-emerald-50 text-emerald-700 border-emerald-200'
            };
        case 'REJECTED':
            return {
                label: 'Rejected',
                icon: <XCircle className="w-3 h-3" />,
                variant: 'destructive' as const,
                color: 'bg-rose-50 text-rose-700 border-rose-200'
            };
        default:
            return {
                label: 'Unknown',
                icon: <AlertTriangle className="w-3 h-3" />,
                variant: 'secondary' as const,
                color: 'bg-slate-50 text-slate-600 border-slate-200'
            };
    }
};

const CategoryProgressBar = ({ category }: { category: RoundCategory }) => (
    <div className="flex items-center gap-2 min-w-[80px]">
        <div className="flex-1 h-2 rounded bg-slate-200 dark:bg-slate-800 overflow-hidden">
            <div 
                className="h-2 bg-emerald-500 transition-all" 
                style={{ width: `${Math.min(100, Math.max(0, category.progress_percent))}%` }} 
            />
        </div>
        <span className="text-xs tabular-nums w-8 text-right text-slate-600">
            {category.progress_percent}%
        </span>
    </div>
);

const CategoryDetailView = ({ categories }: { categories: RoundCategory[] }) => {
    return (
        <div className="space-y-4">
            {categories.map((category) => {
                const statusConfig = getCategoryStatusConfig(category.status);
                
                return (
                    <div 
                        key={category.category_id} 
                        className="rounded-xl border border-slate-200 bg-white p-4 space-y-3"
                    >
                        <div className="flex items-start justify-between">
                            <div className="space-y-1">
                                <h4 className="font-medium text-sm">
                                    {category.category_name}
                                </h4>
                                {category.category_description && (
                                    <p className="text-xs text-slate-600">
                                        {category.category_description}
                                    </p>
                                )}
                            </div>
                            <Badge className={cn("text-xs", statusConfig.color)}>
                                {statusConfig.icon}
                                <span className="ml-1">{statusConfig.label}</span>
                            </Badge>
                        </div>
                        
                        {category.has_applied && (
                            <div className="space-y-2">
                                <CategoryProgressBar category={category} />
                                
                                <div className="grid grid-cols-2 gap-4 text-xs text-slate-600">
                                    {category.application_date && (
                                        <div className="flex items-center gap-1">
                                            <Calendar className="w-3 h-3" />
                                            <span>Applied: {new Date(category.application_date).toLocaleDateString()}</span>
                                        </div>
                                    )}
                                    {category.updated_on && (
                                        <div className="flex items-center gap-1">
                                            <TrendingUp className="w-3 h-3" />
                                            <span>Updated: {new Date(category.updated_on).toLocaleDateString()}</span>
                                        </div>
                                    )}
                                </div>
                                
                                {category.stage_label && (
                                    <div className="text-xs">
                                        <span className="font-medium">Current Stage:</span> {category.stage_label}
                                    </div>
                                )}
                                
                                {category.rejection_reason && (
                                    <div className="rounded border border-rose-200 bg-rose-50 p-2 text-xs text-rose-700">
                                        <span className="font-medium">Rejection Reason:</span> {category.rejection_reason}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                );
            })}
        </div>
    );
};

export default function CategoryApplications({ round, className }: CategoryApplicationsProps) {
    const [isOpen, setIsOpen] = useState(false);
    
    if (!round.categories || round.categories.length === 0) {
        return (
            <span className={cn("text-xs text-slate-500", className)}>
                No categories
            </span>
        );
    }

    // Deduplicate categories by id to avoid duplicates in UI
    const uniqueCategories = Array.from(new Map(round.categories.map(c => [c.category_id, c])).values());
    const appliedCategories = uniqueCategories.filter(cat => cat.has_applied);
    const totalCategories = uniqueCategories.length;
    
    if (appliedCategories.length === 0) {
        return (
            <span className={cn("text-xs text-slate-500", className)}>
                Not applied
            </span>
        );
    }

    // Simple summary for table view
    const SimpleSummary = () => (
        <div className="flex items-center gap-2">
            <span className="text-xs font-medium">
                {appliedCategories.length}/{totalCategories} applied
            </span>
            {appliedCategories.length > 0 && (
                <div className="flex gap-1">
                    {appliedCategories.slice(0, 2).map((category) => {
                        const statusConfig = getCategoryStatusConfig(category.status);
                        return (
                            <Badge 
                                key={category.category_id}
                                className={cn("text-xs px-1 py-0 h-5", statusConfig.color)}
                            >
                                {category.category_name.substring(0, 3)}
                            </Badge>
                        );
                    })}
                    {appliedCategories.length > 2 && (
                        <Badge className="text-xs px-1 py-0 h-5 bg-slate-100 text-slate-600 border border-slate-200">
                            +{appliedCategories.length - 2}
                        </Badge>
                    )}
                </div>
            )}
        </div>
    );

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <DialogTrigger asChild>
                <Button 
                    variant="ghost" 
                    size="sm" 
                    className={cn("h-auto p-1 font-medium justify-start hover:bg-transparent text-slate-700", className)}
                >
                    <SimpleSummary />
                </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <span>Category Applications</span>
                        <Badge variant="outline" className="text-xs">
                            {round.title}
                        </Badge>
                    </DialogTitle>
                </DialogHeader>
                
                <div className="space-y-4">
                    {round.applicationSummary && (
                        <div className="bg-slate-50 rounded-xl border border-slate-200 p-3 space-y-2">
                            <h4 className="font-medium text-sm">Summary</h4>
                            <div className="grid grid-cols-2 gap-3 text-xs">
                                <div>Applied: {round.applicationSummary.applied_categories}/{round.applicationSummary.total_categories}</div>
                                <div>Approved: {round.applicationSummary.approved_categories}</div>
                                <div>Rejected: {round.applicationSummary.rejected_categories}</div>
                                <div>Pending: {round.applicationSummary.pending_categories}</div>
                            </div>
                        </div>
                    )}
                    
                    <CategoryDetailView categories={uniqueCategories} />
                </div>
            </DialogContent>
        </Dialog>
    );
}








