"use client"

import { AlertCircle } from "lucide-react"

interface FormErrorProps {
  message?: string
}

export function FormError({ message }: FormErrorProps) {
  if (!message) return null

  return (
    <div
      className="p-4 bg-red-50 border border-red-200 rounded-lg"
      role="alert"
      aria-live="polite"
    >
      <p className="text-red-600 text-sm flex items-center gap-2">
        <AlertCircle className="h-4 w-4 flex-shrink-0" />
        {message}
      </p>
    </div>
  )
}