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
  const [showPassword, setShowPassword] = React.useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = React.useState(false)

  const {
    form,
    errors,
    metadata,
    isLoadingMetadata,
    metadataError,
    onSubmit,
    isSubmitting,
    toggleType,
    selectedTypes,
    isSupplier,
    isTenant,
    isCustomer,
    verifyEmailUrl,
    resetVerifyEmailUrl,
    getLastVerifyEmailUrl
  } = useRegisterForm()

  const createUser = form.watch("createUser")

  React.useEffect(() => {
    if (!verifyEmailUrl) return
    router.replace(verifyEmailUrl)
    resetVerifyEmailUrl()
  }, [verifyEmailUrl, router, resetVerifyEmailUrl])

  const submitDirectly = async () => {
    setAuthError(null)
    try {
      await onSubmit()
      if (!getLastVerifyEmailUrl()) {
        setSuccess(true)
      }
    } catch (error: any) {
      setAuthError(error?.message ?? "An unexpected error occurred.")
    }
  }

  const nextStep = async () => {
    const baseFields = [
      "Name",
      "TradingName",
      "BusinessType",
      "RegistrationNumber",
      "Country",
      "Location",
      "TaxPIN",
      "VATNumber",
      "Email",
      "Phone",
      "Website",
      "PhysicalAddress",
      "types"
    ] as const

    const fields = [
      ...baseFields,
      ...(isTenant ? ["user_Remarks"] : []),
      ...(isSupplier ? ["user_SupplierCategoryId"] : [])
    ]

    const valid = await form.trigger(fields as any)
    if (!valid) return

    if (!createUser) {
      await submitDirectly()
      return
    }

    setStep(2)
    window.scrollTo({ top: 0 })
  }

  const handleFinalSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (isSubmitting) return
    await submitDirectly()
  }

  const inputStyle =
    "h-12 w-full border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all disabled:bg-slate-50"
  const labelStyle =
    "text-[10px] font-bold uppercase tracking-widest text-slate-600 mb-2 block"
  const errorStyle =
    "text-xs text-red-600 mt-1.5 flex items-center gap-1"

  const roleLabelMap: Record<string, string> = {
    SU: "Supplier",
    TN: "Tenant",
    CU: "Customer"
  }

  const benefitHighlights = [
    {
      title: "Compliance ready",
      detail: "Structured fields keep documentation aligned with regulatory expectations."
    },
    {
      title: "Role-aware onboarding",
      detail: "Supplier, tenant, and customer flows adjust without extra clicks."
    },
    {
      title: "Secure admin access",
      detail: "Create the first admin with password visibility controls and email verification."
    }
  ]

  const stepLabels = [
    {
      title: "Organization",
      detail: "Tell us about your company and how you serve your customers."
    },
    {
      title: "Admin user",
      detail: "Create the first portal admin and invite colleagues later."
    }
  ]

  const selectedRoleNames = selectedTypes?.map(type => roleLabelMap[type] ?? type) ?? []

  if (isLoadingMetadata)
    return (
      <div className="py-20 text-center">
        <Spinner className="h-8 w-8 animate-spin text-blue-600 mx-auto" />
      </div>
    )

  if (metadataError)
    return (
      <div className="py-20 text-center text-red-600">
        {metadataError}
      </div>
    )

  if (success)
    return (
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        className="max-w-md mx-auto text-center py-20 px-8"
      >
        <CheckCircle2 className="h-20 w-20 text-green-600 mx-auto mb-6" />
        <h2 className="text-2xl font-bold mb-4">Registration Complete</h2>
        <Button
          onClick={() => router.replace("/signin")}
          className="w-full h-12 bg-blue-600 text-white font-bold rounded-lg"
        >
          GO TO SIGN IN
        </Button>
      </motion.div>
    )

  return (
    <div className="max-w-5xl mx-auto px-6 pb-20">
      <div className="flex items-center border-b border-slate-200 mb-12">
        <div
          className={cn(
            "flex items-center gap-3 pb-4 pr-12 border-b-2 transition-all",
            step === 1 ? "border-blue-600" : "border-transparent opacity-40"
          )}
        >
          <span className="text-[10px] font-black uppercase tracking-[0.3em]">
            Organization
          </span>
        </div>
        <div
          className={cn(
            "flex items-center gap-3 pb-4 pr-12 border-b-2 transition-all",
            step === 2 ? "border-blue-600" : "border-transparent opacity-40"
          )}
        >
          <span className="text-[10px] font-black uppercase tracking-[0.3em]">
            Admin User
          </span>
        </div>
      </div>

      <AnimatePresence>
        {authError && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="mb-8 p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg flex items-center gap-3"
          >
            <AlertCircle className="h-5 w-5" />
            <span>{authError}</span>
          </motion.div>
        )}
      </AnimatePresence>

      <form
        onSubmit={handleFinalSubmit}
        onKeyDown={e => e.key === "Enter" && e.preventDefault()}
        className="grid grid-cols-1 lg:grid-cols-12 gap-16"
      >
        <div className="lg:col-span-8">
          {step === 1 ? (
            <motion.div
              initial={{ opacity: 0, x: -16 }}
              animate={{ opacity: 1, x: 0 }}
              className="space-y-10"
            >
              <section className="space-y-6">
                <label className={labelStyle}>Select Your Business Roles</label>
                <div className="flex flex-wrap gap-3">
                  {[
                    { id: "SU", label: "Supplier" },
                    { id: "TN", label: "Tenant" },
                    { id: "CU", label: "Customer" }
                  ].map(type => (
                    <button
                      key={type.id}
                      type="button"
                      onClick={() => toggleType(type.id)}
                      className={cn(
                        "px-6 h-12 text-[11px] font-bold uppercase border-2 rounded-lg transition-all",
                        selectedTypes?.includes(type.id)
                          ? "border-blue-600 bg-blue-600 text-white"
                          : "border-slate-200 text-slate-600"
                      )}
                    >
                      {type.label}
                    </button>
                  ))}
                </div>
                {errors.types && (
                  <p className={errorStyle}>{errors.types.message}</p>
                )}
              </section>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div className="md:col-span-2">
                  <label className={labelStyle}>Legal Company Name</label>
                  <Input {...form.register("Name")} className={inputStyle} />
                </div>

                <div className="md:col-span-2">
                  <label className={labelStyle}>Trading Name</label>
                  <Input {...form.register("TradingName")} className={inputStyle} />
                </div>

                <div>
                  <label className={labelStyle}>Business Type</label>
                  <select {...form.register("BusinessType")} className={inputStyle}>
                    <option value="">Select...</option>
                    {metadata.businessTypes.map(bt => (
                      <option key={bt.value} value={bt.value}>
                        {bt.description}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className={labelStyle}>Registration Number</label>
                  <Input {...form.register("RegistrationNumber")} className={inputStyle} />
                </div>

                <div>
                  <label className={labelStyle}>Tax PIN</label>
                  <Input {...form.register("TaxPIN")} className={inputStyle} />
                </div>

                <div>
                  <label className={labelStyle}>VAT Number</label>
                  <Input {...form.register("VATNumber")} className={inputStyle} />
                </div>

                <div>
                  <label className={labelStyle}>Business Email</label>
                  <Input type="email" {...form.register("Email")} className={inputStyle} />
                </div>

                <div>
                  <label className={labelStyle}>Phone</label>
                  <Input {...form.register("Phone")} className={inputStyle} />
                </div>

                {isSupplier && (
                  <div className="md:col-span-2">
                    <label className={labelStyle}>
                      Supplier Category <span className="text-red-500">*</span>
                    </label>
                    <select {...form.register("user_SupplierCategoryId")} className={inputStyle}>
                      <option value="">Select...</option>
                      {metadata.supplierCategories.map(cat => (
                        <option key={cat.id ?? cat.value} value={cat.id ?? cat.value}>
                          {cat.name ?? cat.description ?? cat.label ?? `Category ${cat.id ?? cat.value}`}
                        </option>
                      ))}
                    </select>
                    {errors.user_SupplierCategoryId && (
                      <p className={errorStyle}>{errors.user_SupplierCategoryId.message}</p>
                    )}
                  </div>
                )}

                <div>
                  <label className={labelStyle}>Website</label>
                  <Input {...form.register("Website")} className={inputStyle} />
                </div>

                <div>
                  <label className={labelStyle}>Country</label>
                  <select {...form.register("Country")} className={inputStyle}>
                    <option value="">Select...</option>
                    {metadata.countries.map(c => (
                      <option key={c.code} value={c.code}>
                        {c.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className={labelStyle}>City / Locality</label>
                  <select {...form.register("Location")} className={inputStyle}>
                    <option value="">Select...</option>
                    {metadata.localities.map(l => (
                      <option key={l.id} value={l.id}>
                        {l.name}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="md:col-span-2">
                  <label className={labelStyle}>Physical Address</label>
                  <Input {...form.register("PhysicalAddress")} className={inputStyle} />
                </div>

                {isTenant && (
                  <div className="md:col-span-2">
                    <label className={labelStyle}>Tenant Remarks</label>
                    <Input {...form.register("user_Remarks")} className={inputStyle} />
                  </div>
                )}

                {isCustomer && !isTenant && (
                  <div className="md:col-span-2">
                    <label className={labelStyle}>Customer Remarks</label>
                    <Input {...form.register("user_Remarks")} className={inputStyle} />
                  </div>
                )}

                {isCustomer && (
                  <>
                    <div className="md:col-span-2">
                      <label className={labelStyle}>Customer Date of Birth</label>
                      <Input type="date" {...form.register("user_DateOfBirth")} className={inputStyle} />
                      {errors.user_DateOfBirth && (
                        <p className={errorStyle}>{errors.user_DateOfBirth.message}</p>
                      )}
                    </div>
                    <div>
                      <label className={labelStyle}>Marital Status</label>
                      <select {...form.register("user_MaritalStatus")} className={inputStyle}>
                        <option value="">Select...</option>
                        {metadata.maritalStatuses.map(ms => (
                          <option key={ms.value ?? ms.id} value={ms.value ?? ms.id}>
                            {ms.description ?? ms.name ?? ms.label ?? ms.value ?? `Status ${ms.id ?? ''}`}
                          </option>
                        ))}
                      </select>
                      {errors.user_MaritalStatus && (
                        <p className={errorStyle}>{errors.user_MaritalStatus.message}</p>
                      )}
                    </div>
                    <div>
                      <label className={labelStyle}>Occupation</label>
                      <select {...form.register("user_Occupation")} className={inputStyle}>
                        <option value="">Select...</option>
                        {metadata.occupations.map(opt => (
                          <option key={opt.value ?? opt.id} value={opt.value ?? opt.id}>
                            {opt.description ?? opt.name ?? opt.label ?? opt.value ?? `Occupation ${opt.id ?? ''}`}
                          </option>
                        ))}
                      </select>
                      {errors.user_Occupation && (
                        <p className={errorStyle}>{errors.user_Occupation.message}</p>
                      )}
                    </div>
                  </>
                )}
              </div>

              <div className="p-6 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <div className="flex items-center gap-4">
                  <div className="h-10 w-10 bg-white border border-slate-200 rounded-full flex items-center justify-center text-blue-600">
                    <UserPlus className="h-5 w-5" />
                  </div>
                  <div>
                    <h4 className="text-sm font-bold text-slate-800">
                      Create Admin Account
                    </h4>
                    <p className="text-xs text-slate-500">
                      Enable portal access for this organization
                    </p>
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => form.setValue("createUser", !createUser)}
                  className={cn(
                    "w-12 h-6 rounded-full transition-all relative",
                    createUser ? "bg-blue-600" : "bg-slate-300"
                  )}
                >
                  <div
                    className={cn(
                      "absolute top-1 w-4 h-4 bg-white rounded-full transition-all",
                      createUser ? "right-1" : "left-1"
                    )}
                  />
                </button>
              </div>

              <Button
                type="button"
                onClick={nextStep}
                disabled={isSubmitting}
                className="h-14 bg-blue-600 text-white font-bold uppercase rounded-lg w-full"
              >
                {createUser ? "Continue to User Details" : "Finish Registration"}
              </Button>
            </motion.div>
          ) : (
            <motion.div
              initial={{ opacity: 0, x: 16 }}
              animate={{ opacity: 1, x: 0 }}
              className="space-y-10"
            >
              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <Input {...form.register("user_FirstName")} placeholder="First Name" className={inputStyle} />
                <Input {...form.register("user_LastName")} placeholder="Last Name" className={inputStyle} />
                <Input {...form.register("user_Email")} placeholder="Admin Email" className={inputStyle} />
                <Input {...form.register("user_Phone")} placeholder="Admin Phone" className={inputStyle} />

                <select {...form.register("user_Gender")} className={inputStyle}>
                  <option value="">Gender...</option>
                  {metadata.genders.map(g => (
                    <option key={g.value} value={g.value}>
                      {g.description}
                    </option>
                  ))}
                </select>

                <div className="relative">
                  <Input
                    type={showPassword ? "text" : "password"}
                    {...form.register("user_Password")}
                    placeholder="Password"
                    className={cn(inputStyle, "pr-11")}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(v => !v)}
                    className="absolute right-3 top-1/2 -translate-y-1/2"
                  >
                    {showPassword ? <EyeOff /> : <Eye />}
                  </button>
                </div>

                <div className="relative">
                  <Input
                    type={showConfirmPassword ? "text" : "password"}
                    {...form.register("user_Password_confirmation")}
                    placeholder="Confirm Password"
                    className={cn(inputStyle, "pr-11")}
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirmPassword(v => !v)}
                    className="absolute right-3 top-1/2 -translate-y-1/2"
                  >
                    {showConfirmPassword ? <EyeOff /> : <Eye />}
                  </button>
                </div>
              </div>

              <div className="flex gap-4">
                <Button
                  type="button"
                  onClick={() => setStep(1)}
                  className="h-14 bg-slate-200 text-slate-700 font-bold rounded-lg px-8"
                >
                  Back
                </Button>
                <Button
                  type="submit"
                  disabled={isSubmitting}
                  className="h-14 flex-1 bg-blue-600 text-white font-bold rounded-lg"
                >
                  Register
                </Button>
              </div>
            </motion.div>
          )}
        </div>
      </form>
    </div>
  )
}
