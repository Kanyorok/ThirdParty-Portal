"use client"

import { useMemo, useState } from "react"
import { format } from "date-fns"
import { ChevronLeft, ChevronRight, FilePlus2 } from "lucide-react"
import { Button } from "@/components/common/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Separator } from "@/components/common/separator"
import StatusBadge from "./status-badge"
import ApplicationForm from "./application-form"
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
    const [openRoundId, setOpenRoundId] = useState<string | null>(null)

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
                render: (r: Round) => <StatusBadge status={r.status} />,
            },
            {
                key: "window",
                label: "Application Window",
                render: (r: Round) => formatDateRange(r.startDate, r.endDate),
            },
            {
                key: "actions",
                label: "Actions",
                align: "right" as const,
                render: (r: Round) => (
                    <ApplicationForm
                        open={openRoundId === r.id}
                        onOpenChange={(o) => setOpenRoundId(o ? r.id : null)}
                        defaultRoundId={r.id}
                        onSuccess={({ applicationId, statusLabel }) =>
                            toast.success("Application submitted", {
                                description: `Reference: ${applicationId} • Status: ${statusLabel}`,
                            })
                        }
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            className="gap-2"
                            disabled={r.status !== "O"}
                        >
                            <FilePlus2 className="h-4 w-4" />
                            Click to Apply
                        </Button>
                    </ApplicationForm>
                ),
            },
        ],
        [openRoundId]
    )

    return (
        <div className="overflow-hidden rounded-b-xl">
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
                        {rounds.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="py-10 text-center text-muted-foreground">
                                    No rounds match your filters.
                                </TableCell>
                            </TableRow>
                        ) : (
                            rounds.map((r) => (
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
    )
}
