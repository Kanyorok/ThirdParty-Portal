"use client"

import { useMemo, useState } from "react"
import { format } from "date-fns"
import { ChevronLeft, ChevronRight, FilePlus2, Lock } from "lucide-react"
import { Button } from "@/components/common/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Separator } from "@/components/common/separator"
import StatusBadge from "./status-badge"
import ApplicationForm from "./application-form"
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
                key: 'progress',
                label: 'Progress',
                render: (r: Round) => {
                    if (!r.applicationProgress) return <span className="text-xs text-muted-foreground">—</span>
                    const pct = r.applicationProgress.percent ?? 0
                    return (
                        <div className="flex items-center gap-2 min-w-[120px]">
                            <div className="flex-1 h-2 rounded bg-gray-200 dark:bg-gray-800 overflow-hidden">
                                <div className="h-2 bg-emerald-500 transition-all" style={{ width: `${Math.min(100, Math.max(0, pct))}%` }} />
                            </div>
                            <span className="text-xs tabular-nums w-10 text-right">{pct}%</span>
                        </div>
                    )
                }
            },
            {
                key: "actions",
                label: "Actions",
                align: "right" as const,
                render: (r: Round) => {
                    const hasApplied = r.hasApplied || appliedRoundIds.has(r.id) || !!r.applicationId
                    const createdByOwner = (r as any).createdByOwner === true || (r as any).createdByOwner === 1
                    const backendCanApply = (r as any).canApply !== undefined ? Boolean((r as any).canApply) : !hasApplied
                    const effectiveCanApply = backendCanApply && !hasApplied && !createdByOwner
                    if (hasApplied) {
                        return (
                            <span
                                title={`Application ID: ${r.applicationId || 'Pending'}${r.applicationProgress?.label ? ' • ' + r.applicationProgress.label : ''}`}
                                aria-label={`Applied. Application ID ${r.applicationId || 'pending'}`}
                                className="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800">
                                Applied
                            </span>
                        )
                    }
                    if (!effectiveCanApply) {
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
