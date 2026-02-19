"use client"

import Link from "next/link"
import React, { useEffect, useMemo, useState } from "react"
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/common/accordion"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
  ArrowRight,
  ArrowUpRight,
  BookOpen,
  Command,
  Compass,
  LifeBuoy,
  MessageSquare,
  Search,
  ShieldCheck,
} from "lucide-react"

const FAQS = [
  {
    id: "1",
    question: "How do I find tenders I can apply to?",
    answer: "Switch to Supplier profile then use Dashboard -> Find Tenders.",
  },
  {
    id: "2",
    question: "Why is my application pending?",
    answer: "Pending means reviewer action is in progress. Confirm required docs are complete.",
  },
  {
    id: "3",
    question: "Search opens a result but page is 404.",
    answer: "The item may be archived or moved. Navigate from sidebar and retry.",
  },
  {
    id: "4",
    question: "Which formats are accepted?",
    answer: "PDF is recommended. Images are used for profile/logo assets.",
  },
  {
    id: "5",
    question: "Can logo and user image differ?",
    answer: "Yes. Logo is company branding; user image is account avatar.",
  },
] as const

const QUICK_ACTIONS = [
  {
    title: "Complete business profile",
    description: "Update legal, tax, contacts, and logo.",
    href: "/dashboard/settings/profile",
    icon: Compass,
    tone: "border-sky-200/60 bg-sky-500/10 text-sky-700 dark:border-sky-500/20 dark:text-sky-300",
  },
  {
    title: "Find tenders",
    description: "Browse open tender opportunities.",
    href: "/dashboard/supplier/tenders",
    icon: BookOpen,
    tone: "border-emerald-200/60 bg-emerald-500/10 text-emerald-700 dark:border-emerald-500/20 dark:text-emerald-300",
  },
  {
    title: "Manage supplier documents",
    description: "Upload and maintain compliance docs.",
    href: "/dashboard/supplier/documents",
    icon: ShieldCheck,
    tone: "border-amber-200/60 bg-amber-500/10 text-amber-700 dark:border-amber-500/20 dark:text-amber-300",
  },
] as const

const PLAYBOOK = [
  { title: "Set up profile", detail: "Confirm account details and branding assets." },
  { title: "Unlock modules", detail: "Switch active role based on workflow." },
  { title: "Execute daily tasks", detail: "Track bids, files, and statuses." },
] as const

