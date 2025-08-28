import type React from "react"
import { Check } from "lucide-react"
import { Label } from "@/components/common/label"

interface FormFieldProps {
    children: React.ReactNode
    status: string
    label: string
    required?: boolean
    helpText?: string
    error?: string | null
id: string
}

export const FormField: React.FC<FormFieldProps> = ({
    children,
    status,
    label,
    required = false,
    helpText,
    error,
    id,
}) => {
    const fieldId = `${id}-field`
    const errorId = `${id}-error`
    const helpId = `${id}-help`

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between">
                <Label htmlFor={fieldId} className="text-base font-medium text-gray-800">
                    {label}
                    {required && (
                        <span className="text-red-500 text-sm ml-1" aria-label="required">
                            *
                        </span>
                    )}
                </Label>
                {status === "success" && (
                    <div
                        className="flex items-center gap-2 text-sm text-green-600 font-medium"
                        role="status"
                        aria-label="Field is valid"
                    >
                        <Check className="h-4 w-4" />
                    </div>
                )}
            </div>

            {children}

            {helpText && !error && (
                <p id={helpId} className="text-sm text-gray-500 mt-2 px-1">
                    {helpText}
                </p>
            )}

            {error && (
                <p id={errorId} className="text-red-600 text-sm mt-2 px-1 animate-pulse" role="alert" aria-live="polite">
                    {error}
                </p>
            )}
        </div>
    )
}
