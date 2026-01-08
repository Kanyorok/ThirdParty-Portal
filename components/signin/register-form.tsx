"use client"

import * as React from "react"
import { AlertCircle, CheckCircle2, Eye, EyeOff, Shield, Building2, User, Mail, Phone, MapPin, FileText, Lock, Calendar, Briefcase, Heart } from "lucide-react"
import { motion, AnimatePresence } from "framer-motion"
import { Spinner } from "@/components/common/spinner"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { useRouter } from "next/navigation"
import { cn } from "@/lib/utils"
import { useRegisterForm } from "@/hooks/use-register"

export default function RegisterForm() {
  const router = useRouter()
  const [step, setStep] = React.useState(1)
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
    isCustomer
  } = useRegisterForm()

  const passwordValue = form.watch("user_Password")

  const nextStep = async () => {
    const fields = ["Name", "Email", "Phone", "RegistrationNumber", "TaxPIN", "Country", "Location", "BusinessType", "types"] as any[]
    if (isSupplier) fields.push("user_SupplierCategoryId")
    if (isTenant) fields.push("user_Remarks")

    const isValid = await form.trigger(fields)
    if (isValid) {
      setStep(2)
      window.scrollTo({ top: 0, behavior: "smooth" })
    }
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setAuthError(null)
    try {
      await onSubmit(e)
      setSuccess(true)
    } catch (error: any) {
      setAuthError(error.message || "An unexpected error occurred.")
    }
  }

  const inputStyle = "h-12 w-full border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all disabled:bg-slate-50"
  const labelStyle = "text-[10px] font-bold uppercase tracking-widest text-slate-600 mb-2 block"
  const errorStyle = "text-xs text-red-600 mt-1.5 flex items-center gap-1"

  if (isLoadingMetadata) return <div className="py-20 text-center"><Spinner className="h-8 w-8 animate-spin text-blue-600 mx-auto" /></div>
  if (metadataError) return <div className="py-20 text-center text-red-600">{metadataError}</div>

  if (success) {
    return (
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="max-w-md mx-auto text-center py-20 px-8">
        <CheckCircle2 className="h-20 w-20 text-green-600 mx-auto mb-6" />
        <h2 className="text-2xl font-bold mb-3">Registration Complete!</h2>
        <Button onClick={() => router.push("/signin")} className="w-full h-12 bg-blue-600 text-white font-bold rounded-lg">GO TO SIGN IN</Button>
      </motion.div>
    )
  }

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

      <AnimatePresence>
        {authError && (
          <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} className="mb-8 p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg flex items-center gap-3">
            <AlertCircle className="h-5 w-5" />
            <span>{authError}</span>
          </motion.div>
        )}
      </AnimatePresence>

      <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-12 gap-16">
        <div className="lg:col-span-8">
          {step === 1 ? (
            <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} className="space-y-10">
              <section className="space-y-6">
                <label className={labelStyle}>Select Your Business Roles</label>
                <div className="flex flex-wrap gap-3">
                  {[{ id: "SU", label: "Supplier" }, { id: "TN", label: "Tenant" }, { id: "CU", label: "Customer" }].map((type) => (
                    <button
                      key={type.id}
                      type="button"
                      onClick={() => toggleType(type.id)}
                      className={cn("px-6 h-12 text-[11px] font-bold uppercase border-2 rounded-lg transition-all", selectedTypes?.includes(type.id) ? "border-blue-600 bg-blue-600 text-white" : "border-slate-200 text-slate-600")}
                    >
                      {type.label}
                    </button>
                  ))}
                </div>
                {errors.types && <p className={errorStyle}>{errors.types.message}</p>}
              </section>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div className="md:col-span-2">
                  <label className={labelStyle}>Legal Company Name</label>
                  <Input {...form.register("Name")} className={cn(inputStyle, errors.Name && "border-red-300")} />
                </div>

                <div>
                  <label className={labelStyle}>Business Type</label>
                  <select {...form.register("BusinessType")} className={cn(inputStyle, errors.BusinessType && "border-red-300")}>
                    <option value="">Select Type...</option>
                    {metadata.businessTypes.map((bt: any) => (
                      <option key={bt.value} value={bt.value}>{bt.description}</option>
                    ))}
                  </select>
                  {errors.BusinessType && <p className={errorStyle}>{errors.BusinessType.message}</p>}
                </div>

                <div>
                  <label className={labelStyle}>Registration Number</label>
                  <Input {...form.register("RegistrationNumber")} className={cn(inputStyle, errors.RegistrationNumber && "border-red-300")} />
                </div>

                {isSupplier && (
                  <div className="md:col-span-2">
                    <label className={labelStyle}>Supplier Category</label>
                    <select {...form.register("user_SupplierCategoryId")} className={inputStyle}>
                      <option value="">Select Category...</option>
                      {metadata.supplierCategories.map((sc: any) => <option key={sc.id} value={sc.id}>{sc.name}</option>)}
                    </select>
                  </div>
                )}

                <div>
                  <label className={labelStyle}>Business Email</label>
                  <Input {...form.register("Email")} type="email" className={cn(inputStyle, errors.Email && "border-red-300")} />
                </div>
                <div>
                  <label className={labelStyle}>Phone Number</label>
                  <Input {...form.register("Phone")} className={cn(inputStyle, errors.Phone && "border-red-300")} />
                </div>
                <div>
                  <label className={labelStyle}>Country</label>
                  <select {...form.register("Country")} className={inputStyle}>
                    <option value="">Select...</option>
                    {metadata.countries.map((c: any) => <option key={c.id} value={c.code}>{c.name}</option>)}
                  </select>
                </div>
                <div>
                  <label className={labelStyle}>City / Locality</label>
                  <select {...form.register("Location")} className={inputStyle} disabled={metadata.localities.length === 0}>
                    <option value="">Select...</option>
                    {metadata.localities.map((l: any) => <option key={l.id} value={l.id}>{l.name}</option>)}
                  </select>
                </div>
                <div>
                  <label className={labelStyle}>Tax PIN</label>
                  <Input {...form.register("TaxPIN")} className={cn(inputStyle, errors.TaxPIN && "border-red-300")} />
                </div>
              </div>
              <Button type="button" onClick={nextStep} className="h-14 bg-blue-600 text-white font-bold uppercase rounded-lg">Continue</Button>
            </motion.div>
          ) : (
            <motion.div initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} className="space-y-10">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <Input {...form.register("user_FirstName")} placeholder="First Name" className={inputStyle} />
                <Input {...form.register("user_LastName")} placeholder="Last Name" className={inputStyle} />

                {isCustomer && (
                  <div className="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Input type="date" {...form.register("user_DateOfBirth")} className={inputStyle} />
                    <select {...form.register("user_MaritalStatus")} className={inputStyle}>
                      <option value="">Marital Status...</option>
                      {metadata.maritalStatuses.map((m: any) => <option key={m.value} value={m.value}>{m.description}</option>)}
                    </select>
                    <select {...form.register("user_Occupation")} className={inputStyle}>
                      <option value="">Occupation...</option>
                      {metadata.occupations.map((o: any) => <option key={o.value} value={o.value}>{o.description}</option>)}
                    </select>
                  </div>
                )}

                <Input {...form.register("user_Email")} placeholder="Admin Email" className={inputStyle} />
                <Input {...form.register("user_Phone")} placeholder="Admin Phone" className={inputStyle} />
                <select {...form.register("user_Gender")} className={inputStyle}>
                  <option value="">Gender...</option>
                  {metadata.genders.map((g: any) => <option key={g.value} value={g.value}>{g.description}</option>)}
                </select>
                <Input type={showPassword ? "text" : "password"} {...form.register("user_Password")} placeholder="Password" className={inputStyle} />
                <Input type={showConfirmPassword ? "text" : "password"} {...form.register("user_Password_confirmation")} placeholder="Confirm" className={inputStyle} />
              </div>
              <div className="flex gap-4">
                <Button type="button" onClick={() => setStep(1)} className="h-14 bg-slate-200 text-slate-700 font-bold rounded-lg">Back</Button>
                <Button type="submit" disabled={isSubmitting} className="h-14 flex-1 bg-blue-600 text-white font-bold rounded-lg">
                  {isSubmitting ? "Processing..." : "Register"}
                </Button>
              </div>
            </motion.div>
          )}
        </div>
      </form>
    </div>
  )
}