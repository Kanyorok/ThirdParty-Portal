"use client"

import * as React from "react"
import { AlertCircle, CheckCircle2, Eye, EyeOff } from "lucide-react"
import Loading from "@/components/common/custom_loader"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/common/select"
import { useRouter } from "next/navigation"
import { cn } from "@/lib/utils"
import { type RegisterFormInputs, type RegisterRole, type RegisterThirdPartyResult, useRegisterForm } from "@/hooks/use-register"

const ORGANIZATION_FIELDS = [
  "Name",
  "BusinessType",
  "RegistrationNumber",
  "TaxPIN",
  "Country",
  "Location",
  "Email",
  "Phone",
  "types"
] as const

const ADMIN_FIELDS = [
  "user_FirstName",
  "user_LastName",
  "user_Email",
  "user_Phone",
  "user_Gender",
  "user_Password",
  "user_Password_confirmation"
] as const

const ROLE_OPTIONS: Array<{ id: RegisterRole; label: string }> = [
  { id: "SU", label: "Supplier" },
  { id: "TN", label: "Tenant" },
  { id: "CU", label: "Customer" },
]

const labelStyle = "mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-600"
const inputStyle = "h-11 rounded-lg border-slate-300 bg-white text-slate-900 transition-colors focus-visible:border-slate-900 focus-visible:ring-slate-200"
const selectStyle = "h-11 w-full rounded-lg border-slate-300 bg-white text-slate-900 transition-colors focus:border-slate-900 focus:ring-slate-200"
const errorStyle = "mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600"

function FieldLabel({ children, required }: { children: React.ReactNode; required?: boolean }) {
  return (
    <label className={labelStyle}>
      {children}
      {required ? <span className="text-rose-600"> *</span> : null}
    </label>
  )
}

function FieldError({ message }: { message?: string }) {
  if (!message) return null
  return (
    <p className={errorStyle}>
      <AlertCircle className="h-3 w-3" />
      {message}
    </p>
  )
}

function SectionTitle({ title, description }: { title: string; description?: string }) {
  return (
    <div className="space-y-1">
      <h2 className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{title}</h2>
      {description ? <p className="text-sm text-slate-500">{description}</p> : null}
    </div>
  )
}

