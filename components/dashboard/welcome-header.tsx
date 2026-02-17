"use client"

import { useEffect, useMemo, useState } from "react"
import { motion } from "framer-motion"
import { Sparkles } from "lucide-react"

type Action = { label: string; href: string }

export function WelcomeHeader({
  firstName,
  contextLabel,
  primaryAction: _primaryAction,
  secondaryAction: _secondaryAction,
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
    <header className="relative w-full rounded-2xl border border-border/50 bg-muted/10 px-5 py-4">
      <div className="flex flex-col gap-3">
        <div className="relative inline-block pr-28 sm:pr-32">
          <motion.h1
            initial={{ opacity: 0, y: 6 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3, ease: "easeOut" }}
            className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl"
          >
            {greeting && `${greeting}, ${firstName || "there"}.`}
          </motion.h1>
          <div className="absolute right-0 top-0 z-10 -translate-y-2 sm:-translate-y-3">
            <div className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-primary shadow-[0_10px_22px_rgba(59,130,246,0.16)]">
              <Sparkles className="h-3.5 w-3.5" />
              <span className="text-[11px] font-semibold tracking-tight">
                {stableLabel}
              </span>
            </div>
          </div>
        </div>

        
      </div>
    </header>
  )
}
