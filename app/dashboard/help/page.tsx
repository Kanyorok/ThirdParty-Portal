"use client"

import Link from "next/link"
import React, { useMemo, useState } from "react"
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/common/accordion"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
  ArrowUpRight,
  CircleHelp,
  Clock3,
  Command,
  BookOpen,
  Compass,
  FileSearch,
  LifeBuoy,
  ListChecks,
  MessageSquare,
  SearchX,
  Search,
  ShieldCheck,
  Ticket,
} from "lucide-react"

type TopicKey = "all" | "account" | "tenders" | "documents" | "support"

const TOPICS: Array<{ id: TopicKey; label: string }> = [
  { id: "all", label: "All" },
  { id: "account", label: "Account" },
  { id: "tenders", label: "Tenders" },
  { id: "documents", label: "Documents" },
  { id: "support", label: "Support" },
]

const FAQS: Array<{
  id: string
  topic: Exclude<TopicKey, "all">
  question: string
  answer: string
  eta: string
}> = [
  {
    id: "tender-discovery",
    topic: "tenders",
    question: "How do I find tenders I can apply to?",
    answer: "Switch to Supplier mode, open Tenders, then filter by status and category to surface matching opportunities.",
    eta: "1 min read",
  },
  {
    id: "pending-application",
    topic: "support",
    question: "Why is my application pending?",
    answer: "Pending means review is still in progress. Confirm all required documents are uploaded and valid to avoid back-and-forth.",
    eta: "1 min read",
  },
  {
    id: "missing-route",
    topic: "support",
    question: "Search opens a result but page is 404.",
    answer: "The item may have moved or been archived. Reopen it from the sidebar path and refresh the list before retrying.",
    eta: "1 min read",
  },
  {
    id: "file-formats",
    topic: "documents",
    question: "Which formats are accepted?",
    answer: "PDF is recommended for formal submissions. Images should be reserved for profile and branding assets.",
    eta: "1 min read",
  },
  {
    id: "profile-images",
    topic: "account",
    question: "Can logo and user image differ?",
    answer: "Yes. Logo is your company identity, while user image is the personal avatar tied to the signed-in account.",
    eta: "1 min read",
  },
]

const QUICK_ACTIONS: Array<{
  title: string
  description: string
  href: string
  icon: React.ComponentType<{ className?: string }>
  tone: string
}> = [
  {
    title: "Create support ticket",
    description: "Submit a structured issue and route it to support.",
    href: "/dashboard/help/tickets#create-ticket",
    icon: Ticket,
    tone: "border-blue-200 text-blue-700 bg-blue-50",
  },
  {
    title: "Track my tickets",
    description: "Monitor statuses, responses, and next actions.",
    href: "/dashboard/help/tickets",
    icon: ListChecks,
    tone: "border-emerald-200 text-emerald-700 bg-emerald-50",
  },
  {
    title: "Complete business profile",
    description: "Update legal details, contacts, and branding.",
    href: "/dashboard/settings/profile",
    icon: Compass,
    tone: "border-violet-200 text-violet-700 bg-violet-50",
  },
  {
    title: "Find tenders",
    description: "Browse open tender opportunities.",
    href: "/dashboard/supplier/tenders",
    icon: BookOpen,
    tone: "border-amber-200 text-amber-700 bg-amber-50",
  },
  {
    title: "Manage supplier documents",
    description: "Upload and maintain compliance docs.",
    href: "/dashboard/supplier/documents",
    icon: ShieldCheck,
    tone: "border-slate-200 text-slate-700 bg-slate-50",
  },
]

const SUPPORT_PLAYBOOK = [
  { step: "Search first", detail: "Use keywords for the exact issue and module before raising a ticket." },
  { step: "Be specific", detail: "Use concise subjects with clear impact, expected behavior, and what happened." },
  { step: "Add references", detail: "Include IDs, dates, and screenshots so support can reproduce quickly." },
]

function topicTone(topic: Exclude<TopicKey, "all">) {
  if (topic === "tenders") return "border-amber-200 text-amber-700 bg-amber-50"
  if (topic === "documents") return "border-emerald-200 text-emerald-700 bg-emerald-50"
  if (topic === "account") return "border-violet-200 text-violet-700 bg-violet-50"
  return "border-blue-200 text-blue-700 bg-blue-50"
}

