"use client"

import * as React from "react"
import { AlertCircle, CheckCircle2, Eye, EyeOff, UserPlus } from "lucide-react"
import { motion, AnimatePresence } from "framer-motion"
import { Spinner } from "@/components/common/spinner"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { useRouter } from "next/navigation"
import { cn } from "@/lib/utils"
import { useRegisterForm } from "@/hooks/use-register"

export default function RegisterForm() {
  const router = useRouter()
  const [step, setStep] = React.useState<1 | 2>(1)
  const [authError, setAuthError] = React.useState<string | null>(null)
  const [success, setSuccess] = React.useState(false)
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
    verifyEmailUrl,
    resetVerifyEmailUrl,
    getLastVerifyEmailUrl,
    getLastRegisterResponse,
    registerThirdParty
  } = useRegisterForm()

  const createUser = form.watch("createUser")

  const isPosting = submitState === "posting"
  const isBusy = isSubmitting || isPosting

  React.useEffect(() => {
    console.log("[v0] Form state - Step:", step, "CreateUser:", createUser, "IsSubmitting:", isBusy)
  }, [step, createUser, isBusy])

  const adminFields = [
    "user_FirstName",
    "user_LastName",
    "user_Email",
    "user_Phone",
    "user_Gender",
    "user_Password",
    "user_Password_confirmation"
  ] as const

  React.useEffect(() => {
    if (!verifyEmailUrl) return
    setSubmitState("success")
    setSubmitNotice("Registration submitted. Redirecting to email verification...")
    const timeoutId = window.setTimeout(() => {
      router.replace(verifyEmailUrl)
      resetVerifyEmailUrl()
    }, 900)

    return () => window.clearTimeout(timeoutId)
  }, [verifyEmailUrl, router, resetVerifyEmailUrl])

  const handleNextStep = async (e: React.FormEvent) => {
    e.preventDefault()
    console.log("[v0] handleNextStep triggered, createUser:", createUser)
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
    console.log("[v0] Step 1 validation result:", valid, "Errors:", form.formState.errors)

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
      console.log("[v0] Submitting without user creation")
      setSubmitState("posting")
      setSubmitNotice("Submitting your registration...")
      try {
        const values = form.getValues()
        console.log("[v0] Form values being submitted:", values)
        await registerThirdParty(values)
        const registerResponse = getLastRegisterResponse()
        console.log("[v0] Register response:", registerResponse)
        if (registerResponse?.success && !getLastVerifyEmailUrl()) {
          setSubmitState("success")
          setSubmitNotice("Registration completed successfully.")
          setSuccess(true)
        }
      } catch (error: any) {
        console.log("[v0] Registration error:", error?.message)
        setSubmitState("error")
        setSubmitNotice("Registration failed. Please review the error and try again.")
        setAuthError(error?.message ?? "An unexpected error occurred.")
      }
      return
    }

    console.log("[v0] Moving to step 2")
    setStep(2)
    window.scrollTo({ top: 0, behavior: "smooth" })
  }

  const handleFinalSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    console.log("[v0] handleFinalSubmit triggered")
    setAuthError(null)
    setSubmitNotice(null)
    if (isBusy) {
      console.log("[v0] Already submitting, returning")
      return
    }
    setSubmitState("idle")

    const valid = await form.trigger(adminFields as any)
    console.log("[v0] Step 2 validation result:", valid, "Errors:", form.formState.errors)

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
      console.log("[v0] Submitting with user creation")
      setSubmitState("posting")
      setSubmitNotice("Creating account and submitting registration...")
      const values = form.getValues()
      console.log("[v0] Form values being submitted:", values)
      await registerThirdParty(values)
      const registerResponse = getLastRegisterResponse()
      console.log("[v0] Register response:", registerResponse)
      if (registerResponse?.success && !getLastVerifyEmailUrl()) {
        setSubmitState("success")
        setSubmitNotice("Registration completed successfully.")
        setSuccess(true)
      }
    } catch (error: any) {
      console.log("[v0] Registration error:", error?.message)
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
    "h-14 text-white font-bold uppercase rounded-lg w-full tracking-widest transition-colors disabled:opacity-100 disabled:cursor-not-allowed",
    submitState === "success" && "bg-emerald-600 hover:bg-emerald-600",
    submitState === "error" && "bg-rose-600 hover:bg-rose-700",
    (submitState === "idle" || submitState === "posting") && "bg-blue-600 hover:bg-blue-700"
  )

  const inputStyle = "h-12 w-full border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all rounded-lg"
  const labelStyle = "text-[10px] font-bold uppercase tracking-widest text-slate-600 mb-2 block"
  const errorStyle = "text-xs text-red-600 mt-1.5 flex items-center gap-1"

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
    <motion.div initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} className="max-w-md mx-auto text-center py-20 px-8">
      <CheckCircle2 className="h-20 w-20 text-green-600 mx-auto mb-6" />
      <h2 className="text-2xl font-bold mb-4">Registration Complete</h2>
      <Button onClick={() => router.replace("/signin")} className="w-full h-12 bg-blue-600 text-white font-bold rounded-lg uppercase tracking-widest">
        Sign In
      </Button>
    </motion.div>
  )

  return (
    <div className="max-w-5xl mx-auto px-6 pb-20">
      <div className="flex items-center border-b border-slate-200 mb-12">
        <div className={cn("flex items-center gap-3 pb-4 pr-12 border-b-2 transition-all", step === 1 ? "border-blue-600" : "border-transparent opacity-40")}>
          <span className="text-[10px] font-black uppercase tracking-[0.3em]">Organization</span>
        </div>
        <div className={cn("flex items-center gap-3 pb-4 pr-12 border-b-2 transition-all", step === 2 ? "border-blue-600" : "border-transparent opacity-40")}>
          <span className="text-[10px] font-black uppercase tracking-[0.3em]">Admin User</span>
        </div>
      </div>

      <AnimatePresence mode="wait">
        {authError && (
          <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }} className="mb-8 p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg flex items-center gap-3">
            <AlertCircle className="h-5 w-5" />
            <span className="text-sm font-medium">{authError}</span>
          </motion.div>
        )}
      </AnimatePresence>
      {submitNotice && submitState !== "error" && (
        <div className="mb-8 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg flex items-center gap-3">
          <CheckCircle2 className="h-5 w-5" />
          <span className="text-sm font-medium">{submitNotice}</span>
        </div>
      )}

      <form onSubmit={handleFinalSubmit} onKeyDown={e => e.key === "Enter" && e.preventDefault()} className="grid grid-cols-1 lg:grid-cols-12 gap-16">
        <div className="lg:col-span-8">
          {step === 1 ? (
            <motion.div initial={{ opacity: 0, x: -16 }} animate={{ opacity: 1, x: 0 }} className="space-y-10">
              <section className="space-y-6">
                <label className={labelStyle}>Select Business Roles</label>
                <div className="flex flex-wrap gap-3">
                  {[{ id: "SU", label: "Supplier" }, { id: "TN", label: "Tenant" }, { id: "CU", label: "Customer" }].map(type => (
                    <button key={type.id} type="button" onClick={() => toggleType(type.id)} className={cn("px-6 h-12 text-[11px] font-bold uppercase border-2 rounded-lg transition-all", selectedTypes?.includes(type.id) ? "border-blue-600 bg-blue-600 text-white" : "border-slate-200 text-slate-600")}>
                      {type.label}
                    </button>
                  ))}
                </div>
                {errors.types && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.types.message}</p>}
              </section>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div className="md:col-span-2">
                  <label className={labelStyle}>Legal Company Name</label>
                  <Input {...form.register("Name")} className={inputStyle} />
                  {errors.Name && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Name.message}</p>}
                </div>

                <div className="md:col-span-2">
                  <label className={labelStyle}>Trading Name</label>
                  <Input {...form.register("TradingName")} className={inputStyle} />
                  {errors.TradingName && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.TradingName.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>Business Type</label>
                  <select {...form.register("BusinessType")} className={inputStyle}>
                    <option value="">Select...</option>
                    {metadata.businessTypes.map(bt => <option key={bt.value} value={bt.value}>{bt.description}</option>)}
                  </select>
                  {errors.BusinessType && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.BusinessType.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>Registration Number</label>
                  <Input {...form.register("RegistrationNumber")} className={inputStyle} />
                  {errors.RegistrationNumber && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.RegistrationNumber.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>Tax PIN</label>
                  <Input {...form.register("TaxPIN")} className={inputStyle} />
                  {errors.TaxPIN && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.TaxPIN.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>VAT Number</label>
                  <Input {...form.register("VATNumber")} className={inputStyle} />
                  {errors.VATNumber && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.VATNumber.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>Email</label>
                  <Input type="email" {...form.register("Email")} className={inputStyle} />
                  {errors.Email && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Email.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>Phone</label>
                  <Input {...form.register("Phone")} className={inputStyle} />
                  {errors.Phone && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Phone.message}</p>}
                </div>

                {isSupplier && (
                  <div className="md:col-span-2">
                    <label className={labelStyle}>Supplier Category</label>
                    <select {...form.register("supplier_category_id", { valueAsNumber: true })} className={inputStyle}>
                      <option value="">Select...</option>
                      {metadata.supplierCategories.map(cat => (
                        <option key={cat.id} value={cat.id}>{cat.name}</option>
                      ))}
                    </select>
                    {errors.supplier_category_id && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.supplier_category_id.message}</p>}
                  </div>
                )}

                <div>
                  <label className={labelStyle}>Country</label>
                  <select {...form.register("Country")} className={inputStyle}>
                    <option value="">Select...</option>
                    {metadata.countries.map(c => <option key={c.code} value={c.code}>{c.name}</option>)}
                  </select>
                  {errors.Country && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Country.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>Location</label>
                  <select {...form.register("Location", { valueAsNumber: true })} className={inputStyle}>
                    <option value="">Select...</option>
                    {metadata.localities.map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
                  </select>
                  {errors.Location && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.Location.message}</p>}
                </div>

                <div className="md:col-span-2">
                  <label className={labelStyle}>Physical Address</label>
                  <Input {...form.register("PhysicalAddress")} className={inputStyle} />
                  {errors.PhysicalAddress && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.PhysicalAddress.message}</p>}
                </div>

                {isTenant && (
                  <div className="md:col-span-2">
                    <label className={labelStyle}>Remarks</label>
                    <Input {...form.register("user_Remarks")} className={inputStyle} />
                    {errors.user_Remarks && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Remarks.message}</p>}
                  </div>
                )}
              </div>

              <div className="p-6 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <div className="flex items-center gap-4">
                  <div className="h-10 w-10 bg-white border border-slate-200 rounded-full flex items-center justify-center text-blue-600 shadow-sm"><UserPlus size={20} /></div>
                  <div>
                    <h4 className="text-sm font-bold text-slate-800">Admin Account</h4>
                    <p className="text-xs text-slate-500">Enable portal access</p>
                  </div>
                </div>
                <button type="button" onClick={() => form.setValue("createUser", !createUser)} className={cn("w-12 h-6 rounded-full relative transition-colors", createUser ? "bg-blue-600" : "bg-slate-300")}>
                  <div className={cn("absolute top-1 w-4 h-4 bg-white rounded-full transition-all shadow-sm", createUser ? "right-1" : "left-1")} />
                </button>
              </div>

              <Button type="button" onClick={handleNextStep} disabled={isBusy} className={createUser ? "h-14 bg-blue-600 text-white font-bold uppercase rounded-lg w-full tracking-widest hover:bg-blue-700 transition-colors disabled:opacity-100 disabled:cursor-not-allowed" : submitButtonTone}>
                {isBusy && !createUser ? (
                  <>
                    <Spinner className="mr-2" />
                    {getSubmitButtonLabel("Complete Registration")}
                  </>
                ) : createUser ? (
                  "Continue to User Details"
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
                  getSubmitButtonLabel("Complete Registration")
                )}
              </Button>
            </motion.div>
          ) : (
            <motion.div initial={{ opacity: 0, x: 16 }} animate={{ opacity: 1, x: 0 }} className="space-y-10">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div>
                  <Input {...form.register("user_FirstName")} placeholder="First Name" className={inputStyle} />
                  {errors.user_FirstName && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_FirstName.message}</p>}
                </div>

                <div>
                  <Input {...form.register("user_LastName")} placeholder="Last Name" className={inputStyle} />
                  {errors.user_LastName && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_LastName.message}</p>}
                </div>

                <div>
                  <Input {...form.register("user_Email")} placeholder="Admin Email" className={inputStyle} />
                  {errors.user_Email && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Email.message}</p>}
                </div>

                <div>
                  <Input {...form.register("user_Phone")} placeholder="Admin Phone" className={inputStyle} />
                  {errors.user_Phone && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Phone.message}</p>}
                </div>

                <div>
                  <select {...form.register("user_Gender")} className={inputStyle}>
                    <option value="">Gender...</option>
                    {metadata.genders.map(g => <option key={g.value} value={g.value}>{g.description}</option>)}
                  </select>
                  {errors.user_Gender && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Gender.message}</p>}
                </div>

                <div className="relative">
                  <Input type={showPassword ? "text" : "password"} {...form.register("user_Password")} placeholder="Password" className={inputStyle} />
                  <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                    {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                  {errors.user_Password && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Password.message}</p>}
                </div>

                <div className="relative md:col-span-2">
                  <Input type={showConfirmPassword ? "text" : "password"} {...form.register("user_Password_confirmation")} placeholder="Confirm Password" className={inputStyle} />
                  <button type="button" onClick={() => setShowConfirmPassword(!showConfirmPassword)} className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                    {showConfirmPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                  {errors.user_Password_confirmation && <p className={errorStyle}><AlertCircle className="h-3 w-3" />{errors.user_Password_confirmation.message}</p>}
                </div>
              </div>

              <div className="flex gap-4">
                <Button type="button" onClick={() => { setStep(1); setSubmitState("idle"); setSubmitNotice(null) }} className="h-14 bg-slate-100 text-slate-600 font-bold rounded-lg px-8 uppercase tracking-widest hover:bg-slate-200 transition-colors">Back</Button>
                <Button type="submit" disabled={isBusy} className={`h-14 flex-1 uppercase tracking-widest shadow-lg shadow-blue-100 ${submitButtonTone}`}>
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
                    getSubmitButtonLabel("Register")
                  )}
                </Button>
              </div>
            </motion.div>
          )}
        </div>
      </form>
    </div>
  )
}
