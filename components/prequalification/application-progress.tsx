"use client"
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
    case 'DRAFT':
            return {
                label: 'Draft',
                icon: <FileCheck className="w-3 h-3" />,
                variant: 'secondary' as const,
                color: 'bg-gray-100 text-gray-800'
            };
    case 'SUBMITTED':
            return {
                label: 'Submitted',
                icon: <Clock className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-blue-100 text-blue-800'
            };
    case 'UNDER_REVIEW':
            return {
                label: 'Under Review',
                icon: <Eye className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-yellow-100 text-yellow-800'
            };
    case 'APPROVED':
            return {
                label: 'Approved',
                icon: <CheckCircle2 className="w-3 h-3" />,
                variant: 'default' as const,
                color: 'bg-green-100 text-green-800'
            };
    case 'REJECTED':
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
        <article className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between">
                <h4 className="text-sm font-semibold text-slate-900">{category.category_name}</h4>
                <Badge 
                    variant={config.variant}
                    className={cn("text-xs", config.color)}
                >
                    {config.icon}
                    {config.label}
                </Badge>
            </div>

            <div className="mt-3 space-y-2">
                <div className="flex items-center justify-between text-xs text-slate-600">
                    <span>{category.stage_label}</span>
                    <span>{category.progress_percent}%</span>
                </div>
                <Progress value={category.progress_percent} className="h-2" />
            </div>

            {category.updated_on && (
                <div className="mt-3 flex items-center gap-2 text-xs text-slate-600">
                    <Calendar className="w-3 h-3" />
                    <span>Updated {new Date(category.updated_on).toLocaleDateString()}</span>
                </div>
            )}

            {category.rejection_reason && (
                <div className="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-2 text-xs text-rose-800">
                    <strong>Reason:</strong> {category.rejection_reason}
                </div>
            )}
        </article>
    );
};

type AppSummary = {
    total_categories: number;
    approved_categories: number;
    rejected_categories: number;
    pending_categories: number;
    overall_progress: number;
}

const ProgressSummary = ({ 
    summary, 
    overallStatus 
}: { 
    summary: AppSummary;
    overallStatus: 'DRAFT' | 'SUBMITTED' | 'PARTIAL' | 'COMPLETE' | 'UNKNOWN';
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
        <div className="space-y-4 rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
            <div className="flex items-center gap-3">
                <Badge className={cn("text-xs", statusConfig.color)}>
                    {statusConfig.label}
                </Badge>
                <div className="flex items-center gap-2 text-sm text-slate-600">
                    <TrendingUp className="w-4 h-4" />
                    <span>{summary.overall_progress}% Overall Progress</span>
                </div>
            </div>

            <div className="grid grid-cols-2 gap-3 text-center md:grid-cols-4">
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                    <div className="text-lg font-semibold text-emerald-700">
                        {summary.approved_categories}
                    </div>
                    <div className="text-xs text-emerald-600">Approved</div>
                </div>
                <div className="rounded-xl border border-rose-200 bg-rose-50 p-3">
                    <div className="text-lg font-semibold text-rose-700">
                        {summary.rejected_categories}
                    </div>
                    <div className="text-xs text-rose-600">Rejected</div>
                </div>
                <div className="rounded-xl border border-amber-200 bg-amber-50 p-3">
                    <div className="text-lg font-semibold text-amber-700">
                        {summary.pending_categories}
                    </div>
                    <div className="text-xs text-amber-600">Pending</div>
                </div>
                <div className="rounded-xl border border-indigo-200 bg-indigo-50 p-3">
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
    // Build application progress view model from Round shape
    const categories: CategoryProgress[] = Array.isArray(round.categories) ? round.categories : [];
    const summaryFromRound = round.applicationSummary;
    // Compute basic counts if summary not provided
    const computed: AppSummary = summaryFromRound ?? categories.reduce<AppSummary>((acc, c) => {
        acc.total_categories += 1;
        if (c.status === 'APPROVED') acc.approved_categories += 1;
        else if (c.status === 'REJECTED') acc.rejected_categories += 1;
        else acc.pending_categories += 1;
        // average progress across categories when available
        acc.overall_progress += Number.isFinite(c.progress_percent) ? c.progress_percent : 0;
        return acc;
    }, { total_categories: 0, approved_categories: 0, rejected_categories: 0, pending_categories: 0, overall_progress: 0 });
    if (!summaryFromRound && computed.total_categories > 0) {
        computed.overall_progress = Math.round(computed.overall_progress / computed.total_categories);
    }
    const overallStatus: 'DRAFT' | 'SUBMITTED' | 'PARTIAL' | 'COMPLETE' | 'UNKNOWN' = (() => {
        if (computed.total_categories === 0) return 'UNKNOWN';
        if (computed.approved_categories === computed.total_categories) return 'COMPLETE';
        if (computed.approved_categories > 0 || computed.pending_categories > 0) return 'PARTIAL';
        return 'DRAFT';
    })();

    const applicationProgress = { categories, summary: computed, overall_status: overallStatus };
    const hasCategories = applicationProgress.categories.length > 0;

    // Simple progress bar for table view
    const SimpleProgressBar = () => (
        <div className="flex min-w-[140px] items-center gap-2 rounded-full border border-slate-200 bg-white px-2 py-1">
            <div className="h-2 flex-1 overflow-hidden rounded bg-gray-200 dark:bg-gray-800">
                <div 
                    className="h-2 bg-emerald-500 transition-all" 
                    style={{ width: `${Math.min(100, Math.max(0, applicationProgress.summary.overall_progress))}%` }} 
                />
            </div>
            <span className="w-10 text-right text-xs tabular-nums text-slate-700">
                {applicationProgress.summary.overall_progress}%
            </span>
        </div>
    );

    // Detailed progress dialog
    if (hasCategories) {
        return (
            <Dialog>
                <DialogTrigger asChild>
                    <Button variant="ghost" size="sm" className={cn("h-auto p-1 hover:bg-transparent", className)}>
                        <SimpleProgressBar />
                    </Button>
                </DialogTrigger>
                <DialogContent className="w-[min(100vw-2rem,1040px)] max-w-[1040px] overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-none">
                    <DialogHeader className="border-b border-slate-100 bg-white/95 px-6 py-4 pr-12 text-left backdrop-blur sm:px-8">
                        <DialogTitle className="text-xl font-semibold text-slate-900">
                            Application Progress - {round.title}
                        </DialogTitle>
                    </DialogHeader>
                    
                    <div className="max-h-[calc(100vh-10rem)] space-y-6 overflow-y-auto px-6 py-5 sm:px-8">
                        <ProgressSummary 
                            summary={applicationProgress.summary}
                            overallStatus={applicationProgress.overall_status}
                        />

                        <div className="space-y-4">
                            <h3 className="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Category Progress</h3>
                            <div className="grid gap-4">
                                {applicationProgress.categories.map((category: CategoryProgress) => (
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
