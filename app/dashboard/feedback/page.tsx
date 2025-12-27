"use client"

import React, { useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import {
    MessageSquare,
    Send,
    CheckCircle2,
    XCircle,
    ArrowUpRight,
    Lightbulb,
    Bug,
    Zap,
    Activity
} from "lucide-react"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn } from "@/lib/utils"

export default function FeedbackPage() {
    const [sending, setSending] = useState(false)
    const [sent, setSent] = useState<null | 'ok' | string>(null)

    async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault()
        setSent(null)
        setSending(true)

        const formData = new FormData(e.currentTarget)
        const payload = Object.fromEntries(formData)

        try {
            const res = await fetch('/api/feedback/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...payload, timestamp: new Date().toISOString() })
            })
            if (res.ok) {
                setSent('ok')
                    ; (e.target as HTMLFormElement).reset()
            } else {
                setSent('Transmission Error')
            }
        } catch (err) {
            setSent('Uplink Failure')
        } finally {
            setSending(false)
        }
    }

    return (
        <div className="font-sans antialiased">
            <header className="mb-10 space-y-2 border-b border-border/40 pb-8">
                <div className="flex items-center gap-2 text-primary">
                    <Activity className="size-3.5" />
                    <span className="text-[10px] font-black uppercase tracking-[0.4em]">Optimization Engine</span>
                </div>
                <h1 className="text-3xl font-black tracking-tighter uppercase">
                    System <span className="text-muted-foreground/30 font-normal">Feedback</span>
                </h1>
            </header>

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                {/* Left: Quick Categories */}
                <div className="lg:col-span-6 space-y-10">
                    <div className="space-y-4">
                        <p className="text-xs text-muted-foreground leading-relaxed antialiased font-medium max-w-md">
                            Your feedback directly influences the system roadmap. Report bottlenecks, suggest architectural improvements, or flag interface anomalies.
                        </p>

                        <div className="grid grid-cols-1 gap-px bg-border/40 border border-border/40 rounded-sm overflow-hidden mt-6">
                            {[
                                { icon: Lightbulb, title: "Feature Request", desc: "Suggest new system capabilities", ref: "OPT-REQ" },
                                { icon: Bug, title: "Report Anomaly", desc: "Flag inconsistent behavior", ref: "SYS-ERR" },
                                { icon: Zap, title: "Performance", desc: "Report latency or sync issues", ref: "LAT-LOG" },
                            ].map((card, i) => (
                                <div key={i} className="p-4 bg-background hover:bg-muted/30 transition-all cursor-pointer group flex items-center justify-between">
                                    <div className="flex items-center gap-4">
                                        <card.icon className="size-4 text-primary" />
                                        <div>
                                            <h3 className="font-black text-[10px] uppercase tracking-widest">{card.title}</h3>
                                            <p className="text-[9px] text-muted-foreground font-bold uppercase tracking-tight">{card.desc}</p>
                                        </div>
                                    </div>
                                    <span className="text-[8px] font-mono text-muted-foreground/30">{card.ref}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Right: Feedback Form */}
                <aside className="lg:col-span-6">
                    <div className="border border-border/40 rounded-sm bg-background">
                        <div className="px-5 py-3 border-b border-border/40 flex items-center justify-between bg-muted/20">
                            <h3 className="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                <MessageSquare className="size-3 text-primary" /> Data Entry
                            </h3>
                            <span className="text-[8px] font-mono font-bold text-primary/50">V_FEEDBACK_2.0</span>
                        </div>

                        <form onSubmit={onSubmit} className="p-5 space-y-5">
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <Label className="text-[9px] font-black uppercase tracking-widest text-muted-foreground/70 ml-1">Classification</Label>
                                    <Select name="type" defaultValue="improvement">
                                        <SelectTrigger className="h-9 border-border/40 bg-muted/10 rounded-sm font-bold text-[10px] uppercase">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent className="rounded-sm border-border/40">
                                            <SelectItem value="improvement" className="text-[10px] uppercase font-bold">System Improvement</SelectItem>
                                            <SelectItem value="bug" className="text-[10px] uppercase font-bold">Bug Report</SelectItem>
                                            <SelectItem value="ux" className="text-[10px] uppercase font-bold">User Experience</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label className="text-[9px] font-black uppercase tracking-widest text-muted-foreground/70 ml-1">Subject</Label>
                                    <Input
                                        name="subject"
                                        required
                                        className="h-9 border-border/40 bg-muted/10 rounded-sm font-bold text-[11px] uppercase"
                                        placeholder="SUMMARY_OF_FEEDBACK"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label className="text-[9px] font-black uppercase tracking-widest text-muted-foreground/70 ml-1">Observation Details</Label>
                                    <Textarea
                                        name="content"
                                        required
                                        className="border-border/40 bg-muted/10 rounded-sm min-h-[120px] resize-none text-xs p-3"
                                        placeholder="PROVIDE_DETAILED_CONTEXT"
                                    />
                                </div>
                            </div>

                            <Button
                                type="submit"
                                disabled={sending}
                                className="w-full h-9 bg-primary hover:bg-primary/90 text-primary-foreground font-black uppercase tracking-[0.25em] text-[10px] rounded-sm transition-all"
                            >
                                {sending ? "Transmitting..." : "Submit Feedback"}
                            </Button>

                            <AnimatePresence>
                                {sent && (
                                    <motion.div
                                        initial={{ opacity: 0, x: 5 }}
                                        animate={{ opacity: 1, x: 0 }}
                                        className={cn(
                                            "flex items-center gap-2 p-3 rounded-sm text-[9px] font-black uppercase tracking-widest border",
                                            sent === 'ok' ? "bg-emerald-500/5 border-emerald-500/20 text-emerald-600" : "bg-destructive/5 border-destructive/20 text-destructive"
                                        )}
                                    >
                                        {sent === 'ok' ? <CheckCircle2 className="size-3" /> : <XCircle className="size-3" />}
                                        {sent === 'ok' ? "Feedback Logged Successfully" : sent}
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    )
}