import React from "react"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Eye, EyeOff, AlertCircle, Check, X } from "lucide-react"
import { Button } from "@/components/ui/button"

interface PasswordFieldProps {
  id: string
  label: string
  placeholder?: string
  value?: string
  error?: string
  status?: "default" | "error" | "success"
  showPassword?: boolean
  onTogglePassword?: () => void
  register?: any
  showMatchIndicator?: boolean
  passwordsMatch?: boolean
  onPaste?: (e: React.ClipboardEvent) => void
}

export function PasswordField({
  id,
  label,
  placeholder,
  value,
  error,
  status,
  showPassword,
  onTogglePassword,
  register,
  showMatchIndicator,
  passwordsMatch,
  onPaste,
}: PasswordFieldProps) {
  
  const getInputStyles = (hasError: boolean, isSuccess: boolean) => {
    if (hasError) {
      return "border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50 pr-10"
    }
    if (isSuccess) {
      return "border-green-300 focus:border-green-500 focus:ring-green-500/20 bg-green-50 pr-10"
    }
    return "border-gray-200 focus:border-blue-400 focus:ring-blue-400/20 pr-10"
  }

  const isError = status === "error" || !!error
  const isSuccess = status === "success" && !error

  return (
    <div className="space-y-2">
      <div className="flex justify-between items-baseline">
        <Label htmlFor={id} className="text-gray-700 font-medium">
          {label} <span className="text-red-500">*</span>
        </Label>
        
        {showMatchIndicator && value && (
          <span
            className={`text-xs font-medium flex items-center gap-1 animate-in fade-in ${
              passwordsMatch ? "text-green-600" : "text-red-500"
            }`}
          >
            {passwordsMatch ? (
              <>
                <Check className="h-3 w-3" /> Match
              </>
            ) : (
              <>
                <X className="h-3 w-3" /> Mismatch
              </>
            )}
          </span>
        )}
      </div>

      <div className="relative group">
        <Input
          id={id}
          type={showPassword ? "text" : "password"}
          placeholder={placeholder}
          {...(register || {})}
          aria-invalid={!!error}
          className={getInputStyles(isError, isSuccess)}
          onPaste={onPaste}
        />
        
        <Button
          type="button"
          variant="ghost"
          size="icon"
          className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent text-gray-500 hover:text-gray-700 transition-colors"
          onClick={onTogglePassword}
          aria-label={showPassword ? "Hide password" : "Show password"}
        >
          {showPassword ? (
            <EyeOff className="h-4 w-4" />
          ) : (
            <Eye className="h-4 w-4" />
          )}
        </Button>
      </div>

      {error && (
        <p className="text-sm text-red-500 mt-1 flex items-center gap-1 animate-in slide-in-from-top-1">
          <AlertCircle className="h-4 w-4" />
          {error}
        </p>
      )}
    </div>
  )
}
