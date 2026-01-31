"use client"

import { Card } from "@/components/common/card"
import { Skeleton } from "@/components/common/skeleton"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Building2, FileText } from "lucide-react"
import type { ThirdPartyDetails } from "@/lib/api/profile-management"

interface PartyDetailsViewProps {
    details: ThirdPartyDetails | null | undefined
    isLoading?: boolean
}

export function PartyDetailsView({ details, isLoading }: PartyDetailsViewProps) {
    if (isLoading) {
        return (
            <Card className="p-6">
                <div className="space-y-4">
                    <Skeleton className="h-8 w-32" />
                    <div className="space-y-2">
                        <Skeleton className="h-4 w-full" />
                        <Skeleton className="h-4 w-3/4" />
                    </div>
                </div>
            </Card>
        )
    }

    const registrationNumber = details?.registrationNumber
    const taxPIN = details?.taxPIN

    return (
        <div className="space-y-6">
            {details && (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Card className="p-5 border-border/40 bg-card hover:border-border/60 transition-colors">
                        <div className="flex items-start gap-3">
                            <div className="p-2 bg-primary/8 rounded-lg">
                                <Building2 className="w-4 h-4 text-primary" strokeWidth={2} />
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-[10px] text-muted-foreground/50 font-semibold uppercase tracking-wide mb-0.5">
                                    Company Name
                                </p>
                                <p className="text-sm font-bold text-foreground">{details.thirdPartyName}</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-5 border-border/40 bg-card hover:border-border/60 transition-colors">
                        <div className="flex items-start gap-3">
                            <div className="p-2 bg-primary/8 rounded-lg">
                                <FileText className="w-4 h-4 text-primary" strokeWidth={2} />
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-[10px] text-muted-foreground/50 font-semibold uppercase tracking-wide mb-0.5">
                                    Registration
                                </p>
                                <p className="text-sm font-mono font-bold text-foreground">{registrationNumber || "—"}</p>
                            </div>
                        </div>
                    </Card>
                </div>
            )}

            <Card className="p-6 border-border/40">
                <h3 className="text-sm font-bold text-foreground mb-5 uppercase tracking-wide">Bank Details</h3>
                <div className="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow className="border-b border-border/40 hover:bg-transparent">
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60 py-3">
                                    #
                                </TableHead>
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60">
                                    Name
                                </TableHead>
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60">
                                    Dated
                                </TableHead>
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60 text-right">
                                    Action
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow className="border-b border-border/30 hover:bg-muted/30">
                                <TableCell colSpan={4} className="text-center text-muted-foreground/60 py-10 text-xs">
                                    No bank details added
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </Card>

            {(registrationNumber || taxPIN) && (
                <Card className="p-6 border-border/40">
                    <h3 className="text-sm font-bold text-foreground mb-5 uppercase tracking-wide">Registration Details</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {registrationNumber && (
                            <div className="space-y-2">
                                <p className="text-[10px] text-muted-foreground/50 font-semibold uppercase tracking-widest">
                                    Registration Number
                                </p>
                                <p className="text-sm font-mono font-bold text-foreground">{registrationNumber}</p>
                            </div>
                        )}
                        {taxPIN && (
                            <div className="space-y-2">
                                <p className="text-[10px] text-muted-foreground/50 font-semibold uppercase tracking-widest">Tax PIN</p>
                                <p className="text-sm font-mono font-bold text-foreground">{taxPIN}</p>
                            </div>
                        )}
                    </div>
                </Card>
            )}

            <Card className="p-6 border-border/40">
                <h3 className="text-sm font-bold text-foreground mb-5 uppercase tracking-wide">Users</h3>
                <div className="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow className="border-b border-border/40 hover:bg-transparent">
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60 py-3">
                                    #
                                </TableHead>
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60">
                                    Name
                                </TableHead>
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60">
                                    Email
                                </TableHead>
                                <TableHead className="font-semibold text-[10px] uppercase tracking-widest text-muted-foreground/60 text-right">
                                    Action
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow className="border-b border-border/30 hover:bg-muted/30">
                                <TableCell colSpan={4} className="text-center text-muted-foreground/60 py-10 text-xs">
                                    No users assigned
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </Card>
        </div>
    )
}