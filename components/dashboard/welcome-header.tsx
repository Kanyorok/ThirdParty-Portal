"use client"

import Link from "next/link"
import { useEffect, useMemo, useState } from "react"
import { motion } from "framer-motion"
import { ArrowUpRight, Sparkles } from "lucide-react"
import { Button } from "@/components/common/button"

type Action = { label: string; href: string }

const headerShellVariants = {
  hidden: { opacity: 0, y: 16 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.4,
      ease: [0.22, 1, 0.36, 1],
      staggerChildren: 0.08,
      delayChildren: 0.05,
    },
  },
}

const headerItemVariants = {
  hidden: { opacity: 0, y: 8 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.32,
      ease: [0.22, 1, 0.36, 1],
    },
  },
}

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
    <motion.header
      variants={headerShellVariants}
      initial="hidden"
      animate="visible"
      className="dashboard-shell dashboard-shell--hero w-full rounded-2xl px-5 py-5 md:px-6 md:py-6"
    >
      <motion.div
        aria-hidden
        animate={{ opacity: [0.12, 0.2, 0.12], scale: [1, 1.03, 1] }}
        transition={{ duration: 7, repeat: Infinity, ease: "easeInOut" }}
        className="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-primary/15"
      />
      <motion.div
        aria-hidden
        animate={{ opacity: [0.08, 0.14, 0.08], y: [0, -2, 0] }}
        transition={{ duration: 6, repeat: Infinity, ease: "easeInOut", delay: 0.8 }}
        className="pointer-events-none absolute -bottom-12 left-1/2 h-28 w-28 rounded-full bg-cyan-500/10"
      />

      <div className="relative flex flex-col gap-4">
        <motion.div variants={headerItemVariants} className="dashboard-chip w-fit border-primary/25 bg-primary/10 px-3 py-1 text-primary">
          <Sparkles className="h-3.5 w-3.5" />
          <span className="text-[11px] font-semibold tracking-tight">{stableLabel}</span>
        </motion.div>

        <motion.div variants={headerItemVariants} className="space-y-1">
          <motion.h1
            className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl"
          >
            {greeting && `${greeting}, ${firstName || "there"}.`}
          </motion.h1>
          <p className="text-sm text-muted-foreground">
            Focus on the highest-impact actions and move opportunities forward faster.
          </p>
        </motion.div>

        <motion.div variants={headerItemVariants} className="flex flex-wrap items-center gap-2.5">
          <Button
            asChild
            className="dashboard-cta dashboard-cta--primary h-10 px-4"
          >
            <Link href={primaryAction.href}>
              {primaryAction.label}
              <ArrowUpRight className="ml-1.5 h-4 w-4" />
            </Link>
          </Button>
          <Button
            asChild
            variant="outline"
            className="dashboard-cta dashboard-cta--slate h-10 px-4"
          >
            <Link href={secondaryAction.href}>
              {secondaryAction.label}
            </Link>
          </Button>
        </motion.div>
      </div>
    </motion.header>
  )
}
