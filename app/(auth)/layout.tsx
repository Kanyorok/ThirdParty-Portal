import * as React from "react"

export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <React.Suspense fallback={<div>Loading…</div>}>
      {children}
    </React.Suspense>
  )
}


