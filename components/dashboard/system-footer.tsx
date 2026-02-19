"use client"

import { useEffect, useMemo, useState } from "react"

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
    <div className="w-full rounded-xl border border-border/60 bg-background/70 px-3 py-2 backdrop-blur">
      <div className="flex w-full flex-col gap-1.5 text-[11px] font-medium text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
        <div className="flex min-w-0 flex-wrap items-center gap-2">
          <span className="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-700">
            BRERP Logo
          </span>
          <span className="truncate">© {year} BR_ERP | Craft Silicon Limited</span>
        </div>

        <div className="flex min-w-0 flex-wrap items-center gap-2 sm:justify-end">
          <span className="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
            System Time: {now ? formatSystemTime(now) : "--:--:--"}
          </span>
          <span className="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
            version v1.0.0
          </span>
        </div>
      </div>
    </div>
  )
}
