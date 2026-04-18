"use client"

import { useEffect, useMemo, useState } from "react"
import { CLIENT_APP_NAME_STRING } from "@/config/client-config"

function pad(value: number) {
  return String(value).padStart(2, "0")
}

function formatSystemTime(date: Date) {
  const day = pad(date.getDate())
  const month = pad(date.getMonth() + 1)
  const year = date.getFullYear()
  const hours = pad(date.getHours())
  const minutes = pad(date.getMinutes())
  const seconds = pad(date.getSeconds())
  return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`
}

export function SystemFooter() {
  const [now, setNow] = useState<Date | null>(null)

  useEffect(() => {
    setNow(new Date())
    const timer = setInterval(() => setNow(new Date()), 1000)
    return () => clearInterval(timer)
  }, [])

  const year = useMemo(() => (now ? now.getFullYear() : new Date().getFullYear()), [now])

  return (
    <div className="w-full">
      <div className="flex w-full flex-col gap-1.5 border-t border-border/60 pt-2 text-[11px] font-medium text-slate-600 sm:flex-row sm:items-center sm:justify-between">
        <div className="min-w-0">
          <span className="truncate">© {year} {CLIENT_APP_NAME_STRING} | Craft Silicon Limited</span>
        </div>

        <div className="flex min-w-0 flex-wrap items-center gap-1.5 sm:justify-end">
          <span className="uppercase tracking-[0.12em] text-[10px] text-slate-500">System Time</span>
          <span className="font-mono font-semibold text-slate-700">{now ? formatSystemTime(now) : "--:--:--"}</span>
          <span className="text-slate-300">|</span>
          <span className="uppercase tracking-[0.12em] text-[10px] text-slate-500">Version</span>
          <span className="font-semibold text-slate-700">v1.0.0</span>
        </div>
      </div>
    </div>
  )
}
