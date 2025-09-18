"use client"

import Link from "next/link"
import { AlertCircle, Loader2 } from "lucide-react"
import { useRegisterForm } from "@/hooks/use-register"
import { FormField } from "@/components/signin/form-fields/login-fields"
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import { PasswordField } from "@/components/signin/form-fields/pwd"
// import { ContactSection } from "@/components/common/contact-us"
import { AuthHeader } from "@/components/layout/auth-header"

export function RegisterForm() {
  const {
    form,
    showPassword,
    setShowPassword,
    showConfirmPassword,
    setShowConfirmPassword,
    onSubmit,
    fieldStatuses,
    isSubmitting,
    isValid,
    errors,
    watchedFields,
    touchedFields,
  } = useRegisterForm()

  const { register } = form

  const inputBaseStyles =
    "w-full py-4 px-4 text-base border rounded-lg transition-all duration-200 focus:ring-2 focus:outline-none"

  const resolveInputStyles = (field: keyof typeof fieldStatuses, hasError?: boolean) => {
    if (hasError) return `${inputBaseStyles} border-red-300 bg-red-50 focus:ring-red-400`
    if (fieldStatuses[field] === "success") return `${inputBaseStyles} border-green-300 bg-green-50 focus:ring-green-400`
    return `${inputBaseStyles} border-gray-200 hover:border-gray-300 focus:ring-blue-600 focus:border-blue-600`
  }

  return (
    <div className="w-full max-w-4xl mx-auto p-6 sm:p-10">
      <AuthHeader />

      <form
        onSubmit={form.handleSubmit(onSubmit)}
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
            <Input
              id="phoneNumber"
              type="tel"
              placeholder="+254712345678"
              autoComplete="tel"
              {...register("phone")}
              aria-invalid={!!errors.phone}
              className={resolveInputStyles("phone", !!errors.phone)}
            />
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
            onPaste={(e) => e.preventDefault()}
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