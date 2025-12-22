"use client";

import React from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
    X, Calendar, Hash, Building2,
    FileText, ShieldCheck, Clock, ArrowRight
} from "lucide-react";
import { format } from "date-fns";

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/common/dialog";
import { Button } from "@/components/common/button";
import { Badge } from "@/components/common/badge";
import { Separator } from "@/components/common/separator";

interface TenderDetailModalProps {
    isOpen: boolean;
    onClose: () => void;
    tender: any;
    onInvitationUpdate?: () => void;
}

export default function TenderDetailModal({
    isOpen,
    onClose,
    tender,
}: TenderDetailModalProps) {
    if (!tender) return null;

    const deadline = tender.submissionDeadline ? new Date(tender.submissionDeadline) : null;
    const isPublished = tender.status === "pb";

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-2xl p-0 overflow-hidden border-border/50 bg-background/95 backdrop-blur-xl rounded-[2rem]">
                {/* Header Section */}
                <div className="relative p-8 pb-6 bg-muted/30">
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={onClose}
                        className="absolute right-4 top-4 rounded-full h-8 w-8 hover:bg-background shadow-sm"
                    >
                        <X className="h-4 w-4 text-muted-foreground" />
                    </Button>

                    <div className="space-y-4">
                        <Badge className="bg-primary/10 text-primary border-none text-[10px] font-black uppercase tracking-widest px-3">
                            {tender.tenderType === 'op' ? 'Open Tender' : 'Restricted'}
                        </Badge>

                        <div className="space-y-1">
                            <DialogTitle className="text-2xl font-bold tracking-tight text-foreground leading-tight">
                                {tender.title}
                            </DialogTitle>
                            <div className="flex items-center gap-3 text-muted-foreground/60">
                                <div className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider">
                                    <Hash className="h-3 w-3" /> {tender.tenderNo || "REF-PENDING"}
                                </div>
                                <span className="h-1 w-1 rounded-full bg-border" />
                                <div className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider">
                                    <Building2 className="h-3 w-3" /> {tender.tenderCategoryRelation?.tenderCategory || "General"}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="p-8 pt-6 space-y-8">
                    {/* Info Grid - Glass Design */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="p-4 rounded-2xl border border-border/40 bg-card/50 flex items-center gap-4">
                            <div className="h-10 w-10 rounded-xl bg-primary/5 flex items-center justify-center">
                                <Calendar className="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p className="text-[10px] font-bold text-muted-foreground/50 uppercase tracking-widest">Closing Date</p>
                                <p className="text-sm font-bold tabular-nums">
                                    {deadline ? format(deadline, "dd MMM, yyyy") : "No Date Set"}
                                </p>
                            </div>
                        </div>

                        <div className="p-4 rounded-2xl border border-border/40 bg-card/50 flex items-center gap-4">
                            <div className="h-10 w-10 rounded-xl bg-primary/5 flex items-center justify-center">
                                <Clock className="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p className="text-[10px] font-bold text-muted-foreground/50 uppercase tracking-widest">Status</p>
                                <div className="flex items-center gap-2">
                                    <span className={`h-1.5 w-1.5 rounded-full ${isPublished ? 'bg-emerald-500' : 'bg-orange-500'}`} />
                                    <p className="text-sm font-bold uppercase tracking-tighter">
                                        {isPublished ? 'Live' : 'Draft'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Description Section */}
                    <div className="space-y-3">
                        <div className="flex items-center gap-2">
                            <FileText className="h-4 w-4 text-primary" />
                            <h4 className="text-[11px] font-black uppercase tracking-[0.2em] text-foreground/70">Project Scope</h4>
                        </div>
                        <div className="p-5 rounded-[1.25rem] bg-muted/20 border border-border/30">
                            <p className="text-[13px] leading-relaxed text-muted-foreground font-medium">
                                {tender.description || "No detailed description provided for this tender opportunity."}
                            </p>
                        </div>
                    </div>

                    {/* Footer Actions */}
                    <div className="pt-4 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <ShieldCheck className="h-4 w-4 text-emerald-500" />
                            <span className="text-[10px] font-bold text-muted-foreground/50 uppercase">Verified Procurement</span>
                        </div>

                        <div className="flex gap-3">
                            <Button variant="outline" onClick={onClose} className="rounded-xl px-6 text-[12px] font-bold uppercase tracking-wider">
                                Close
                            </Button>
                            <Button className="rounded-xl px-6 bg-primary hover:bg-primary/90 text-[12px] font-bold uppercase tracking-wider group">
                                View Full Documents
                                <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </Button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}