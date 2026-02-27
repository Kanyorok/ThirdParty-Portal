"use client"

import Link from "next/link"
import { useEffect, useMemo, useState } from "react"
import { motion } from "framer-motion"
import { ArrowUpRight, Sparkles } from "lucide-react"
import { Button } from "@/components/common/button"

type Action = { label: string; href: string }

export function WelcomeHeader({
  firstName,
  contextLabel,
  primaryAction,
  secondaryAction,
}: {
  firstName: string
  contextLabel: string
  primaryAction: Action
  secondaryAction: Action
}) {
  const [greeting, setGreeting] = useState<string>("")

  useEffect(() => {
    const hour = new Date().getHours()
    if (hour < 12) setGreeting("Good morning")
    else if (hour < 17) setGreeting("Good afternoon")
    else setGreeting("Good evening")
  }, [])

  const stableLabel = useMemo(() => contextLabel, [contextLabel])

  return (
    <header className="relative w-full overflow-hidden rounded-2xl border border-border/70 bg-gradient-to-r from-card via-muted/35 to-card px-5 py-5 md:px-6 md:py-6">
      <div className="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-primary/15" />
      <div className="pointer-events-none absolute -bottom-12 left-1/2 h-28 w-28 rounded-full bg-cyan-500/10" />

      <div className="relative flex flex-col gap-4">
        <div className="inline-flex w-fit items-center gap-2 rounded-full border border-primary/25 bg-primary/10 px-3 py-1 text-primary">
          <Sparkles className="h-3.5 w-3.5" />
          <span className="text-[11px] font-semibold tracking-tight">{stableLabel}</span>
        </div>

        <div className="space-y-1">
          <motion.h1
            initial={{ opacity: 0, y: 6 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, ease: "easeOut" }}
            className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl"
          >
            {greeting && `${greeting}, ${firstName || "there"}.`}
          </motion.h1>
          <p className="text-sm text-muted-foreground">
            Focus on the highest-impact actions and move opportunities forward faster.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-2.5">
          <Button
            asChild
            className="h-10 rounded-full border border-primary bg-primary px-4 text-xs font-semibold text-primary-foreground hover:bg-primary/90"
          >
            <Link href={primaryAction.href}>
              {primaryAction.label}
              <ArrowUpRight className="ml-1.5 h-4 w-4" />
            </Link>
          </Button>
          <Button
            asChild
            variant="outline"
            className="h-10 rounded-full border-border/70 bg-card px-4 text-xs font-semibold text-foreground hover:bg-accent/60"
          >
            <Link href={secondaryAction.href}>
              {secondaryAction.label}
            </Link>
          </Button>
        </div>
      </div>
    </header>
  )
}
