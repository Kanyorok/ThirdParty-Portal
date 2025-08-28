"use client"

import { Loader2 } from "lucide-react"
import { Button } from "@/components/common/button"

interface FormButtonProps {
  isSubmitting: boolean
  isValid: boolean
}

export function FormButton({ isSubmitting, isValid }: FormButtonProps) {
  return (
    <Button
      type="submit"
      disabled={isSubmitting || !isValid}
      className="flex-1 inline-flex items-center justify-center gap-2 min-h-[56px] px-6 text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-all text-center"
      aria-describedby={isSubmitting ? "submit-status" : undefined}
    >
      {isSubmitting ? (
        <>
          <Loader2 className="h-5 w-5 animate-spin" />
          <span id="submit-status">Creating Account...</span>
        </>
      ) : (
        "Create Account"
      )}
    </Button>
  )
}