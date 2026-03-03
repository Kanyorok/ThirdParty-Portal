"use client"

import * as React from "react"
import { AlertCircle, ArrowRight, CheckCircle2, Eye, EyeOff, ShieldCheck, UserPlus } from "lucide-react"
import { motion, AnimatePresence } from "framer-motion"
import { Spinner } from "@/components/common/spinner"
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
import { type RegisterFormInputs, type RegisterThirdPartyResult, useRegisterForm } from "@/hooks/use-register"

export default function RegisterForm() {
  const router = useRouter()
  const [step, setStep] = React.useState<1 | 2>(1)
  const [authError, setAuthError] = React.useState<string | null>(null)
  const [success, setSuccess] = React.useState(false)
  const [successTitle, setSuccessTitle] = React.useState("Registration Complete")
  const [successDescription, setSuccessDescription] = React.useState<string>(
    "Your account has been created successfully."
  )
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

  const isPosting = submitState === "posting"
  const isBusy = isSubmitting || isPosting

  const adminFields = [
    "user_FirstName",
    "user_LastName",
    "user_Email",
    "user_Phone",
    "user_Gender",
    "user_Password",
    "user_Password_confirmation"
  ] as const

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

      if (verificationRequired) {
        const notice = verificationEmail
          ? `Account created. We sent a verification email to ${verificationEmail}. Confirm your email to continue.`
          : "Account created. We sent a verification email. Confirm your email to continue."
        setSubmitNotice(notice)
        setSuccessTitle("Check your email")
        setSuccessDescription(
          verificationEmail
            ? `A verification link was sent to ${verificationEmail}. Click the link in the email to activate your account.`
            : "A verification link was sent to your email address. Click the link to activate your account."
        )
      } else {
        setSubmitNotice(backendMessage || "Registration completed successfully.")
        setSuccessTitle("Registration complete")
        setSuccessDescription("Your account has been created successfully.")
      }

      setSuccess(true)
    },
    [extractRegisterMessage, pickVerificationEmail]
  )

  const handleNextStep = async (e: React.FormEvent) => {
    e.preventDefault()
    setAuthError(null)
    setSubmitNotice(null)
    setSubmitState("idle")

    const fieldsToValidate = [
      "Name",
      "BusinessType",
      "RegistrationNumber",
      "Country",
      "Location",
      "TaxPIN",
      "Email",
      "Phone",
      "types"
    ] as any

    if (isSupplier) fieldsToValidate.push("supplier_category_id")
    if (isTenant) fieldsToValidate.push("user_Remarks")

    const valid = await form.trigger(fieldsToValidate)

    if (!valid) {
      const errorFields = form.formState.errors
      const firstError = Object.keys(errorFields)[0]

      setAuthError(
        errorFields[firstError as keyof typeof errorFields]?.message ||
        "Please fix the validation errors before continuing"
      )

      form.setFocus(firstError as any)
      return
    }

    if (!createUser) {
      setSubmitState("posting")
      setSubmitNotice("Submitting your registration...")
      try {
        const values = form.getValues()
        const registerResponse = await registerThirdParty(values)
        if (registerResponse?.success) handleRegistrationSuccess(values, registerResponse)
      } catch (error: any) {
        setSubmitState("error")
        setSubmitNotice("Registration failed. Please review the error and try again.")
        setAuthError(error?.message ?? "An unexpected error occurred.")
      }
      return
    }

    setStep(2)
    window.scrollTo({ top: 0, behavior: "smooth" })
  }

  const handleFinalSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setAuthError(null)
    setSubmitNotice(null)
    if (isBusy) {
      return
    }
    setSubmitState("idle")

    const valid = await form.trigger(adminFields as any)

    if (!valid) {
      const errorFields = form.formState.errors
      const firstError = Object.keys(errorFields)[0]
      setAuthError(
        errorFields[firstError as keyof typeof errorFields]?.message ||
        "Please fix the validation errors before continuing"
      )
      form.setFocus(firstError as any)
      return
    }

    try {
      setSubmitState("posting")
      setSubmitNotice("Creating account and submitting registration...")
      const values = form.getValues()
      const registerResponse = await registerThirdParty(values)
      if (registerResponse?.success) handleRegistrationSuccess(values, registerResponse)
    } catch (error: any) {
      setSubmitState("error")
      setSubmitNotice("Registration failed. Please review the error and try again.")
      setAuthError(error?.message ?? "An unexpected error occurred.")
    }
  }

  const getSubmitButtonLabel = (defaultLabel: string) => {
    if (submitState === "posting") return "Submitting..."
    if (submitState === "success") return "Submitted"
    if (submitState === "error") return "Try Again"
    return defaultLabel
  }

  const submitButtonTone = cn(
    "h-12 rounded-xl px-5 text-sm font-semibold tracking-tight disabled:cursor-not-allowed disabled:opacity-90",
    submitState === "success" && "bg-emerald-600 hover:bg-emerald-600",
    submitState === "error" && "bg-rose-600 hover:bg-rose-700",
    (submitState === "idle" || submitState === "posting") && "bg-primary text-primary-foreground hover:bg-[var(--primary-hover)]"
  )

  const inputStyle = "h-12 bg-background/95"
  const selectStyle = "h-12 w-full bg-background/95"
  const selectContentStyle = ""
  const selectItemStyle = ""
  const labelStyle = "mb-1.5 block text-[11px] font-semibold tracking-wide text-slate-600"
  const errorStyle = "mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600"
  const sectionStyle = "space-y-4"
  const sectionTitleStyle = "text-base font-semibold tracking-tight text-slate-900"
  const panelStyle = "rounded-2xl border border-slate-200/90 bg-white/90 p-5 sm:p-6"
  const secondaryButtonStyle = "h-12 rounded-xl px-6 text-sm font-semibold"
  const businessTypeValue = form.watch("BusinessType")
  const supplierCategoryValue = form.watch("supplier_category_id")
  const countryValue = form.watch("Country")
  const locationValue = form.watch("Location")
  const genderValue = form.watch("user_Gender")

  if (isLoadingMetadata) return (
    <div className="py-20 text-center">
      <Spinner className="h-8 w-8 animate-spin text-blue-600 mx-auto" />
    </div>
  )

  if (metadataError) return (
    <div className="py-20 text-center text-red-600 font-bold uppercase tracking-tighter">
      {metadataError}
    </div>
  )

  if (success) return (
    <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} className="mx-auto max-w-md py-12 text-center">
      <CheckCircle2 className="mx-auto mb-5 h-14 w-14 text-emerald-600" />
      <h2 className="mb-2 text-[28px] font-semibold tracking-tight text-slate-900">{successTitle}</h2>
      <p className="mb-6 text-sm leading-relaxed text-slate-600">{successDescription}</p>
      <Button onClick={() => router.replace("/signin")} className="h-12 w-full rounded-xl bg-[#0e63f4] px-5 text-sm font-semibold tracking-tight text-white hover:bg-[#0a54d1]">
        Sign In
      </Button>
    </motion.div>
  )

  return (
    <div className="mx-auto max-w-7xl px-1 pb-4">
      <div className="mb-8 flex flex-wrap items-center justify-between gap-3">
        <div className="inline-flex h-8 items-center rounded-full border border-[#0e63f4]/20 bg-[#0e63f4]/5 px-3 text-xs font-semibold tracking-wide text-[#0c408f]">
          Step {step}/2
        </div>
        <div className="flex items-center gap-2 text-[13px] font-semibold tracking-tight">
          <span className={cn("rounded-full border px-3 py-1.5 transition-colors", step === 1 ? "border-[#0e63f4]/35 bg-[#0e63f4]/10 text-[#0c408f]" : "border-slate-200 text-slate-400")}>
            Organization
          </span>
          <span className={cn("rounded-full border px-3 py-1.5 transition-colors", step === 2 ? "border-[#0e63f4]/35 bg-[#0e63f4]/10 text-[#0c408f]" : "border-slate-200 text-slate-400")}>
            Admin User
          </span>
        </div>
      </div>
      <div className="mb-8 h-1.5 w-full rounded-full bg-slate-200/70">
        <div className={cn("h-1.5 rounded-full bg-[#0e63f4] transition-all duration-300", step === 1 ? "w-1/2" : "w-full")} />
      </div>

      <AnimatePresence mode="wait">
        {authError && (
          <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }} className="mb-6 flex items-center gap-2 border-l-2 border-rose-500 pl-3 text-rose-700">
            <AlertCircle className="h-4 w-4 shrink-0" />
            <span className="text-sm font-semibold">{authError}</span>
          </motion.div>
        )}
      </AnimatePresence>
      {submitNotice && submitState !== "error" && (
        <div className="mb-6 flex items-center gap-2 border-l-2 border-emerald-500 pl-3 text-emerald-700">
          <CheckCircle2 className="h-4 w-4 shrink-0" />
          <span className="text-sm font-semibold">{submitNotice}</span>
        </div>
      )}

      <form onSubmit={handleFinalSubmit} onKeyDown={e => e.key === "Enter" && e.preventDefault()} className="space-y-7">
        {step === 1 ? (
          <motion.div initial={{ opacity: 0, x: -16 }} animate={{ opacity: 1, x: 0 }} className="space-y-7">
            <section className={cn(sectionStyle, panelStyle)}>
              <div>
                <p className={sectionTitleStyle}>Business roles</p>
              </div>
              <div className="grid grid-cols-1 gap-2 sm:grid-cols-3">
                {[{ id: "SU", label: "Supplier" }, { id: "TN", label: "Tenant" }, { id: "CU", label: "Customer" }].map(type => (
                  <button
                    key={type.id}
                    type="button"
                    onClick={() => toggleType(type.id)}
                    className={cn(
                      "h-11 rounded-xl border px-4 text-sm font-medium transition-colors",
                      selectedTypes?.includes(type.id)
                        ? "border-[#0e63f4]/35 bg-[#0e63f4]/10 text-[#0c408f]"
                        : "border-slate-300 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-800"
                    )}
                  >
                    {type.label}
                  </button>
                ))}
              </div>
              {errors.types && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.types.message}</p>}
            </section>

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
              <section className={cn(sectionStyle, panelStyle)}>
                <div>
                  <p className={sectionTitleStyle}>Company</p>
                </div>
                <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                  <div className="md:col-span-2">
                    <label className={labelStyle}>Legal Company Name</label>
                    <Input {...form.register("Name")} placeholder="Daniel Logistics Limited" className={inputStyle} />
                    {errors.Name && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Name.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Trading Name</label>
                    <Input {...form.register("TradingName")} placeholder="Daniel Logistics" className={inputStyle} />
                    {errors.TradingName && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.TradingName.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Registration Number</label>
                    <Input {...form.register("RegistrationNumber")} placeholder="C123456" className={inputStyle} />
                    {errors.RegistrationNumber && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.RegistrationNumber.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Business Type</label>
                    <Select
                      value={businessTypeValue || undefined}
                      onValueChange={(value) =>
                        form.setValue("BusinessType", value, { shouldDirty: true, shouldValidate: true })
                      }
                    >
                      <SelectTrigger className={selectStyle}>
                        <SelectValue placeholder="Select business type" />
                      </SelectTrigger>
                      <SelectContent className={selectContentStyle}>
                        {metadata.businessTypes.map((bt) => (
                          <SelectItem key={bt.value} value={bt.value} className={selectItemStyle}>
                            {bt.description}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.BusinessType && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.BusinessType.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Tax PIN</label>
                    <Input {...form.register("TaxPIN")} placeholder="P051234567X" className={inputStyle} />
                    {errors.TaxPIN && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.TaxPIN.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>VAT Number</label>
                    <Input {...form.register("VATNumber")} placeholder="Optional" className={inputStyle} />
                    {errors.VATNumber && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.VATNumber.message}</p>}
                  </div>

                  {isSupplier && (
                    <div className="md:col-span-2">
                      <label className={labelStyle}>Supplier Category</label>
                      <Select
                        value={supplierCategoryValue != null ? String(supplierCategoryValue) : undefined}
                        onValueChange={(value) =>
                          form.setValue("supplier_category_id", Number(value), { shouldDirty: true, shouldValidate: true })
                        }
                      >
                        <SelectTrigger className={selectStyle}>
                          <SelectValue placeholder="Select supplier category" />
                        </SelectTrigger>
                        <SelectContent className={selectContentStyle}>
                          {metadata.supplierCategories.map((cat) => (
                            <SelectItem key={cat.id} value={String(cat.id)} className={selectItemStyle}>
                              {cat.name}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                      {errors.supplier_category_id && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.supplier_category_id.message}</p>}
                    </div>
                  )}
                </div>
              </section>

              <section className={cn(sectionStyle, panelStyle)}>
                <div>
                  <p className={sectionTitleStyle}>Contact</p>
                </div>
                <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                  <div>
                    <label className={labelStyle}>Business Email</label>
                    <Input type="email" {...form.register("Email")} placeholder="procurement@company.com" className={inputStyle} />
                    {errors.Email && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Email.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Phone Number</label>
                    <Input {...form.register("Phone")} placeholder="+254 700 000 000" className={inputStyle} />
                    {errors.Phone && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Phone.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Country</label>
                    <Select
                      value={countryValue || undefined}
                      onValueChange={(value) =>
                        form.setValue("Country", value, { shouldDirty: true, shouldValidate: true })
                      }
                    >
                      <SelectTrigger className={selectStyle}>
                        <SelectValue placeholder="Select country" />
                      </SelectTrigger>
                      <SelectContent className={selectContentStyle}>
                        {metadata.countries.map((c) => (
                          <SelectItem key={c.code} value={c.code} className={selectItemStyle}>
                            {c.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.Country && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Country.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Location</label>
                    <Select
                      value={locationValue != null ? String(locationValue) : undefined}
                      onValueChange={(value) =>
                        form.setValue("Location", Number(value), { shouldDirty: true, shouldValidate: true })
                      }
                    >
                      <SelectTrigger className={selectStyle}>
                        <SelectValue placeholder="Select locality" />
                      </SelectTrigger>
                      <SelectContent className={selectContentStyle}>
                        {metadata.localities.map((l) => (
                          <SelectItem key={l.id} value={String(l.id)} className={selectItemStyle}>
                            {l.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.Location && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Location.message}</p>}
                  </div>

                  <div className="md:col-span-2">
                    <label className={labelStyle}>Physical Address</label>
                    <Input {...form.register("PhysicalAddress")} placeholder="123 King Chain Road, Nairobi" className={inputStyle} />
                    {errors.PhysicalAddress && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.PhysicalAddress.message}</p>}
                  </div>

                  <div className="md:col-span-2">
                    <label className={labelStyle}>Website</label>
                    <Input {...form.register("Website")} placeholder="https://daniellogistics.com" className={inputStyle} />
                    {errors.Website && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Website.message}</p>}
                  </div>

                  {isTenant && (
                    <div className="md:col-span-2">
                      <label className={labelStyle}>Tenant Remarks</label>
                      <Input {...form.register("user_Remarks")} placeholder="Setup notes" className={inputStyle} />
                      {errors.user_Remarks && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Remarks.message}</p>}
                    </div>
                  )}
                </div>
              </section>
            </div>

            <section className={cn("flex items-center justify-between", panelStyle)}>
              <div className="flex items-center gap-3">
                <div className="flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[#0e63f4]"><UserPlus size={15} /></div>
                <div>
                  <h4 className="text-sm font-semibold text-slate-800">Create admin login</h4>
                </div>
              </div>
              <button type="button" onClick={() => form.setValue("createUser", !createUser)} className={cn("relative h-7 w-12 rounded-full transition-colors", createUser ? "bg-[#0e63f4]" : "bg-slate-300")}>
                <div className={cn("absolute top-1 h-5 w-5 rounded-full bg-white transition-all", createUser ? "right-1" : "left-1")} />
              </button>
            </section>

            <Button
              type="button"
              onClick={handleNextStep}
              disabled={isBusy}
              className={createUser ? "h-12 w-full rounded-xl bg-[#0e63f4] px-5 text-sm font-semibold tracking-tight text-white transition-colors hover:bg-[#0a54d1] focus-visible:ring-2 focus-visible:ring-[#0e63f4]/20 disabled:cursor-not-allowed disabled:opacity-90" : cn("w-full", submitButtonTone)}
            >
              {isBusy && !createUser ? (
                <>
                  <Spinner className="mr-2" />
                  {getSubmitButtonLabel("Complete Registration")}
                </>
              ) : createUser ? (
                <span className="inline-flex items-center gap-2">
                  Continue to admin setup
                  <ArrowRight className="h-4 w-4" />
                </span>
              ) : submitState === "success" ? (
                <>
                  <CheckCircle2 className="mr-2 h-4 w-4" />
                  {getSubmitButtonLabel("Complete Registration")}
                </>
              ) : submitState === "error" ? (
                <>
                  <AlertCircle className="mr-2 h-4 w-4" />
                  {getSubmitButtonLabel("Complete Registration")}
                </>
                ) : (
                  getSubmitButtonLabel("Create organization")
                )}
            </Button>
          </motion.div>
        ) : (
          <motion.div initial={{ opacity: 0, x: 16 }} animate={{ opacity: 1, x: 0 }} className="space-y-7">
            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
              <section className={cn(sectionStyle, panelStyle)}>
                <div>
                  <p className={sectionTitleStyle}>Admin profile</p>
                </div>
                <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                  <div>
                    <label className={labelStyle}>First Name</label>
                    <Input {...form.register("user_FirstName")} placeholder="Daniel" className={inputStyle} />
                    {errors.user_FirstName && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_FirstName.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Last Name</label>
                    <Input {...form.register("user_LastName")} placeholder="Kitonga" className={inputStyle} />
                    {errors.user_LastName && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_LastName.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Admin Email</label>
                    <Input {...form.register("user_Email")} placeholder="admin@company.com" className={inputStyle} />
                    {errors.user_Email && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Email.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Admin Phone</label>
                    <Input {...form.register("user_Phone")} placeholder="+254 700 000 000" className={inputStyle} />
                    {errors.user_Phone && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Phone.message}</p>}
                  </div>

                  <div>
                    <label className={labelStyle}>Gender</label>
                    <Select
                      value={genderValue || undefined}
                      onValueChange={(value) =>
                        form.setValue("user_Gender", value, { shouldDirty: true, shouldValidate: true })
                      }
                    >
                      <SelectTrigger className={selectStyle}>
                        <SelectValue placeholder="Select gender" />
                      </SelectTrigger>
                      <SelectContent className={selectContentStyle}>
                        {metadata.genders.map((g) => (
                          <SelectItem key={g.value} value={g.value} className={selectItemStyle}>
                            {g.description}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {errors.user_Gender && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Gender.message}</p>}
                  </div>
                </div>
              </section>

              <section className={cn(sectionStyle, panelStyle)}>
                <div className="flex items-center justify-between gap-3">
                  <p className={sectionTitleStyle}>Security</p>
                  <div className="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                    <ShieldCheck className="h-3.5 w-3.5" />
                    Protected
                  </div>
                </div>
                <div className="grid grid-cols-1 gap-5">
                  <div className="relative">
                    <label className={labelStyle}>Password</label>
                    <Input type={showPassword ? "text" : "password"} {...form.register("user_Password")} placeholder="At least 8 characters" className={cn(inputStyle, "pr-10")} />
                    <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3 top-[33px] text-slate-400">
                      {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                    </button>
                    {errors.user_Password && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Password.message}</p>}
                  </div>

                  <div className="relative">
                    <label className={labelStyle}>Confirm Password</label>
                    <Input type={showConfirmPassword ? "text" : "password"} {...form.register("user_Password_confirmation")} placeholder="Repeat password" className={cn(inputStyle, "pr-10")} />
                    <button type="button" onClick={() => setShowConfirmPassword(!showConfirmPassword)} className="absolute right-3 top-[33px] text-slate-400">
                      {showConfirmPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                    </button>
                    {errors.user_Password_confirmation && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Password_confirmation.message}</p>}
                  </div>
                </div>
              </section>
            </div>

            <div className="flex flex-wrap gap-3">
              <Button type="button" onClick={() => { setStep(1); setSubmitState("idle"); setSubmitNotice(null) }} className={secondaryButtonStyle}>
                Back
              </Button>
              <Button type="submit" disabled={isBusy} className={cn("flex-1", submitButtonTone)}>
                {isBusy ? (
                  <>
                    <Spinner className="mr-2" />
                    {getSubmitButtonLabel("Register")}
                  </>
                ) : submitState === "success" ? (
                  <>
                    <CheckCircle2 className="mr-2 h-4 w-4" />
                    {getSubmitButtonLabel("Register")}
                  </>
                ) : submitState === "error" ? (
                  <>
                    <AlertCircle className="mr-2 h-4 w-4" />
                    {getSubmitButtonLabel("Register")}
                  </>
                ) : (
                  getSubmitButtonLabel("Create account")
                )}
              </Button>
            </div>
          </motion.div>
        )}
      </form>
    </div>
  )
}