export default function HelpPage() {
  const [query, setQuery] = useState("")
  const [entered, setEntered] = useState(false)

  const filteredFaqs = useMemo(() => {
    const q = query.trim().toLowerCase()
    return FAQS.filter((f) => !q || f.question.toLowerCase().includes(q) || f.answer.toLowerCase().includes(q))
  }, [query])

  useEffect(() => {
    const t = setTimeout(() => setEntered(true), 30)
    return () => clearTimeout(t)
  }, [])

  return (
    <div className="w-full antialiased relative">
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(56rem_24rem_at_0%_0%,rgba(14,165,233,0.12),transparent_58%),radial-gradient(40rem_18rem_at_100%_0%,rgba(16,185,129,0.10),transparent_62%)]"
      />
      <div className="w-full space-y-6 sm:space-y-8">
        <header className="border-b border-border/60 pb-6">
          <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div className="min-w-0">
              <div className="inline-flex items-center gap-2 border border-border/60 px-3 py-1.5">
                <LifeBuoy className="h-3.5 w-3.5 text-primary" />
                <span className="text-[10px] font-semibold uppercase tracking-wider text-primary">Support Hub</span>
              </div>
              <h1 className="mt-3 text-2xl sm:text-3xl font-semibold tracking-tight text-foreground">Help Center</h1>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Button asChild className="h-10 rounded-md shadow-none text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground">
                <Link href="/dashboard/help/tickets#create-ticket">
                  Create ticket
                  <ArrowUpRight className="ml-1.5 h-4 w-4" />
                </Link>
              </Button>
              <Button asChild variant="outline" className="h-10 rounded-md shadow-none text-xs font-medium">
                <Link href="/dashboard/help/tickets">
                  My tickets
                  <ArrowUpRight className="ml-1.5 h-4 w-4" />
                </Link>
              </Button>
            </div>
          </div>

          <div className="mt-4 max-w-2xl">
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Search answers for account, tenders, prequalification, documents..."
                className="h-10 pl-10 border-border/60 bg-background"
              />
            </div>
          </div>
        </header>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 xl:gap-8">
          <aside className="lg:col-span-4 xl:col-span-3 min-w-0 space-y-6 lg:sticky lg:top-4 self-start">
            <section className="border-y border-border/60 bg-background">
              <div className="border-b border-border/60 px-5 py-4">
                <h2 className="text-base font-semibold text-foreground">Quick actions</h2>
              </div>
              <div className="px-5 py-5 space-y-3">
                {QUICK_ACTIONS.map((item, index) => {
                  const Icon = item.icon
                  return (
                    <Link
                      key={item.href}
                      href={item.href}
                      className={[
                        "group flex items-start gap-3 border border-border/60 bg-background p-3",
                        "transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/[0.03]",
                        entered ? "opacity-100 translate-y-0" : "opacity-0 translate-y-1",
                      ].join(" ")}
                      style={{ transitionDelay: `${index * 40}ms` }}
                    >
                      <div className={`mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center border ${item.tone}`}>
                        <Icon className="h-4 w-4" />
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="text-sm font-semibold text-foreground leading-tight">{item.title}</p>
                        <p className="mt-1 text-xs text-muted-foreground leading-relaxed">{item.description}</p>
                      </div>
                      <ArrowUpRight className="h-4 w-4 text-muted-foreground group-hover:text-primary" />
                    </Link>
                  )
                })}
              </div>
            </section>

            <section className="border-y border-border/60 bg-background">
              <div className="border-b border-border/60 px-5 py-4">
                <h2 className="flex items-center gap-2 text-base font-semibold text-foreground">
                  <Command className="h-4 w-4 text-primary" />
                  Before you submit
                </h2>
              </div>
              <div className="px-5 py-5 space-y-3 text-xs text-muted-foreground">
                <p className="flex items-start gap-2">
                  <ArrowRight className="h-3.5 w-3.5 mt-0.5 text-primary" />
                  Use a clear subject that names the issue in 5-8 words.
                </p>
                <p className="flex items-start gap-2">
                  <ArrowRight className="h-3.5 w-3.5 mt-0.5 text-primary" />
                  In the message, include steps to reproduce and expected result.
                </p>
              </div>
            </section>
          </aside>

          <div className="lg:col-span-8 xl:col-span-9 min-w-0 space-y-6">
            <section className="border-y border-border/60 bg-background">
              <div className="border-b border-border/60 px-5 py-4">
                <h2 className="text-base font-semibold text-foreground">Getting started playbook</h2>
              </div>
              <div className="px-5 py-5 grid grid-cols-1 md:grid-cols-3 gap-3">
                {PLAYBOOK.map((step, index) => (
                  <div
                    key={step.title}
                    className={[
                      "border border-border/60 bg-background p-4",
                      "transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:bg-primary/[0.03]",
                      entered ? "opacity-100 translate-y-0" : "opacity-0 translate-y-1",
                    ].join(" ")}
                    style={{ transitionDelay: `${(index + 1) * 45}ms` }}
                  >
                    <div className="inline-flex h-6 min-w-6 items-center justify-center border border-border/60 bg-background px-2 text-[10px] font-bold text-primary">
                      {index + 1}
                    </div>
                    <h3 className="mt-3 text-sm font-semibold text-foreground">{step.title}</h3>
                    <p className="mt-1 text-xs text-muted-foreground leading-relaxed">{step.detail}</p>
                  </div>
                ))}
              </div>
            </section>

            <section className="border-y border-border/60 bg-background">
              <div className="border-b border-border/60 px-5 py-4">
                <h2 className="flex items-center gap-2 text-base font-semibold text-foreground">
                  <MessageSquare className="h-4 w-4 text-primary" />
                  Instant answers
                </h2>
              </div>
              <div className="px-5 py-5">
                {filteredFaqs.length > 0 ? (
                  <Accordion type="single" collapsible className="w-full space-y-3">
                    {filteredFaqs.map((faq) => (
                      <AccordionItem key={faq.id} value={faq.id} className="border border-border/60 px-4 bg-background">
                        <AccordionTrigger className="hover:no-underline font-medium py-4 text-left">{faq.question}</AccordionTrigger>
                        <AccordionContent className="text-muted-foreground pb-4 leading-relaxed">{faq.answer}</AccordionContent>
                      </AccordionItem>
                    ))}
                  </Accordion>
                ) : (
                  <div className="border border-dashed border-border/60 bg-background p-6 text-center">
                    <p className="text-sm font-semibold text-foreground">No FAQ results found.</p>
                  </div>
                )}
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  )
}
