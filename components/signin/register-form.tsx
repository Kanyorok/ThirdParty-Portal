"use client"

import { useState } from "react"
import Link from "next/link"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { AlertCircle, Loader2 } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { FormField } from "@/components/signin/form-field"
import { PasswordField } from "@/components/signin/password-field"
import { AuthHeader } from "@/components/signin/auth-header"
// import { ContactSection } from "@/components/signin/contact-section"
import { registerUser } from "@/actions/auth"

const registerSchema = z
  .object({
    firstName: z.string().min(2, "First name is required"),
    lastName: z.string().min(2, "Last name is required"),
    email: z.string().email("Please enter a valid email address"),
    phone: z.string().min(10, "Phone number is required"),
    password: z
      .string()
      .min(8, "Password must be at least 8 characters")
      .regex(/[A-Z]/, "Must mention one uppercase letter")
      .regex(/[a-z]/, "Must mention one lowercase letter")
      .regex(/[0-9]/, "Must mention one number"),
    confirmPassword: z.string(),
  })
  .refine((data) => data.password === data.confirmPassword, {
    message: "Passwords do not match",
    path: ["confirmPassword"],
  })

type RegisterValues = z.infer<typeof registerSchema>

export function RegisterForm() {
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)

  const {
    register,
    handleSubmit,
    watch,
    formState: { errors, isSubmitting, isValid, touchedFields },
  } = useForm<RegisterValues>({
    resolver: zodResolver(registerSchema),
    mode: "onChange",
  })

  // Watch fields for dynamic styling
  const watchedFields = watch()

  // Helper to determine field status
  const getFieldStatus = (fieldName: keyof RegisterValues) => {
    if (errors[fieldName]) return "error"
    if (touchedFields[fieldName] && !errors[fieldName] && watchedFields[fieldName]) return "success"
    return "default"
  }

  const fieldStatuses = {
    firstName: getFieldStatus("firstName"),
    lastName: getFieldStatus("lastName"),
    email: getFieldStatus("email"),
    phone: getFieldStatus("phone"),
    password: getFieldStatus("password"),
    confirmPassword: getFieldStatus("confirmPassword"),
  }

  const onSubmit = async (data: RegisterValues) => {
    // try {
    await registerUser(data)
    // } catch (error) {
    //   // Error is handled by the action which throws
    //   console.error(error)
    // }
  }

  const resolveInputStyles = (fieldName: keyof RegisterValues, hasError: boolean) => {
    if (hasError) {
      return "border-red-300 focus:border-red-500 focus:ring-red-500/20 bg-red-50"
    }
    if (touchedFields[fieldName] && !errors[fieldName] && watchedFields[fieldName]) {
      return "border-green-300 focus:border-green-500 focus:ring-green-500/20 bg-green-50"
    }
    return "border-gray-200 focus:border-blue-400 focus:ring-blue-400/20"
  }

  return (
    <div className="w-full max-w-2xl mx-auto bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-800 p-8">
      <AuthHeader />

      <form
        onSubmit={handleSubmit(onSubmit)}
        noValidate
        aria-label="Registration form"
        className="space-y-8"
      >
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormField
            status={fieldStatuses.firstName}
            label="First Name"
            required
            error={errors.firstName?.message}
            id="firstName"
          >
            <Input
              id="firstName"
              type="text"
              placeholder="e.g. Mary"
              {...register("firstName")}
              aria-invalid={!!errors.firstName}
              className={resolveInputStyles("firstName", !!errors.firstName)}
            />
          </FormField>

          <FormField
            status={fieldStatuses.lastName}
            label="Last Name"
            required
            error={errors.lastName?.message}
            id="lastName"
          >
            <Input
              id="lastName"
              type="text"
              placeholder="e.g. Ochieng"
              {...register("lastName")}
              aria-invalid={!!errors.lastName}
              className={resolveInputStyles("lastName", !!errors.lastName)}
            />
          </FormField>

          <FormField
            status={fieldStatuses.email}
            label="Email Address"
            required
            error={errors.email?.message}
            id="email"
          >
            <Input
              id="email"
              type="email"
              placeholder="you@example.com"
              autoComplete="email"
              {...register("email")}
              aria-invalid={!!errors.email}
              className={resolveInputStyles("email", !!errors.email)}
            />
          </FormField>

          <FormField
            status={fieldStatuses.phone}
            label="Phone Number"
            required
            error={errors.phone?.message}
            id="phoneNumber"
          >
            {(() => {
              const phoneReg = register("phone")

              const handleBeforeInput = (e: any) => {
                // Prevent typing any non-digit characters
                const data = e?.data
                if (data && /\D/.test(data)) {
                  e.preventDefault()
                }
              }

              const handlePaste = (e: any) => {
                const pasted = e?.clipboardData?.getData?.("text") || (window as any).clipboardData?.getData?.("Text") || ""
                if (!pasted) return
                const cleaned = pasted.replace(/\D/g, "")
                if (cleaned === pasted) return // no invalid chars
                e.preventDefault()
                const target = e.target as HTMLInputElement
                const start = target.selectionStart ?? target.value.length
                const end = target.selectionEnd ?? start
                const newVal = target.value.slice(0, start) + cleaned + target.value.slice(end)
                const nativeSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, "value")?.set
                if (nativeSetter) {
                  nativeSetter.call(target, newVal)
                } else {
                  target.value = newVal
                }
                const ev = new Event("input", { bubbles: true })
                target.dispatchEvent(ev)
                // let react-hook-form know about the change
                if (phoneReg.onChange) phoneReg.onChange({ target } as any)
              }

              const handleChange = (e: any) => {
                const cleaned = (e.target.value || "").replace(/\D/g, "")
                if (cleaned !== e.target.value) {
                  const nativeSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, "value")?.set
                  if (nativeSetter) nativeSetter.call(e.target, cleaned)
                  else e.target.value = cleaned
                  const ev = new Event("input", { bubbles: true })
                  e.target.dispatchEvent(ev)
                }
                if (phoneReg.onChange) phoneReg.onChange(e)
              }

              return (
                <Input
                  id="phoneNumber"
                  type="tel"
                  placeholder="254712345678"
                  autoComplete="tel"
                  {...phoneReg}
                  aria-invalid={!!errors.phone}
                  onBeforeInput={handleBeforeInput}
                  onPaste={handlePaste}
                  onChange={handleChange}
                  className={resolveInputStyles("phone", !!errors.phone)}
                />
              )
            })()}
          </FormField>

          <PasswordField
            id="password"
            label="Password"
            placeholder="Enter your password"
            value={watchedFields.password}
            error={errors.password?.message}
            status={fieldStatuses.password}
            showPassword={showPassword}
            onTogglePassword={() => setShowPassword(!showPassword)}
            register={register("password")}
          />

          <PasswordField
            id="confirmPassword"
            label="Confirm Password"
            placeholder="Confirm your password"
            value={watchedFields.confirmPassword}
            error={errors.confirmPassword?.message}
            status={fieldStatuses.confirmPassword}
            showPassword={showConfirmPassword}
            onTogglePassword={() => setShowConfirmPassword(!showConfirmPassword)}
            register={register("confirmPassword")}
            showMatchIndicator={touchedFields.confirmPassword}
            passwordsMatch={
              watchedFields.confirmPassword === watchedFields.password &&
              watchedFields.confirmPassword !== ""
            }
            onPaste={(e: React.ClipboardEvent) => e.preventDefault()}
          />
        </div>

        {errors.root?.message && (
          <div
            className="p-4 bg-red-50 border border-red-200 rounded-lg"
            role="alert"
            aria-live="polite"
          >
            <p className="text-red-600 text-sm flex items-center gap-2">
              <AlertCircle className="h-4 w-4 flex-shrink-0" />
              {errors.root.message}
            </p>
          </div>
        )}

        <div className="pt-6 flex flex-col sm:flex-row gap-4 w-full">
          <Link
            href="/signin"
            className="flex-1 inline-flex items-center justify-center min-h-[56px] px-6 text-base font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 transition-all text-center"
          >
            Back to Login
          </Link>

          <Button
            type="submit"
            disabled={isSubmitting || !isValid}
            className="flex-1 inline-flex items-center justify-center gap-2 min-h-[56px] px-6 text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-all text-center"
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
        </div>

        <div className="text-center pt-4 text-base text-gray-600">
          Already have an account?{" "}
          <Link
            href="/signin"
            className="text-blue-600 hover:underline font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded"
          >
            Sign in
          </Link>
        </div>

        {/* <div className="mt-10">
          <ContactSection />
        </div> */}
      </form>
    </div>
  )
}