export default function HelpPage() {
  const [query, setQuery] = useState("")
  const [activeTopic, setActiveTopic] = useState<TopicKey>("all")

  const filteredFaqs = useMemo(() => {
    const q = query.trim().toLowerCase()
    return FAQS.filter((f) => {
      if (activeTopic !== "all" && f.topic !== activeTopic) return false
      if (!q) return true
      return f.question.toLowerCase().includes(q) || f.answer.toLowerCase().includes(q)
    })
  }, [query, activeTopic])

  return (
    <div className="w-full space-y-8 antialiased">
      <header className="space-y-6">
        <div className="space-y-2.5">
          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
            <LifeBuoy className="h-3.5 w-3.5 text-blue-600" />
            <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Help Center</span>
          </div>
          <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div className="min-w-0">
              <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Find answers faster</h1>
              <p className="mt-1 text-sm text-slate-600">
                Explore instant answers, follow support best practices, and route issues clearly for faster resolution.
              </p>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Button asChild className="h-10 rounded-full border border-blue-600 bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700">
                <Link href="/dashboard/help/tickets#create-ticket">
                  Create ticket
                  <ArrowUpRight className="ml-1.5 h-4 w-4" />
                </Link>
              </Button>
              <Button asChild variant="outline" className="h-10 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50">
                <Link href="/dashboard/help/tickets">
                  My tickets
                  <ArrowUpRight className="ml-1.5 h-4 w-4" />
                </Link>
              </Button>
            </div>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2 text-xs">
          <span className="inline-flex h-8 items-center rounded-full border border-slate-200 bg-white px-3 font-medium text-slate-600">
            Articles <span className="ml-1 font-semibold text-slate-900">{FAQS.length}</span>
          </span>
          <span className="inline-flex h-8 items-center rounded-full border border-slate-200 bg-white px-3 font-medium text-slate-600">
            Matching <span className="ml-1 font-semibold text-slate-900">{filteredFaqs.length}</span>
          </span>
          <span className="inline-flex h-8 items-center rounded-full border border-slate-200 bg-white px-3 font-medium text-slate-600">
            Quick actions <span className="ml-1 font-semibold text-slate-900">{QUICK_ACTIONS.length}</span>
          </span>
        </div>
      </header>

      <section className="space-y-3">
        <div className="relative max-w-3xl">
          <Search className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <Input
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            placeholder="Search by issue, workflow, or keyword..."
            className="h-11 rounded-xl border-slate-200 bg-white pl-10 pr-10 text-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-50"
          />
          {query.trim() ? (
            <button
              type="button"
              onClick={() => setQuery("")}
              className="absolute right-2.5 top-1/2 -translate-y-1/2 inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
              aria-label="Clear search"
            >
              <SearchX className="h-4 w-4" />
            </button>
          ) : null}
        </div>
        <div className="flex flex-wrap items-center gap-2">
          {TOPICS.map((topic) => {
            const isActive = activeTopic === topic.id
            return (
              <button
                key={topic.id}
                type="button"
                onClick={() => setActiveTopic(topic.id)}
                className={[
                  "inline-flex h-9 items-center rounded-full border px-4 text-xs font-semibold transition-colors",
                  isActive
                    ? "border-blue-300 bg-blue-50 text-blue-700"
                    : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50",
                ].join(" ")}
              >
                {topic.label}
              </button>
            )
          })}
        </div>
      </section>

      <section className="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside className="space-y-6 xl:sticky xl:top-4 self-start">
          <div className="border-y border-slate-200 bg-white">
            <div className="border-b border-slate-200 px-5 py-4">
              <h2 className="text-base font-semibold text-slate-900">Support shortcuts</h2>
            </div>
            <div className="px-5 py-5 space-y-3">
              {QUICK_ACTIONS.map((item) => {
                const Icon = item.icon
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    className="group flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-3 transition-colors hover:border-slate-300 hover:bg-slate-50"
                  >
                    <div className={`mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border ${item.tone}`}>
                      <Icon className="h-4 w-4" />
                    </div>
                    <div className="min-w-0 flex-1">
                      <p className="text-sm font-semibold text-slate-900 leading-tight">{item.title}</p>
                      <p className="mt-1 text-xs text-slate-600 leading-relaxed">{item.description}</p>
                    </div>
                    <ArrowUpRight className="h-4 w-4 text-slate-400 transition-colors group-hover:text-slate-700" />
                  </Link>
                )
              })}
            </div>
          </div>

          <div className="border-y border-slate-200 bg-white">
            <div className="border-b border-slate-200 px-5 py-4">
              <h2 className="flex items-center gap-2 text-base font-semibold text-slate-900">
                <Command className="h-4 w-4 text-blue-600" />
                Better ticket quality
              </h2>
            </div>
            <div className="px-5 py-5 space-y-4">
              {SUPPORT_PLAYBOOK.map((item) => (
                <div key={item.step} className="rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3">
                  <p className="text-sm font-semibold text-slate-900">{item.step}</p>
                  <p className="mt-1 text-xs leading-relaxed text-slate-600">{item.detail}</p>
                </div>
              ))}
            </div>
          </div>
        </aside>

        <div className="space-y-6 min-w-0">
          <section className="border-y border-slate-200 bg-white">
            <div className="border-b border-slate-200 px-5 py-4">
              <h2 className="flex items-center gap-2 text-base font-semibold text-slate-900">
                <CircleHelp className="h-4 w-4 text-blue-600" />
                Knowledge base
              </h2>
            </div>
            <div className="px-5 py-5">
              {filteredFaqs.length > 0 ? (
                <Accordion type="single" collapsible className="space-y-3">
                  {filteredFaqs.map((faq) => (
                    <AccordionItem key={faq.id} value={faq.id} className="rounded-xl border border-slate-200 bg-white px-4">
                      <AccordionTrigger className="hover:no-underline py-3.5 text-left">
                        <span className="inline-flex flex-col items-start gap-2">
                          <span className="text-sm font-semibold text-slate-900">{faq.question}</span>
                          <span className="inline-flex items-center gap-2">
                            <span className={`inline-flex h-6 items-center rounded-full border px-2.5 text-[10px] font-semibold uppercase tracking-wide ${topicTone(faq.topic)}`}>
                              {faq.topic}
                            </span>
                            <span className="inline-flex items-center text-[11px] text-slate-500">
                              <Clock3 className="mr-1 h-3.5 w-3.5" />
                              {faq.eta}
                            </span>
                          </span>
                        </span>
                      </AccordionTrigger>
                      <AccordionContent className="pb-4 text-sm leading-relaxed text-slate-600">
                        {faq.answer}
                      </AccordionContent>
                    </AccordionItem>
                  ))}
                </Accordion>
              ) : (
                <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
                  <div className="mx-auto flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white">
                    <FileSearch className="h-4 w-4 text-slate-500" />
                  </div>
                  <p className="mt-3 text-sm font-semibold text-slate-900">No matching answers</p>
                  <p className="mt-1 text-xs text-slate-600">
                    Refine your keywords or switch topic filters.
                  </p>
                  <Button
                    type="button"
                    variant="outline"
                    className="mt-4 h-9 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-white"
                    onClick={() => {
                      setQuery("")
                      setActiveTopic("all")
                    }}
                  >
                    Reset filters
                  </Button>
                </div>
              )}
            </div>
          </section>

          <section className="border-y border-slate-200 bg-white">
            <div className="border-b border-slate-200 px-5 py-4">
              <h2 className="flex items-center gap-2 text-base font-semibold text-slate-900">
                <MessageSquare className="h-4 w-4 text-blue-600" />
                Still need help?
              </h2>
            </div>
            <div className="px-5 py-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <p className="text-sm text-slate-600 max-w-2xl">
                Open a ticket with clear details and references. This improves routing and shortens turnaround time.
              </p>
              <div className="flex flex-wrap items-center gap-2">
                <Button asChild className="h-10 rounded-full border border-blue-600 bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700">
                  <Link href="/dashboard/help/tickets#create-ticket">
                    Create ticket
                    <ArrowUpRight className="ml-1.5 h-4 w-4" />
                  </Link>
                </Button>
                <Button asChild variant="outline" className="h-10 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50">
                  <Link href="/dashboard/help/tickets">
                    Track tickets
                    <ArrowUpRight className="ml-1.5 h-4 w-4" />
                  </Link>
                </Button>
              </div>
            </div>
          </section>
        </div>
      </section>
    </div>
  )
}
