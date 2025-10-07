"use client"

import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/common/accordion"
import { Alert, AlertDescription, AlertTitle } from "@/components/common/alert"
import { Badge } from "@/components/common/badge"
import { useState } from "react"

export default function HelpPage() {
  const [sending, setSending] = useState(false)
  const [sent, setSent] = useState<null | 'ok' | string>(null)

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setSent(null)
    setSending(true)
    const form = e.currentTarget
    const formData = new FormData(form)
    const subject = String(formData.get('subject') || '')
    const category = String(formData.get('category') || '')
    const message = String(formData.get('message') || '')
    const pageUrl = typeof window !== 'undefined' ? window.location.href : ''
    try {
      const res = await fetch('/api/support/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subject, category, message, pageUrl })
      })
      if (res.ok) {
        setSent('ok')
        form.reset()
      } else {
        const data = await res.json().catch(() => null)
        setSent(data?.error || 'Failed to send')
      }
    } catch (e: any) {
      setSent(e?.message || 'Failed to send')
    } finally {
      setSending(false)
    }
  }

  return (
    <div className="px-4 py-6 md:px-8">
      <div className="mb-6 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Help & Support</h1>
          <p className="mt-1 text-sm text-muted-foreground">Guides, FAQs, and troubleshooting for suppliers.</p>
        </div>
        <Badge variant="secondary">Portal v1</Badge>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2 space-y-4">
          <section>
            <h2 className="mb-2 text-lg font-medium">Getting Started</h2>
            <div className="space-y-2 text-sm text-muted-foreground">
              <p>1. Sign in and complete your profile under Dashboard → My Account.</p>
              <p>2. Review available tenders and direct invites under Dashboard → Tenders / RFQs.</p>
              <p>3. Apply for prequalification categories under Dashboard → Pre-qualification.</p>
              <p>4. Upload supporting documents under Dashboard → My Documents.</p>
            </div>
          </section>

          <section>
            <h2 className="mb-2 text-lg font-medium">Global Search</h2>
            <Alert className="mb-3">
              <AlertTitle>Quick open</AlertTitle>
              <AlertDescription>
                Press <kbd className="rounded bg-muted px-1">Ctrl/Cmd</kbd>+<kbd className="rounded bg-muted px-1">J</kbd> to search tenders, RFQs, and documents. Use arrows to navigate, Enter to open.
              </AlertDescription>
            </Alert>
          </section>

          <section>
            <h2 className="mb-2 text-lg font-medium">FAQs</h2>
            <Accordion type="single" collapsible className="divide-y rounded-md border">
              <AccordionItem value="tenders">
                <AccordionTrigger>How do I find tenders I can apply to?</AccordionTrigger>
                <AccordionContent>
                  Go to Dashboard → Tenders. You’ll see open tenders and any restricted tenders you were invited to. Use filters or global search for keywords.
                </AccordionContent>
              </AccordionItem>
              <AccordionItem value="rfqs">
                <AccordionTrigger>Where do I see my RFQ invitations?</AccordionTrigger>
                <AccordionContent>
                  Open Dashboard → RFQs to view invitations, statuses, deadlines, and details.
                </AccordionContent>
              </AccordionItem>
              <AccordionItem value="preq">
                <AccordionTrigger>How do pre-qualification applications work?</AccordionTrigger>
                <AccordionContent>
                  Under Dashboard → Pre-qualification, select a round and apply for relevant categories. Track status as Submitted, Under Review, Approved, or Rejected.
                </AccordionContent>
              </AccordionItem>
              <AccordionItem value="docs">
                <AccordionTrigger>What documents can I access?</AccordionTrigger>
                <AccordionContent>
                  Use Dashboard → My Documents to access public documents or your permitted files. You can preview supported formats directly in the portal.
                </AccordionContent>
              </AccordionItem>
              <AccordionItem value="search">
                <AccordionTrigger>Search shows results but navigating gives 404</AccordionTrigger>
                <AccordionContent>
                  Ensure the route exists under Dashboard. Use global search results (top group) or sidebar navigation. If it persists, refresh and try again.
                </AccordionContent>
              </AccordionItem>
            </Accordion>
          </section>
        </div>

        <aside className="space-y-4">
          <section className="rounded-md border p-4">
            <h3 className="text-sm font-medium">Keyboard Shortcuts</h3>
            <ul className="mt-2 space-y-1 text-sm text-muted-foreground">
              <li>
                <kbd className="rounded bg-muted px-1">Ctrl/Cmd</kbd>+<kbd className="rounded bg-muted px-1">J</kbd> Open search
              </li>
            </ul>
          </section>

          <section className="rounded-md border p-4">
            <h3 className="text-sm font-medium">Troubleshooting</h3>
            <ul className="mt-2 space-y-1 text-sm text-muted-foreground">
              <li>Not seeing tenders? Confirm you’re invited or check tender status.</li>
              <li>401/403 errors: re-login; ensure your session is active.</li>
              <li>Uploads failing: check file size/type and network stability.</li>
            </ul>
          </section>

          <section className="rounded-md border p-4">
            <h3 className="text-sm font-medium">Contact Support</h3>
            <form onSubmit={onSubmit} className="mt-3 space-y-3">
              <div className="grid gap-2">
                <label className="text-xs text-muted-foreground" htmlFor="subject">Subject</label>
                <input id="subject" name="subject" required className="rounded-md border bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring" placeholder="Brief subject" />
              </div>
              <div className="grid gap-2">
                <label className="text-xs text-muted-foreground" htmlFor="category">Category</label>
                <select id="category" name="category" className="rounded-md border bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                  <option value="General">General</option>
                  <option value="Tenders">Tenders</option>
                  <option value="RFQs">RFQs</option>
                  <option value="Prequalification">Pre-qualification</option>
                  <option value="Documents">Documents</option>
                  <option value="Account">Account</option>
                </select>
              </div>
              <div className="grid gap-2">
                <label className="text-xs text-muted-foreground" htmlFor="message">Message</label>
                <textarea id="message" name="message" required rows={4} className="rounded-md border bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring" placeholder="Describe the issue or question..." />
              </div>
              <button disabled={sending} className="inline-flex items-center rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                {sending ? 'Sending…' : 'Send message'}
              </button>
              {sent && (
                <p className={`text-xs ${sent === 'ok' ? 'text-green-600' : 'text-red-600'}`}>
                  {sent === 'ok' ? 'Your message has been sent. We will get back to you shortly.' : sent}
                </p>
              )}
              <p className="mt-2 text-xs text-muted-foreground">Hours: Mon–Fri, 8:00–17:00 EAT</p>
            </form>
          </section>
        </aside>
      </div>
    </div>
  )
}


