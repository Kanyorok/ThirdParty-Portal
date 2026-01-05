import React from "react"
import { Label } from "@/components/ui/label"
import { AlertCircle, CheckCircle } from "lucide-react"

interface FormFieldProps {
  children: React.ReactNode
  label: string
  id: string
  required?: boolean
  error?: string
  status?: "default" | "error" | "success"
}

export function FormField({
  children,
  label,
  id,
  required,
  error,
  status = "default",
}: FormFieldProps) {
  return (
    <div className="space-y-2">
      <div className="flex justify-between items-baseline">
        <Label htmlFor={id} className="text-gray-700 font-medium">
          {label} {required && <span className="text-red-500 ml-1">*</span>}
        </Label>
        {status === "success" && !error && (
          <span className="text-xs text-green-600 font-medium flex items-center gap-1 animate-in fade-in slide-in-from-left-2">
            <CheckCircle className="h-3 w-3" />
            Valid
          </span>
        )}
      </div>
      
      <div className="relative">
        {children}
        
        {status === "error" && (
          <div className="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 pointer-events-none">
            <AlertCircle className="h-5 w-5" />
          </div>
        )}
      </div>

      {error ? (
        <p 
          id={`${id}-error`} 
          className="text-sm text-red-500 mt-1 flex items-center gap-1 animate-in slide-in-from-top-1"
          role="alert"
        >
          {error}
        </p>
      ) : (
        /* Reserve space to prevent layout jump if desired, or just null */
        null
      )}
    </div>
  )
}
