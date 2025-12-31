"use client"

import { useMemo, useState } from "react"
import { useRouter } from "next/navigation"
import { format } from "date-fns"
import { ChevronLeft, ChevronRight, FilePlus2, Lock, CheckCircle2 } from "lucide-react"
import { Button } from "@/components/common/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Checkbox } from "@/components/common/checkbox"
import StatusBadge from "./status-badge"
import ApplicationForm from "./application-form"
import CategoryApplications from "./category-applications"
import { useSession } from "next-auth/react"
import { toast } from "sonner"
import { Round } from "@/types/types"
import { cn } from "@/lib/utils"

function formatDateRange(start: string, end: string) {
    return (
        <div className="flex flex-col gap-0.5">
            <span className="text-[10px] font-bold uppercase text-muted-foreground/50 leading-none">Window</span>
            <span className="text-xs font-bold tabular-nums">
                {format(new Date(start), "MMM d")} — {format(new Date(end), "MMM d, yyyy")}
            </span>
        </div>
    )
}

export default function RoundsTable({
    rounds = [],
    page = 1,
    pageSize = 10,
    totalPages = 1,
    sortBy = "startDate",
    sortOrder = "asc",
}: {
    rounds?: Round[]
    total?: number
    page?: number
    pageSize?: number
    totalPages?: number
    sortBy?: string
    sortOrder?: "asc" | "desc"
}) {
    const { data: session } = useSession()
    const accessToken = session?.accessToken as string | undefined
    const router = useRouter()
    const [openRoundId, setOpenRoundId] = useState<string | null>(null)
    const [appliedRoundIds, setAppliedRoundIds] = useState<Set<string>>(new Set())
    const [hideApplied, setHideApplied] = useState(false)

    const columns = useMemo(
        () => [
            {
                key: "title",
                label: "Round Details",
                render: (r: Round) => (
                    <div className="flex flex-col gap-1">
                        <span className="text-xs font-black uppercase tracking-tight leading-tight">{r.title}</span>
                        <div className="flex items-center gap-2">
                            <StatusBadge status={typeof r.status === 'object' ? (r.status.value as any) : (r.status as any)} />
                        </div>
                    </div>
                ),
            },
            {
                key: "window",
                label: "Timeline",
                render: (r: Round) => formatDateRange(r.startDate, r.endDate),
            },
            {
                key: 'categories',
                label: 'Status/Progress',
                render: (r: Round) => <CategoryApplications round={r} className="justify-start scale-90 origin-left" />
            },
            {
                key: "actions",
                label: "Action",
                align: "right" as const,
                render: (r: Round) => {
                    const appliedCategories = r.categories?.filter(cat => cat.has_applied) || [];
                    const hasAnyApplication = appliedCategories.length > 0 || appliedRoundIds.has(r.id);
                    const supplierEligible = r.supplierEligible === false ? false : (r.supplierEligible ?? true);
                    const isClosed = Boolean(r.isClosed);
                    const isExpired = Boolean(r.isExpired);
                    const windowOpen = r.windowOpen !== undefined ? Boolean(r.windowOpen) : true;
                    const isFutureWindow = Boolean(r.isFutureWindow);
                    const duplicateWithinRange = Boolean(r.duplicateWithinRange);
                    const availableCategories = r.categories?.filter(cat => !cat.has_applied) || [];
                    const canApplyToMore = availableCategories.length > 0;
                    const backendCanApply = r.canApply !== undefined ? Boolean(r.canApply) : undefined;
                    const effectiveCanApply = backendCanApply !== undefined ? backendCanApply : true;

                    if (!supplierEligible) {
                        return (
                            <div className="flex items-center justify-end gap-1.5 text-[10px] font-black uppercase text-muted-foreground/40">
                                <Lock className="h-3 w-3" />
                                <span>Ineligible</span>
                            </div>
                        )
                    }

                    if (isExpired || isClosed || !windowOpen || isFutureWindow || duplicateWithinRange || !effectiveCanApply) {
                        return (
                            <div className="flex items-center justify-end gap-1.5 text-[10px] font-black uppercase text-muted-foreground/40">
                                <Lock className="h-3 w-3" />
                                <span>{isExpired ? 'Expired' : isClosed ? 'Closed' : 'Locked'}</span>
                            </div>
                        )
                    }

                    if (effectiveCanApply && hasAnyApplication && !canApplyToMore) {
                        return (
                            <div className="flex items-center justify-end gap-1.5 text-[10px] font-black uppercase text-primary">
                                <CheckCircle2 className="h-3.5 w-3.5" />
                                <span>Complete</span>
                            </div>
                        )
                    }

                    return (
                        <Button
                            variant={hasAnyApplication ? "secondary" : "default"}
                            size="sm"
                            className={cn(
                                "h-8 px-4 text-[10px] font-black uppercase tracking-widest transition-all",
                                !hasAnyApplication && "bg-primary hover:bg-primary/90 shadow-sm"
                            )}
                            onClick={() => {
                                if (!accessToken) {
                                    toast.error('Sign in required')
                                    return
                                }
                                setOpenRoundId(r.id)
                            }}
                        >
                            <FilePlus2 className="mr-1.5 h-3 w-3" />
                            {hasAnyApplication ? "Add More" : "Apply"}
                        </Button>
                    )
                },
            },
        ],
        [appliedRoundIds, accessToken]
    )

    const visibleRounds = hideApplied ? rounds.filter(r => !(Boolean(r.hasApplied) || appliedRoundIds.has(r.id))) : rounds

    return (
        <div className="w-full">
            <div className="flex items-center justify-end gap-2 bg-muted/20 px-6 py-3 border-y border-muted/50">
                <Checkbox
                    id="hide-applied"
                    checked={hideApplied}
                    onCheckedChange={(v) => setHideApplied(!!v)}
                    className="h-3.5 w-3.5 border-2"
                />
                <label htmlFor="hide-applied" className="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 cursor-pointer">
                    Hide applied rounds
                </label>
            </div>

            <Table>
                <TableHeader className="bg-muted/10">
                    <TableRow className="hover:bg-transparent border-none">
                        {columns.map((c) => (
                            <TableHead key={c.key} className={cn(
                                "h-10 text-[10px] font-black uppercase tracking-widest text-muted-foreground/40 px-6",
                                c.align === "right" && "text-right"
                            )}>
                                {c.label}
                            </TableHead>
                        ))}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {visibleRounds.map((r) => (
                        <TableRow key={r.id} className="group border-muted/40 hover:bg-muted/5 transition-colors">
                            {columns.map((c) => (
                                <TableCell key={c.key} className={cn(
                                    "py-4 px-6",
                                    c.align === "right" && "text-right"
                                )}>
                                    {c.render(r)}
                                </TableCell>
                            ))}
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            <div className="flex items-center justify-between px-6 py-4 border-t-2 border-muted bg-muted/5">
                <p className="text-[10px] font-black uppercase tracking-widest text-muted-foreground/50">
                    Page {page} <span className="mx-1 text-muted-foreground/20">/</span> {totalPages}
                </p>
                <div className="flex items-center gap-1">
                    <Button variant="ghost" size="sm" className="h-8 text-[10px] font-bold uppercase tracking-tighter" disabled={page <= 1} asChild>
                        <a href={`?page=${Math.max(1, page - 1)}&pageSize=${pageSize}&sortBy=${sortBy}&sortOrder=${sortOrder}`}>
                            <ChevronLeft className="mr-1 h-3 w-3" /> Prev
                        </a>
                    </Button>
                    <Button variant="ghost" size="sm" className="h-8 text-[10px] font-bold uppercase tracking-tighter" disabled={page >= totalPages} asChild>
                        <a href={`?page=${Math.min(totalPages, page + 1)}&pageSize=${pageSize}&sortBy=${sortBy}&sortOrder=${sortOrder}`}>
                            Next <ChevronRight className="ml-1 h-3 w-3" />
                        </a>
                    </Button>
                </div>
            </div>

            {openRoundId && (
                <ApplicationForm
                    open={true}
                    defaultRoundId={openRoundId}
                    onOpenChange={(o) => { if (!o) setOpenRoundId(null) }}
                    onSuccess={({ roundId }) => {
                        setAppliedRoundIds(prev => new Set(prev).add(roundId))
                        router.refresh()
                        setOpenRoundId(null)
                    }}
                >
                    <span className="hidden" />
                </ApplicationForm>
            )}
        </div>
    )
}