export default function RegisterForm() {
  const router = useRouter()
  const [authError, setAuthError] = React.useState<string | null>(null)
  const [success, setSuccess] = React.useState(false)
  const [successTitle, setSuccessTitle] = React.useState("Registration successful")
  const [successDescription, setSuccessDescription] = React.useState("Your account has been created successfully.")
  const [submitState, setSubmitState] = React.useState<"idle" | "posting" | "success" | "error">("idle")
  const [submitNotice, setSubmitNotice] = React.useState<string | null>(null)
  const [showPassword, setShowPassword] = React.useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = React.useState(false)

  const {
    form,
    errors,
    metadata,
    isLoadingMetadata,
    metadataError,
    isSubmitting,
    toggleType,
    selectedTypes,
    isSupplier,
    isTenant,
    registerThirdParty
  } = useRegisterForm()

  const createUser = form.watch("createUser")
  const businessTypeValue = form.watch("BusinessType")
  const supplierCategoryValue = form.watch("supplier_category_id")
  const countryValue = form.watch("Country")
  const locationValue = form.watch("Location")
  const genderValue = form.watch("user_Gender")

  const isPosting = submitState === "posting"
  const isBusy = isSubmitting || isPosting

  const getFieldsToValidate = React.useCallback(() => {
    const fields = [...ORGANIZATION_FIELDS] as string[]
    if (isSupplier) fields.push("supplier_category_id")
    if (isTenant) fields.push("user_Remarks")
    if (createUser) fields.push(...ADMIN_FIELDS)
    return fields
  }, [createUser, isSupplier, isTenant])

  const getFirstFieldError = React.useCallback((fields: string[]) => {
    const errorsMap = form.formState.errors as Record<string, { message?: string }>
    for (const field of fields) {
      if (errorsMap[field]?.message) {
        return { field, message: errorsMap[field].message as string }
      }
    }
    const fallbackError = Object.entries(errorsMap)[0]
    if (!fallbackError) return null
    return {
      field: fallbackError[0],
      message: fallbackError[1]?.message || "Please fix the validation errors before submitting."
    }
  }, [form.formState.errors])

  const extractRegisterMessage = React.useCallback((payload?: Record<string, any> | null) => {
    const candidates = [
      payload?.message,
      payload?.data?.message,
      payload?.data?.data?.message
    ]
    for (const candidate of candidates) {
      if (typeof candidate === "string" && candidate.trim()) return candidate.trim()
    }
    return ""
  }, [])

  const pickVerificationEmail = React.useCallback((values: RegisterFormInputs) => {
    const userEmail = values.user_Email?.trim()
    if (userEmail) return userEmail
    const organizationEmail = values.Email?.trim()
    return organizationEmail || null
  }, [])

  const handleRegistrationSuccess = React.useCallback(
    (values: RegisterFormInputs, registerResponse: RegisterThirdPartyResult) => {
      const backendMessage = extractRegisterMessage(registerResponse.payload)
      const verificationRequired =
        Boolean(registerResponse.verifyEmailUrl) ||
        values.createUser ||
        /verify|verification|confirm.+email/i.test(backendMessage)
      const verificationEmail = pickVerificationEmail(values)

      setSubmitState("success")
      setSubmitNotice("Registration completed.")

      if (verificationRequired) {
        setSuccessTitle("Check your email")
        setSuccessDescription(
          verificationEmail
            ? `We sent a verification link to ${verificationEmail}.`
            : "We sent a verification link to your email address."
        )
      } else {
        setSuccessTitle("Registration successful")
        setSuccessDescription("Your account has been created successfully.")
      }

      setSuccess(true)
    },
    [extractRegisterMessage, pickVerificationEmail]
  )

  const handleSubmitForm = async (e: React.FormEvent) => {
    e.preventDefault()
    setAuthError(null)
    setSubmitNotice(null)
    if (isBusy) return

    setSubmitState("idle")
    const fieldsToValidate = getFieldsToValidate()
    const valid = await form.trigger(fieldsToValidate as any)

    if (!valid) {
      const firstError = getFirstFieldError(fieldsToValidate)
      setAuthError(firstError?.message ?? "Please fix the validation errors before submitting.")
      if (firstError?.field) form.setFocus(firstError.field as any)
      return
    }

    try {
      setSubmitState("posting")
      setSubmitNotice("Submitting...")
      const values = form.getValues()
      const registerResponse = await registerThirdParty(values)
      if (registerResponse?.success) handleRegistrationSuccess(values, registerResponse)
    } catch (error: unknown) {
      setSubmitState("error")
      setSubmitNotice("Registration could not be completed.")
      setAuthError(error instanceof Error ? error.message : "Registration could not be completed. Please try again.")
    }
  }

  const getSubmitButtonLabel = (defaultLabel: string) => {
    if (submitState === "posting") return "Submitting..."
    if (submitState === "success") return "Submitted"
    if (submitState === "error") return "Try Again"
    return defaultLabel
  }

  const submitButtonTone = cn(
    "h-12 w-full rounded-lg px-5 text-sm font-semibold tracking-tight disabled:cursor-not-allowed disabled:opacity-90 sm:w-auto sm:min-w-[220px]",
    submitState === "success" && "bg-emerald-600 text-white hover:bg-emerald-600",
    submitState === "error" && "bg-rose-600 text-white hover:bg-rose-700",
    (submitState === "idle" || submitState === "posting") && "bg-slate-900 text-white hover:bg-slate-800"
  )

  if (isLoadingMetadata) {
    return (
      <Loading
        message="Loading form"
        fullScreen={false}
        className="py-28"
      />
    )
  }

  if (metadataError) {
    return (
      <div className="py-16 text-center">
        <p className="text-sm font-medium text-rose-700">{metadataError}</p>
      </div>
    )
  }

  if (success) {
    return (
      <div className="mx-auto max-w-md py-8 text-center">
        <CheckCircle2 className="mx-auto mb-4 h-12 w-12 text-emerald-600" />
        <h2 className="mb-2 text-2xl font-semibold tracking-tight text-slate-900">{successTitle}</h2>
        <p className="mb-6 text-sm text-slate-600">{successDescription}</p>
        <Button
          onClick={() => router.replace("/signin")}
          className="h-11 w-full rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800"
        >
          Sign In
        </Button>
      </div>
    )
  }

  return (
    <div className="mx-auto max-w-6xl">
      <div className="relative">
        {isPosting ? (
          <div className="absolute inset-0 z-20 bg-white/85 backdrop-blur-[1px]">
            <Loading
              message="Submitting your registration"
              fullScreen={false}
              className="h-full bg-transparent py-0"
            />
          </div>
        ) : null}

        <form
          onSubmit={handleSubmitForm}
          className={cn("space-y-10", isPosting && "pointer-events-none")}
          noValidate
          aria-busy={isPosting}
        >
          <section className="space-y-4">
            <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
              <div className="w-full lg:max-w-3xl">
                <div aria-label="Business role" className="grid grid-cols-1 gap-2 sm:grid-cols-3" role="group">
                  {ROLE_OPTIONS.map((type) => (
                    <button
                      key={type.id}
                      type="button"
                      onClick={() => toggleType(type.id)}
                      className={cn(
                        "h-11 rounded-lg border px-3 text-sm font-semibold transition-colors",
                        selectedTypes?.includes(type.id)
                          ? "border-slate-900 bg-slate-900 text-white"
                          : "border-slate-300 bg-white text-slate-700 hover:border-slate-500 hover:text-slate-900"
                      )}
                    >
                      {type.label}
                    </button>
                  ))}
                </div>
                <FieldError message={errors.types?.message as string | undefined} />
              </div>

              <div className="w-full lg:max-w-xs">
                <button
                  type="button"
                  onClick={() => form.setValue("createUser", !createUser)}
                  className={cn(
                    "flex h-11 w-full items-center justify-between rounded-lg border px-3 text-sm font-medium transition-colors",
                    createUser
                      ? "border-slate-900 bg-slate-900 text-white"
                      : "border-slate-300 bg-white text-slate-700 hover:border-slate-500"
                  )}
                  role="switch"
                  aria-checked={createUser}
                  aria-label="Create user login access"
                >
                  <span>{createUser ? "Create user login: On" : "Create user login: Off"}</span>
                  <span className={cn("relative h-6 w-11 rounded-full transition-colors", createUser ? "bg-white/20" : "bg-slate-200")}>
                    <span
                      className={cn(
                        "absolute top-0.5 h-5 w-5 rounded-full shadow-sm transition-all",
                        createUser ? "right-0.5 bg-white" : "left-0.5 bg-slate-500"
                      )}
                    />
                  </span>
                </button>
              </div>
            </div>
          </section>

          {authError ? (
            <div className="flex items-start gap-2 border-l-2 border-rose-500 pl-3 text-rose-700">
              <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
              <span className="text-sm font-medium">{authError}</span>
            </div>
          ) : null}

          {submitNotice && submitState !== "error" ? (
            <div className="flex items-start gap-2 border-l-2 border-emerald-500 pl-3 text-emerald-700">
              <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" />
              <span className="text-sm font-medium">{submitNotice}</span>
            </div>
          ) : null}

          <section className="space-y-4 border-t border-slate-200 pt-7">
            <SectionTitle title="Business" />
            <div className="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">
              <div className="md:col-span-2">
                <FieldLabel required>Legal Name</FieldLabel>
                <Input required autoComplete="organization" {...form.register("Name")} placeholder="Daniel Logistics Limited" className={inputStyle} />
                <FieldError message={errors.Name?.message as string | undefined} />
              </div>

              <div>
                <FieldLabel required>Registration Number</FieldLabel>
                <Input required {...form.register("RegistrationNumber")} placeholder="C123456" className={inputStyle} />
                <FieldError message={errors.RegistrationNumber?.message as string | undefined} />
              </div>

              <div>
                <FieldLabel required>Business Type</FieldLabel>
                <Select
                  value={businessTypeValue || undefined}
                  onValueChange={(value) => form.setValue("BusinessType", value, { shouldDirty: true, shouldValidate: true })}
                >
                  <SelectTrigger className={selectStyle} aria-required="true">
                    <SelectValue placeholder="Select business type" />
                  </SelectTrigger>
                  <SelectContent>
                    {metadata.businessTypes.map((bt) => (
                      <SelectItem key={bt.value} value={bt.value}>
                        {bt.description}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FieldError message={errors.BusinessType?.message as string | undefined} />
              </div>

              <div>
                <FieldLabel required>Tax PIN</FieldLabel>
                <Input required {...form.register("TaxPIN")} placeholder="P051234567X" className={inputStyle} />
                <FieldError message={errors.TaxPIN?.message as string | undefined} />
              </div>

              {isSupplier ? (
                <div className="md:col-span-2">
                  <FieldLabel required>Supplier Category</FieldLabel>
                  <Select
                    value={supplierCategoryValue != null ? String(supplierCategoryValue) : undefined}
                    onValueChange={(value) => form.setValue("supplier_category_id", Number(value), { shouldDirty: true, shouldValidate: true })}
                  >
                    <SelectTrigger className={selectStyle} aria-required="true">
                      <SelectValue placeholder="Select supplier category" />
                    </SelectTrigger>
                    <SelectContent>
                      {metadata.supplierCategories.map((cat) => (
                        <SelectItem key={cat.id} value={String(cat.id)}>
                          {cat.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FieldError message={errors.supplier_category_id?.message as string | undefined} />
                </div>
              ) : null}
            </div>
          </section>

          <section className="space-y-4 border-t border-slate-200 pt-7">
            <SectionTitle title="Contact" />
            <div className="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">
              <div>
                <FieldLabel required>Business Email</FieldLabel>
                <Input type="email" required autoComplete="email" {...form.register("Email")} placeholder="procurement@company.com" className={inputStyle} />
                <FieldError message={errors.Email?.message as string | undefined} />
              </div>

              <div>
                <FieldLabel required>Phone Number</FieldLabel>
                <Input type="tel" inputMode="tel" pattern="[+]?[0-9]{8,15}" required autoComplete="tel" {...form.register("Phone")} placeholder="+254712345678" className={inputStyle} />
                <p className="mt-1 text-xs text-slate-500">Use 8 to 15 digits, optional leading +.</p>
                <FieldError message={errors.Phone?.message as string | undefined} />
              </div>

              <div>
                <FieldLabel required>Country</FieldLabel>
                <Select
                  value={countryValue || undefined}
                  onValueChange={(value) => form.setValue("Country", value, { shouldDirty: true, shouldValidate: true })}
                >
                  <SelectTrigger className={selectStyle} aria-required="true">
                    <SelectValue placeholder="Select country" />
                  </SelectTrigger>
                  <SelectContent>
                    {metadata.countries.map((c) => (
                      <SelectItem key={c.code} value={c.code}>
                        {c.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FieldError message={errors.Country?.message as string | undefined} />
              </div>

              <div>
                <FieldLabel required>Location</FieldLabel>
                <Select
                  value={locationValue != null ? String(locationValue) : undefined}
                  onValueChange={(value) => form.setValue("Location", Number(value), { shouldDirty: true, shouldValidate: true })}
                >
                  <SelectTrigger className={selectStyle} aria-required="true">
                    <SelectValue placeholder="Select location" />
                  </SelectTrigger>
                  <SelectContent>
                    {metadata.localities.map((l) => (
                      <SelectItem key={l.id} value={String(l.id)}>
                        {l.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FieldError message={errors.Location?.message as string | undefined} />
              </div>

              {isTenant ? (
                <div className="md:col-span-2">
                  <FieldLabel required>Tenant Remarks</FieldLabel>
                  <Input required {...form.register("user_Remarks")} placeholder="Setup notes" className={inputStyle} />
                  <FieldError message={errors.user_Remarks?.message as string | undefined} />
                </div>
              ) : null}
            </div>
          </section>

          {createUser ? (
            <section className="space-y-4 border-t border-slate-200 pt-7">
              <SectionTitle title="User Access" />
              <div className="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">
                <div>
                  <FieldLabel required>First Name</FieldLabel>
                  <Input required autoComplete="given-name" {...form.register("user_FirstName")} placeholder="Daniel" className={inputStyle} />
                  <FieldError message={errors.user_FirstName?.message as string | undefined} />
                </div>

                <div>
                  <FieldLabel required>Last Name</FieldLabel>
                  <Input required autoComplete="family-name" {...form.register("user_LastName")} placeholder="Kitonga" className={inputStyle} />
                  <FieldError message={errors.user_LastName?.message as string | undefined} />
                </div>

                <div>
                  <FieldLabel required>User Email</FieldLabel>
                  <Input type="email" required autoComplete="email" {...form.register("user_Email")} placeholder="admin@company.com" className={inputStyle} />
                  <FieldError message={errors.user_Email?.message as string | undefined} />
                </div>

                <div>
                  <FieldLabel required>User Phone</FieldLabel>
                  <Input type="tel" inputMode="tel" pattern="[+]?[0-9]{8,15}" required autoComplete="tel" {...form.register("user_Phone")} placeholder="+254712345678" className={inputStyle} />
                  <FieldError message={errors.user_Phone?.message as string | undefined} />
                </div>

                <div className="md:col-span-2">
                  <FieldLabel required>Gender</FieldLabel>
                  <Select
                    value={genderValue || undefined}
                    onValueChange={(value) => form.setValue("user_Gender", value, { shouldDirty: true, shouldValidate: true })}
                  >
                    <SelectTrigger className={selectStyle} aria-required="true">
                      <SelectValue placeholder="Select gender" />
                    </SelectTrigger>
                    <SelectContent>
                      {metadata.genders.map((g) => (
                        <SelectItem key={g.value} value={g.value}>
                          {g.description}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FieldError message={errors.user_Gender?.message as string | undefined} />
                </div>

                <div className="relative">
                  <FieldLabel required>Password</FieldLabel>
                  <Input
                    type={showPassword ? "text" : "password"}
                    required
                    autoComplete="new-password"
                    {...form.register("user_Password")}
                    placeholder="At least 8 characters"
                    className={cn(inputStyle, "pr-10")}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-[33px] text-slate-400 transition-colors hover:text-slate-700"
                    aria-label={showPassword ? "Hide password" : "Show password"}
                  >
                    {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                  <FieldError message={errors.user_Password?.message as string | undefined} />
                </div>

                <div className="relative">
                  <FieldLabel required>Confirm Password</FieldLabel>
                  <Input
                    type={showConfirmPassword ? "text" : "password"}
                    required
                    autoComplete="new-password"
                    {...form.register("user_Password_confirmation")}
                    placeholder="Repeat password"
                    className={cn(inputStyle, "pr-10")}
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    className="absolute right-3 top-[33px] text-slate-400 transition-colors hover:text-slate-700"
                    aria-label={showConfirmPassword ? "Hide confirm password" : "Show confirm password"}
                  >
                    {showConfirmPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                  <FieldError message={errors.user_Password_confirmation?.message as string | undefined} />
                </div>
              </div>
            </section>
          ) : null}

          <div className="border-t border-slate-200 pt-8">
            <Button type="submit" disabled={isBusy} className={submitButtonTone}>
              {isBusy ? (
                getSubmitButtonLabel("Create Account")
              ) : submitState === "success" ? (
                <>
                  <CheckCircle2 className="mr-2 h-4 w-4" />
                  {getSubmitButtonLabel("Create Account")}
                </>
              ) : submitState === "error" ? (
                <>
                  <AlertCircle className="mr-2 h-4 w-4" />
                  {getSubmitButtonLabel("Create Account")}
                </>
              ) : (
                getSubmitButtonLabel("Create Account")
              )}
            </Button>
          </div>
        </form>
      </div>
    </div>
  )
}
