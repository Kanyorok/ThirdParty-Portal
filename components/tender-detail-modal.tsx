"use client"

import {
    X,
    Calendar,
    Hash,
    Building2,
    FileText,
    ShieldCheck,
    Clock,
    ArrowRight,
} from "lucide-react"
import { format } from "date-fns"

import {
    Dialog,
    DialogContent,
    DialogTitle,
} from "@/components/common/dialog"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"

interface TenderDetailModalProps {
    isOpen: boolean
    onClose: () => void
    tender: any
}

export default function TenderDetailModal({
    isOpen,
    onClose,
    tender,
}: TenderDetailModalProps) {
    if (!tender) return null

    const deadline = tender.submissionDeadline
        ? new Date(tender.submissionDeadline)
        : null

    const isPublished = tender.status === "pb"

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent
                className="
          max-w-5xl w-[95vw]
          max-h-[90vh]
          p-0
          overflow-hidden
          rounded-3xl
          border-border/50
          bg-background
        "
            >
                <div className="sticky top-0 z-10 bg-background/95 backdrop-blur-xl border-b border-border/40 px-6 py-5">
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={onClose}
                        className="absolute right-4 top-4 rounded-full h-9 w-9"
                    >
                        <X className="h-4 w-4" />
                    </Button>

                    <div className="space-y-3">
                        <Badge className="bg-primary/10 text-primary border-none text-[11px] font-bold uppercase tracking-widest px-3">
                            {tender.tenderType === "op" ? "Open Tender" : "Restricted Tender"}
                        </Badge>

                        <DialogTitle className="text-3xl font-bold leading-tight">
                            {tender.title}
                        </DialogTitle>

                        <div className="flex flex-wrap items-center gap-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            <span className="flex items-center gap-1.5">
                                <Hash className="h-3.5 w-3.5" />
                                {tender.tenderNo || "REF-PENDING"}
                            </span>
                            <span className="flex items-center gap-1.5">
                                <Building2 className="h-3.5 w-3.5" />
                                {tender.tenderCategoryRelation?.tenderCategory || "General"}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="overflow-y-auto px-6 py-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div className="lg:col-span-2 space-y-6">
                            <section className="space-y-3">
                                <div className="flex items-center gap-2">
                                    <FileText className="h-4 w-4 text-primary" />
                                    <h4 className="text-xs font-black uppercase tracking-[0.2em] text-muted-foreground">
                                        Project Scope
                                    </h4>
                                </div>

                                <div className="rounded-2xl border border-border/40 bg-muted/20 p-5">
                                    <p className="text-sm leading-relaxed text-muted-foreground">
                                        {tender.description ||
                                            "No detailed description provided for this tender opportunity."}
                                    </p>
                                </div>
                            </section>
                        </div>

                        <div className="space-y-4">
                            <div className="rounded-2xl border border-border/40 bg-card/60 p-4 flex items-center gap-4">
                                <div className="h-11 w-11 rounded-xl bg-primary/10 flex items-center justify-center">
                                    <Calendar className="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <p className="text-[11px] font-bold uppercase tracking-widest text-muted-foreground/60">
                                        Closing Date
                                    </p>
                                    <p className="text-sm font-bold">
                                        {deadline
                                            ? format(deadline, "dd MMM yyyy")
                                            : "Not specified"}
                                    </p>
                                </div>
                            </div>

                            <div className="rounded-2xl border border-border/40 bg-card/60 p-4 flex items-center gap-4">
                                <div className="h-11 w-11 rounded-xl bg-primary/10 flex items-center justify-center">
                                    <Clock className="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <p className="text-[11px] font-bold uppercase tracking-widest text-muted-foreground/60">
                                        Status
                                    </p>
                                    <div className="flex items-center gap-2">
                                        <span
                                            className={`h-2 w-2 rounded-full ${isPublished ? "bg-emerald-500" : "bg-orange-500"
                                                }`}
                                        />
                                        <p className="text-sm font-bold uppercase">
                                            {isPublished ? "Live" : "Draft"}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="flex items-center gap-2 pt-2">
                                <ShieldCheck className="h-4 w-4 text-emerald-500" />
                                <span className="text-xs font-semibold text-muted-foreground">
                                    Verified procurement process
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="sticky bottom-0 border-t border-border/40 bg-background/95 backdrop-blur-xl px-6 py-4 flex items-center justify-between">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        className="rounded-xl px-6 font-semibold"
                    >
                        Close
                    </Button>

                    <Button className="rounded-xl px-8 font-bold group">
                        View Full Documents
                        <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    )
}
