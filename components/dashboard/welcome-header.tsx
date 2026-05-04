"use client"

import Link from "next/link"
import { useEffect, useState } from "react"
import { motion, type Variants } from "framer-motion"
import { ArrowUpRight } from "lucide-react"
import { Button } from "@/components/common/button"

type Action = { label: string; href: string }

const headerEase = [0.22, 1, 0.36, 1] as const

const headerShellVariants: Variants = {
  hidden: { opacity: 0, y: 16 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.4,
      ease: headerEase,
      staggerChildren: 0.08,
      delayChildren: 0.05,
    },
  },
}

const headerItemVariants: Variants = {
  hidden: { opacity: 0, y: 8 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.32,
      ease: headerEase,
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

  return (
    <motion.header
      variants={headerShellVariants}
      initial="hidden"
      animate="visible"
      className="dashboard-shell dashboard-shell--hero w-full rounded-2xl px-5 py-4 md:px-6 md:py-5"
    >
      <div className="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div className="space-y-3">
          <motion.div variants={headerItemVariants} className="flex flex-wrap items-center gap-2">
            <span className="dashboard-chip dashboard-chip--neutral px-3 py-1 text-[11px] normal-case tracking-normal text-slate-700">
              {contextLabel}
            </span>
            <span className="dashboard-chip border-blue-200 bg-blue-50 px-3 py-1 text-[11px] normal-case tracking-normal text-blue-700">
              Dashboard
            </span>
          </motion.div>

          <motion.div variants={headerItemVariants} className="space-y-1">
            <motion.h1
              className="text-2xl font-semibold tracking-tight text-foreground sm:text-[2rem]"
            >
              {greeting && `${greeting}, ${firstName || "there"}.`}
            </motion.h1>
            <p className="max-w-2xl text-sm text-muted-foreground">
              Review pipeline movement, keep active work visible, and act on the next best step.
            </p>
          </motion.div>
        </div>

        <motion.div variants={headerItemVariants} className="flex flex-wrap items-center gap-2.5 lg:justify-end">
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
