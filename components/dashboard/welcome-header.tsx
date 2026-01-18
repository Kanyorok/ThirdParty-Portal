'use client'

import Link from 'next/link'
import { useMemo } from 'react'
import { motion } from 'framer-motion'
import { ArrowRight, Sparkles } from 'lucide-react'

import { Button } from '@/components/common/button'

type Action = { label: string; href: string }

export function WelcomeHeader({
  firstName,
  contextLabel = "Dashboard",
  primaryAction,
  secondaryAction,
}: {
  firstName: string
  contextLabel?: string
  primaryAction: Action
  secondaryAction: Action
}) {
  const greeting = useMemo(() => {
    const hour = new Date().getHours()
    if (hour < 12) return 'Good morning'
    if (hour < 17) return 'Good afternoon'
    return 'Good evening'
  }, [])

  return (
    <header className="relative w-full overflow-hidden rounded-2xl border border-border/50 bg-card px-6 py-6 shadow-none md:px-8 md:py-7">
      <div className="relative z-10 flex flex-col gap-6">
        <div className="space-y-2.5">
          <div className="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/5 px-3 py-1.5 text-primary">
            <Sparkles className="h-3.5 w-3.5" />
            <span className="text-[12px] font-semibold tracking-tight">{contextLabel}</span>
          </div>

          <div className="flex flex-col gap-2 md:flex-row md:items-end md:justify-between md:gap-6">
            <div className="min-w-0">
              <motion.h1
                initial={{ opacity: 0, y: 8 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.35, ease: 'easeOut' }}
                className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl"
              >
                {greeting}, {firstName || 'there'}.
              </motion.h1>
              <p className="mt-1 text-sm text-muted-foreground">
                Here’s a quick overview and the next best actions to take.
              </p>
            </div>

            <div className="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
              <Button asChild className="h-11 rounded-xl px-4 text-sm font-semibold shadow-none">
                <Link href={primaryAction.href}>
                  {primaryAction.label}
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Link>
              </Button>
              <Button
                asChild
                variant="outline"
                className="h-11 rounded-xl border-border/60 bg-background px-4 text-sm font-semibold shadow-none hover:bg-muted"
              >
                <Link href={secondaryAction.href}>{secondaryAction.label}</Link>
              </Button>
            </div>
          </div>
        </div>
      </div>

      <div className="pointer-events-none absolute -left-10 -top-10 h-64 w-64 rounded-full bg-primary/[0.06] blur-[90px]" />
      <div className="pointer-events-none absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-sky-500/[0.06] blur-[110px]" />
    </header>
  )
}
