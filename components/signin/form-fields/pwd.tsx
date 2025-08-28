"use client"

import type React from "react"
import { Eye, EyeOff, Check, X } from "lucide-react"
import { Input } from "@/components/common/input"
import { FormField } from "./login-fields"

interface PasswordFieldProps {
    id: string
    label: string
    placeholder: string
    value: string
    error?: string
    status: string
    showPassword: boolean
    onTogglePassword: () => void
    register: any
    helpText?: string
    showMatchIndicator?: boolean
    passwordsMatch?: boolean
    onPaste?: (e: React.ClipboardEvent) => void
}

export const PasswordField: React.FC<PasswordFieldProps> = ({
    id,
    label,
    placeholder,
    value,
    error,
    status,
    showPassword,
    onTogglePassword,
    register,
    helpText,
    showMatchIndicator = false,
    passwordsMatch = false,
    onPaste,
}) => {
    return (
        <FormField status={status} label={label} required helpText={helpText} error={error} id={id}>
            <div className="relative w-full">
                <Input
                    id={id}
                    type={showPassword ? "text" : "password"}
                    placeholder={placeholder}
                    className={`w-full py-4 pl-4 pr-12 text-lg border border-l-0 rounded-r-lg transition-all duration-200 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 ${error
                        ? "border-red-300 bg-red-50"
                        : status === "success"
                            ? "border-green-300 bg-green-50"
                            : "border-gray-200 hover:border-gray-300"
                        }`}
                    {...register}
                    aria-invalid={!!error}
                    aria-describedby={error ? `${id}-error` : `${id}-help`}
                    onPaste={onPaste}
                />

                <button
                    type="button"
                    onClick={onTogglePassword}
                    className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded"
                    aria-label={showPassword ? "Hide password" : "Show password"}
                >
                    {showPassword ? <EyeOff className="h-6 w-6" /> : <Eye className="h-6 w-6" />}
                </button>
            </div>

            {showMatchIndicator && value && (
                <div className="mt-3 pl-14">
                    {passwordsMatch ? (
                        <div className="flex items-center gap-2 text-green-600 text-sm" role="status" aria-live="polite">
                            <Check className="h-4 w-4" />
                            Passwords match
                        </div>
                    ) : (
                        <div className="flex items-center gap-2 text-red-600 text-sm" role="status" aria-live="polite">
                            <X className="h-4 w-4" />
                            Passwords do not match
                        </div>
                    )}
                </div>
            )}
        </FormField>
    )
}
