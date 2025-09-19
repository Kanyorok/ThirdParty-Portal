"use client"

import { useMemo, useState } from "react"
import { format } from "date-fns"
import { ChevronLeft, ChevronRight, FilePlus2, Lock } from "lucide-react"
import { Button } from "@/components/common/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Separator } from "@/components/common/separator"
import StatusBadge from "./status-badge"
import ApplicationForm from "./application-form"
import CategoryApplications from "./category-applications"
import { useSession } from "next-auth/react"
import { toast } from "sonner"
import { Round } from "@/types/types"

function formatDateRange(start: string, end: string) {
    return `${format(new Date(start), "MMM d, yyyy")} — ${format(new Date(end), "MMM d, yyyy")}`
}

export default function RoundsTable({
    rounds = [],
    total = 0,
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
    const [openRoundId, setOpenRoundId] = useState<string | null>(null)
    const [appliedRoundIds, setAppliedRoundIds] = useState<Set<string>>(new Set())
    // Removed submittingRoundId because we now always open the full application form (categories required)
    const [hideApplied, setHideApplied] = useState(false)

    const columns = useMemo(
    () => [
            {
                key: "title",
                label: "Round Name",
                render: (r: Round) => <span className="font-medium">{r.title}</span>,
            },
            {
                key: "status",
                label: "Round Status",
                render: (r: Round) => {
                    const value = typeof r.status === 'object' ? r.status.value : r.status
                    return <StatusBadge status={value === 'O' || value === 'CL' ? value as any : 'CL'} />
                },
            },
            {
                key: "window",
                label: "Application Window",
                render: (r: Round) => formatDateRange(r.startDate, r.endDate),
            },
            {
                key: 'categories',
                label: 'Category Applications',
                render: (r: Round) => {
                    return <CategoryApplications round={r} className="justify-start" />
                }
            },
            {
                key: "actions",
                label: "Actions",
                align: "right" as const,
                render: (r: Round) => {
                    // Check if user has applied to any category in this round
                    const appliedCategories = r.categories?.filter(cat => cat.has_applied) || [];
                    const hasAnyApplication = appliedCategories.length > 0 || appliedRoundIds.has(r.id);
                    const createdByOwner = (r as any).createdByOwner === true || (r as any).createdByOwner === 1;
                    
                    // Check if there are any categories available to apply to
                    const availableCategories = r.categories?.filter(cat => !cat.has_applied) || [];
                    const canApplyToMore = availableCategories.length > 0;
                    
                    const backendCanApply = (r as any).canApply !== undefined ? Boolean((r as any).canApply) : true;
                    
                    if (backendCanApply && hasAnyApplication && !canApplyToMore) {
                        return (
                            <span
                                title={`Applied to all categories (${appliedCategories.length})`}
                                aria-label={`Applied to all available categories`}
                                className="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800">
                                All Applied
                            </span>
                        )
                    }
                    
                    if (backendCanApply && hasAnyApplication && canApplyToMore) {
                        return (
                            <div className="flex flex-col gap-1">
                                <span className="text-xs text-green-600 font-medium">
                                    {appliedCategories.length} applied
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="h-7 px-2 text-xs"
                                    onClick={() => setOpenRoundId(r.id)}
                                    disabled={!accessToken}
                                    title={`Apply to ${availableCategories.length} more categories`}
                                >
                                    <FilePlus2 className="h-3 w-3 mr-1" />
                                    Apply More
                                </Button>
                            </div>
                        )
                    }
                    
                    if (!backendCanApply || createdByOwner) {
                        return (
                            <span className="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400" title={createdByOwner ? 'Owner created round' : 'Not eligible to apply'}>
                                <Lock className="h-3 w-3 mr-1" />
                                Not Eligible
                            </span>
                        )
                    }
                    return (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            className="gap-2"
                            aria-haspopup="dialog"
                            onClick={() => {
                                if (!accessToken) {
                                    toast.error('You must be signed in to apply.')
                                    return
                                }
                                setOpenRoundId(r.id)
                            }}
                        >
                            <FilePlus2 className="h-4 w-4" />
                            Apply
                        </Button>
                    )
                },
            },
        ],
        [openRoundId, appliedRoundIds]
    )

    const visibleRounds = hideApplied ? rounds.filter(r => !(r.hasApplied || appliedRoundIds.has(r.id))) : rounds

    return (
        <>
        <div className="overflow-hidden rounded-b-xl">
            <div className="flex items-center justify-end gap-3 p-3">
                <label className="flex items-center gap-2 text-xs font-medium cursor-pointer select-none">
                    <input type="checkbox" className="accent-emerald-600" checked={hideApplied} onChange={e => setHideApplied(e.target.checked)} />
                    Hide applied
                </label>
            </div>
            <div className="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            {columns.map((c) => (
                                <TableHead
                                    key={c.key}
                                    className={c.align === "right" ? "text-right" : undefined}
                                >
                                    {c.label}
                                </TableHead>
                            ))}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {visibleRounds.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="py-10 text-center text-muted-foreground">
                                    {hideApplied ? 'No unapplied rounds.' : 'No rounds match your filters.'}
                                </TableCell>
                            </TableRow>
                        ) : (
                            visibleRounds.map((r) => (
                                <TableRow key={r.id}>
                                    {columns.map((c) => (
                                        <TableCell
                                            key={c.key}
                                            className={c.align === "right" ? "text-right" : undefined}
                                        >
                                            {c.render(r)}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>

            <Separator />

            <div className="flex items-center justify-between p-3 text-sm text-muted-foreground">
                <div>
                    Page {page} of {totalPages} • {total.toLocaleString()} total
                </div>
                <div className="flex items-center gap-2">
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        disabled={page <= 1}
                    >
                        <a href={`?page=${Math.max(1, page - 1)}&pageSize=${pageSize}&sortBy=${sortBy}&sortOrder=${sortOrder}`}>
                            <ChevronLeft className="mr-2 h-4 w-4" />
                            Previous
                        </a>
                    </Button>
                    <Button
                        asChild
                        variant="outline"
                        size="sm"
                        disabled={page >= totalPages}
                    >
                        <a href={`?page=${Math.min(totalPages, page + 1)}&pageSize=${pageSize}&sortBy=${sortBy}&sortOrder=${sortOrder}`}>
                            Next
                            <ChevronRight className="ml-2 h-4 w-4" />
                        </a>
                    </Button>
                </div>
            </div>
        </div>
        {openRoundId ? (
            <ApplicationForm
                open={true}
                defaultRoundId={openRoundId || undefined}
                onOpenChange={(o) => { if (!o) setOpenRoundId(null) }}
                onSuccess={({ roundId, applicationId }) => {
                    setAppliedRoundIds(prev => new Set(prev).add(roundId))
                    toast.success('Application submitted', { description: applicationId ? `Reference: ${applicationId}` : undefined })
                    setOpenRoundId(null)
                }}
            >
                <span />
            </ApplicationForm>
        ) : null}
        </>
    )
}
