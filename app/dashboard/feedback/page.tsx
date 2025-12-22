"use client"

import React, { useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import {
    HelpCircle,
    Send,
    LifeBuoy,
    Search,
    ChevronRight,
    CheckCircle2,
    XCircle
} from "lucide-react"
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/common/accordion"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn } from "@/lib/utils"

export default function HelpPage() {
    const [sending, setSending] = useState(false)
    const [sent, setSent] = useState<null | 'ok' | string>(null)

    async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault()
        setSent(null)
        setSending(true)

        const formData = new FormData(e.currentTarget)
        const payload = Object.fromEntries(formData)

        try {
            const res = await fetch('/api/support/contact', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...payload, pageUrl: window.location.href })
            })
            if (res.ok) {
                setSent('ok')
                    ; (e.target as HTMLFormElement).reset()
            } else {
                setSent('Something went wrong. Please try again.')
            }
        } catch (err) {
            setSent('Network error. Check your connection.')
        } finally {
            setSending(false)
        }
    }

    return (
        <div className="max-w-[1200px] mx-auto py-12 px-6">
            {/* Minimalist Header */}
            <header className="mb-16 space-y-4">
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="flex items-center gap-2 text-primary/60"
                >
                    <LifeBuoy className="h-4 w-4" />
                    <span className="text-[10px] font-bold uppercase tracking-[0.3em]">Support Center</span>
                </motion.div>
                <motion.h1
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.1 }}
                    className="text-4xl md:text-5xl font-bold tracking-tight text-foreground"
                >
                    How can we help?
                </motion.h1>
            </header>

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-16">
                {/* Left: Knowledge Base & FAQs */}
                <div className="lg:col-span-7 space-y-12">
                    <section className="space-y-6">
                        <h2 className="text-sm font-bold uppercase tracking-widest text-muted-foreground/50 flex items-center gap-2">
                            <Search className="h-4 w-4" /> Common Solutions
                        </h2>

                        <Accordion type="single" collapsible className="w-full space-y-3">
                            {[
                                { q: "Finding relevant tenders", a: "Head to the Tenders tab to see all active public opportunities and restricted invitations." },
                                { q: "Prequalification Status", a: "Track your progress in the Pre-qualification module; status updates are real-time." },
                                { q: "Document Upload Limits", a: "Individual files are limited to 50MB. We support PDF, PNG, and JPEG formats." }
                            ].map((item, i) => (
                                <AccordionItem key={i} value={`item-${i}`} className="border rounded-2xl px-6 bg-card/30 hover:bg-card/50 transition-colors border-border/50">
                                    <AccordionTrigger className="hover:no-underline py-5 text-base font-medium">
                                        {item.q}
                                    </AccordionTrigger>
                                    <AccordionContent className="text-muted-foreground pb-5 leading-relaxed">
                                        {item.a}
                                    </AccordionContent>
                                </AccordionItem>
                            ))}
                        </Accordion>
                    </section>
                </div>

                {/* Right: Contact Support Form */}
                <aside className="lg:col-span-5">
                    <motion.div
                        initial={{ opacity: 0, scale: 0.98 }}
                        animate={{ opacity: 1, scale: 1 }}
                        className="sticky top-8 p-8 md:p-10 rounded-[2.5rem] bg-secondary/20 border border-border/40 backdrop-blur-sm shadow-xl shadow-primary/5"
                    >
                        <div className="mb-8 space-y-1">
                            <h3 className="text-xl font-bold tracking-tight">Direct Support</h3>
                            <p className="text-xs text-muted-foreground">Typical response time: under 2 hours</p>
                        </div>

                        <form onSubmit={onSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="category" className="text-[10px] font-bold uppercase tracking-widest opacity-40">Issue Type</Label>
                                <Select name="category" defaultValue="General">
                                    <SelectTrigger className="h-12 bg-background/50 rounded-xl border-border/40">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="General">General Inquiry</SelectItem>
                                        <SelectItem value="Technical">Technical Support</SelectItem>
                                        <SelectItem value="Tenders">Tenders/RFQs</SelectItem>
                                        <SelectItem value="Account">Account Access</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="subject" className="text-[10px] font-bold uppercase tracking-widest opacity-40">Subject</Label>
                                <Input id="subject" name="subject" required className="h-12 bg-background/50 rounded-xl border-border/40" placeholder="e.g. Document upload failed" />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="message" className="text-[10px] font-bold uppercase tracking-widest opacity-40">Detailed Message</Label>
                                <Textarea id="message" name="message" required className="bg-background/50 rounded-xl border-border/40 min-h-[120px] resize-none" placeholder="Please describe your issue..." />
                            </div>

                            <Button
                                type="submit"
                                disabled={sending}
                                className="w-full h-12 rounded-xl bg-primary hover:bg-primary/90 text-primary-foreground font-bold transition-all active:scale-[0.98]"
                            >
                                {sending ? "Sending..." : "Submit Ticket"}
                                <Send className="ml-2 h-4 w-4" />
                            </Button>

                            <AnimatePresence>
                                {sent && (
                                    <motion.div
                                        initial={{ opacity: 0, height: 0 }}
                                        animate={{ opacity: 1, height: 'auto' }}
                                        className={cn(
                                            "flex items-center gap-2 p-4 rounded-xl text-xs font-medium border",
                                            sent === 'ok' ? "bg-emerald-500/10 border-emerald-500/20 text-emerald-600" : "bg-rose-500/10 border-rose-500/20 text-rose-600"
                                        )}
                                    >
                                        {sent === 'ok' ? <CheckCircle2 className="h-4 w-4" /> : <XCircle className="h-4 w-4" />}
                                        {sent === 'ok' ? "Ticket submitted. We'll reach out via email." : sent}
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </form>
                    </motion.div>
                </aside>
            </div>
        </div>
    )
}