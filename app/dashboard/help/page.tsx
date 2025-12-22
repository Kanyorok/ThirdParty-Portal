"use client"

import React, { useState } from "react"
import {
  Accordion, AccordionContent, AccordionItem, AccordionTrigger
} from "@/components/common/accordion"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { BookOpen, Command, LifeBuoy, Send, MessageSquare } from "lucide-react"

export default function HelpPage() {
  const [sending, setSending] = useState(false)
  const [sent, setSent] = useState<null | 'ok' | string>(null)

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setSent(null)
    setSending(true)
    const form = e.currentTarget
    const formData = new FormData(form)

    try {
      const res = await fetch('/api/support/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(formData))
      })
      if (res.ok) {
        setSent('ok')
        form.reset()
      } else {
        setSent('Failed to send')
      }
    } catch (err) {
      setSent('Network error')
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="max-w-[1400px] mx-auto px-6 py-10 space-y-12">
      <header className="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-border/40 pb-10">
        <div className="space-y-2">
          <div className="flex items-center gap-3 text-primary">
            <LifeBuoy className="h-5 w-5" />
            <span className="text-[10px] font-black uppercase tracking-[.3em]">Support Center</span>
          </div>
          <h1 className="text-4xl font-bold tracking-tight text-foreground">How can we help you?</h1>
          <p className="text-muted-foreground max-w-md leading-relaxed">
            Access our comprehensive guides, frequently asked questions, or reach out to our dedicated support team.
          </p>
        </div>
        <Badge variant="outline" className="h-fit px-4 py-1 rounded-full bg-secondary/50 border-primary/20 text-primary">
          Portal v0.2.0
        </Badge>
      </header>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <div className="lg:col-span-8 space-y-12">

          <section className="space-y-6">
            <div className="flex items-center gap-3 text-foreground/80 font-semibold">
              <BookOpen className="h-4 w-4" />
              <h2>Quick Start Guide</h2>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {[
                { title: "Profile", desc: "Complete your business profile in Account settings." },
                { title: "Tenders", desc: "Browse open opportunities under the Tenders tab." },
                { title: "Pre-qual", desc: "Submit category applications to get vetted." },
                { title: "Documents", desc: "Centralize your compliance files in My Documents." }
              ].map((item, i) => (
                <div key={i} className="p-5 rounded-2xl bg-secondary/30 border border-border/50 hover:border-primary/30 transition-colors group">
                  <span className="text-[10px] font-bold text-primary opacity-50 mb-2 block uppercase tracking-widest">Step 0{i + 1}</span>
                  <h3 className="font-bold text-foreground mb-1">{item.title}</h3>
                  <p className="text-xs text-muted-foreground leading-relaxed">{item.desc}</p>
                </div>
              ))}
            </div>
          </section>

          {/* FAQ Section */}
          <section className="space-y-6">
            <div className="flex items-center gap-3 text-foreground/80 font-semibold">
              <MessageSquare className="h-4 w-4" />
              <h2>Frequently Asked Questions</h2>
            </div>
            <Accordion type="single" collapsible className="w-full space-y-3">
              <AccordionItem value="item-1" className="border rounded-2xl px-4 bg-card/50">
                <AccordionTrigger className="hover:no-underline font-medium py-5">How do I find tenders I can apply to?</AccordionTrigger>
                <AccordionContent className="text-muted-foreground pb-5 leading-relaxed">
                  Navigate to Dashboard → Tenders. You'll find all active public opportunities and restricted invitations tailored to your profile.
                </AccordionContent>
              </AccordionItem>
              <AccordionItem value="item-2" className="border rounded-2xl px-4 bg-card/50">
                <AccordionTrigger className="hover:no-underline font-medium py-5">Global Search shows results but navigates to 404?</AccordionTrigger>
                <AccordionContent className="text-muted-foreground pb-5 leading-relaxed">
                  This usually happens if the tender has been recently archived. Try clearing your browser cache or using the sidebar navigation for direct access.
                </AccordionContent>
              </AccordionItem>
            </Accordion>
          </section>
        </div>

        {/* Sidebar Support Form */}
        <aside className="lg:col-span-4 space-y-6">
          <div className="p-8 rounded-[2.5rem] bg-card border border-border/60 shadow-2xl shadow-primary/5 relative overflow-hidden group">
            <div className="absolute top-0 right-0 w-32 h-32 bg-primary/5 blur-3xl -z-10 group-hover:bg-primary/10 transition-colors" />

            <div className="mb-8">
              <h3 className="text-xl font-bold tracking-tight mb-2">Contact Support</h3>
              <p className="text-xs text-muted-foreground">Response time: &lt; 2 hours (EAT)</p>
            </div>

            <form onSubmit={onSubmit} className="space-y-5">
              <div className="space-y-2">
                <Label htmlFor="subject" className="text-[10px] uppercase tracking-widest font-bold opacity-60">Subject</Label>
                <Input id="subject" name="subject" required className="bg-secondary/20 border-border/40" />
              </div>

              <div className="space-y-2">
                <Label htmlFor="category" className="text-[10px] uppercase tracking-widest font-bold opacity-60">Category</Label>
                <Select name="category" defaultValue="General">
                  <SelectTrigger className="bg-secondary/20 border-border/40">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="General">General Inquiry</SelectItem>
                    <SelectItem value="Tenders">Tender Issues</SelectItem>
                    <SelectItem value="Technical">Technical Support</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="message" className="text-[10px] uppercase tracking-widest font-bold opacity-60">Message</Label>
                <Textarea id="message" name="message" required rows={4} className="bg-secondary/20 border-border/40 resize-none" />
              </div>

              <Button disabled={sending} className="w-full h-12 rounded-xl font-bold tracking-tight shadow-lg shadow-primary/20">
                {sending ? "Sending Request..." : "Submit Ticket"}
                <Send className="ml-2 h-4 w-4" />
              </Button>

              {sent && (
                <p className={`text-center text-xs font-medium ${sent === 'ok' ? 'text-emerald-500' : 'text-rose-500'}`}>
                  {sent === 'ok' ? "Ticket created successfully. Check your email." : sent}
                </p>
              )}
            </form>
          </div>

          <div className="p-6 rounded-3xl bg-secondary/20 border border-border/40">
            <h4 className="flex items-center gap-2 text-xs font-bold uppercase tracking-widest mb-4 opacity-70">
              <Command className="h-3 w-3" />
              Power User Shortcuts
            </h4>
            <div className="flex items-center justify-between text-sm">
              <span className="text-muted-foreground">Global Search</span>
              <kbd className="px-2 py-1 rounded bg-background border border-border/60 text-[10px] font-mono shadow-sm">⌘ + J</kbd>
            </div>
          </div>
        </aside>
      </div>
    </div>
  )
}