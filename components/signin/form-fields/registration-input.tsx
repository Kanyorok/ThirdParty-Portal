"use client"

import type { FieldError, UseFormRegisterReturn } from "react-hook-form"
import { cn } from "@/lib/utils"
import { Input } from "@/components/common/input"
import { FormField } from "@/components/signin/form-fields/login-fields"

interface RegistrationInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  id: "firstName" | "lastName" | "email" | "phone"
  label: string
  status: string
  error?: FieldError
  register: UseFormRegisterReturn
}

export function RegistrationInput({
  id,
  label,
  status,
  error,
  register,
  ...props
}: RegistrationInputProps) {
  const inputClasses = cn(
    "w-full py-4 px-4 text-base border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-600 focus:border-blue-600",
    {
      "border-red-300 bg-red-50": !!error,
      "border-green-300 bg-green-50": status === "success",
      "border-gray-200 hover:border-gray-300": status !== "success" && !error,
    },
  )

  return (
    <FormField
      status={status}
      label={label}
      required
      error={error?.message}
      id={id}
    >
      <Input
        id={id}
        className={inputClasses}
        {...register}
        {...props}
        aria-invalid={!!error}
        aria-describedby={error ? `${id}-error` : `${id}-help`}
      />
    </FormField>
  )
}