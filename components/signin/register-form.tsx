"use client"

import * as React from "react"
import { AlertCircle, CheckCircle2, Eye, EyeOff, Loader2, Shield, Building2, User, Mail, Phone, MapPin, FileText, Lock, CreditCard } from "lucide-react"
import { motion, AnimatePresence } from "framer-motion"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { useRouter } from "next/navigation"
import { cn } from "@/lib/utils"
import { useRegisterForm } from "@/hooks/use-register"

interface PasswordStrength {
  score: number
  label: string
  color: string
}

export default function RegisterForm() {
  const router = useRouter()
  const [step, setStep] = React.useState(1)
  const [authError, setAuthError] = React.useState<string | null>(null)
  const [success, setSuccess] = React.useState(false)
  const [showPassword, setShowPassword] = React.useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = React.useState(false)
  const [passwordStrength, setPasswordStrength] = React.useState<PasswordStrength>({ score: 0, label: "", color: "" })

  const {
    form,
    metadata,
    isLoadingMetadata,
    metadataError,
    onSubmit,
    isSubmitting,
    errors,
    toggleType,
    selectedTypes,
    isSupplier,
    isTenant,
    isCustomer
  } = useRegisterForm()

  const passwordValue = form.watch("user_Password")

  const roleOptions = [
    { id: "SU", label: "Supplier", icon: Building2 },
    { id: "TN", label: "Tenant", icon: User },
    { id: "CU", label: "Customer", icon: User }
  ]

  const calculatePasswordStrength = (password: string): PasswordStrength => {
    if (!password) return { score: 0, label: "", color: "" }

    let score = 0
    if (password.length >= 8) score++
    if (password.length >= 12) score++
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++
    if (/\d/.test(password)) score++
    if (/[^a-zA-Z0-9]/.test(password)) score++

    const strengthMap = [
      { score: 0, label: "", color: "" },
      { score: 1, label: "Weak", color: "bg-red-500" },
      { score: 2, label: "Fair", color: "bg-orange-500" },
      { score: 3, label: "Good", color: "bg-yellow-500" },
      { score: 4, label: "Strong", color: "bg-green-500" },
      { score: 5, label: "Excellent", color: "bg-emerald-600" },
    ]

    return strengthMap[Math.min(score, 5)]
  }

  React.useEffect(() => {
    setPasswordStrength(calculatePasswordStrength(passwordValue))
  }, [passwordValue])

  const nextStep = async () => {
    const fields = ["Name", "Email", "Phone", "RegistrationNumber", "TaxPIN", "Country", "Location", "BusinessType", "types"] as any[]

    if (isSupplier) {
      fields.push("supplier_category_id")
    }

    if (isTenant) {
      fields.push("tenant_Remarks")
    }

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
      setAuthError(error.message || "An unexpected error occurred. Please try again.")
    }
  }

  const inputStyle = "h-12 w-full border border-slate-200 bg-white px-4 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all placeholder:text-slate-400 disabled:bg-slate-50 disabled:text-slate-400"
  const textareaStyle = "w-full border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all placeholder:text-slate-400 disabled:bg-slate-50 disabled:text-slate-400 resize-none"
  const labelStyle = "text-[10px] font-bold uppercase tracking-widest text-slate-600 mb-2 block"
  const errorStyle = "text-xs text-red-600 mt-1.5 flex items-center gap-1"

  if (isLoadingMetadata) {
    return (
      <div className="max-w-5xl mx-auto px-6 py-20 text-center">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600 mx-auto mb-4" />
        <p className="text-sm text-slate-500">Loading registration form...</p>
      </div>
    )
  }

  if (metadataError) {
    return (
      <div className="max-w-5xl mx-auto px-6 py-20 text-center">
        <AlertCircle className="h-8 w-8 text-red-600 mx-auto mb-4" />
        <p className="text-sm text-red-600 mb-4">{metadataError}</p>
        <Button onClick={() => window.location.reload()} className="h-10 px-6">
          Refresh Page
        </Button>
      </div>
    )
  }

  if (success) {
    return (
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="max-w-md mx-auto text-center py-20 px-8"
      >
        <motion.div
          initial={{ scale: 0 }}
          animate={{ scale: 1 }}
          transition={{ delay: 0.2, type: "spring", stiffness: 200 }}
          className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-50 mb-6"
        >
          <CheckCircle2 className="h-10 w-10 text-green-600" />
        </motion.div>
        <h2 className="text-2xl font-bold text-slate-900 mb-3">Registration Complete!</h2>
        <p className="text-slate-600 text-sm mb-8 leading-relaxed">
          We've sent a verification email to your inbox. Please verify your email address to complete your registration.
        </p>
        <Button
          onClick={() => router.push("/signin")}
          className="w-full max-w-xs h-12 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-all"
        >
          GO TO SIGN IN
        </Button>
      </motion.div>
    )
  }

  return (
    <div className="max-w-5xl mx-auto px-6 pb-20">
      <div className="flex items-center border-b border-slate-200 mb-12">
        <motion.div
          className={cn(
            "flex items-center gap-3 pb-4 pr-12 relative transition-all",
            step === 1 ? "border-b-2 border-blue-600" : "opacity-40"
          )}
        >
          <div className={cn(
            "flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold transition-all",
            step === 1 ? "bg-blue-600 text-white" : "bg-slate-200 text-slate-500"
          )}>
            {step > 1 ? <CheckCircle2 className="h-4 w-4" /> : "1"}
          </div>
          <span className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-700">Organization</span>
        </motion.div>

        <motion.div
          className={cn(
            "flex items-center gap-3 pb-4 pr-12 relative transition-all",
            step === 2 ? "border-b-2 border-blue-600" : "opacity-40"
          )}
        >
          <div className={cn(
            "flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold transition-all",
            step === 2 ? "bg-blue-600 text-white" : "bg-slate-200 text-slate-500"
          )}>
            2
          </div>
          <span className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-700">Admin User</span>
        </motion.div>
      </div>

      <AnimatePresence mode="wait">
        {authError && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            className="mb-8 p-4 bg-red-50 text-red-700 text-sm border border-red-200 rounded-lg flex items-start gap-3"
          >
            <AlertCircle className="h-5 w-5 flex-shrink-0 mt-0.5" />
            <span>{authError}</span>
          </motion.div>
        )}
      </AnimatePresence>

      <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-12 gap-16">
        <div className="lg:col-span-8">
          <AnimatePresence mode="wait">
            {step === 1 ? (
              <motion.div
                key="step1"
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 20 }}
                transition={{ duration: 0.3 }}
                className="space-y-10"
              >
                <section className="space-y-6">
                  <div className="flex items-center gap-2 mb-4">
                    <Shield className="h-5 w-5 text-blue-600" />
                    <label className={cn(labelStyle, "mb-0")}>Select Your Business Roles</label>
                  </div>
                  <div className="flex flex-wrap gap-3">
                    {roleOptions.map((type) => (
                      <motion.button
                        key={type.id}
                        type="button"
                        onClick={() => toggleType(type.id)}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                        className={cn(
                          "flex items-center gap-2 px-6 h-12 text-[11px] font-bold uppercase tracking-wider border-2 transition-all rounded-lg",
                          selectedTypes?.includes(type.id)
                            ? "border-blue-600 bg-blue-600 text-white shadow-lg shadow-blue-200"
                            : "border-slate-200 text-slate-600 hover:border-blue-300 hover:bg-blue-50"
                        )}
                      >
                        <type.icon className="h-4 w-4" />
                        {type.label}
                      </motion.button>
                    ))}
                  </div>
                  {errors.types && (
                    <p className={errorStyle}>
                      <AlertCircle className="h-3 w-3" />
                      {errors.types.message}
                    </p>
                  )}
                  {selectedTypes && selectedTypes.length > 0 && (
                    <p className="text-xs text-slate-600 flex items-center gap-2">
                      <CheckCircle2 className="h-3 w-3 text-green-600" />
                      {selectedTypes.length} role{selectedTypes.length > 1 ? 's' : ''} selected
                    </p>
                  )}
                </section>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                  <div className="md:col-span-2">
                    <label className={labelStyle}>
                      <Building2 className="inline h-3 w-3 mr-1" />
                      Legal Company Name
                    </label>
                    <Input
                      {...form.register("Name")}
                      placeholder="Enter your registered company name"
                      className={cn(inputStyle, errors.Name && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.Name && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.Name.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <Building2 className="inline h-3 w-3 mr-1" />
                      Trading Name <span className="text-slate-400">(Optional)</span>
                    </label>
                    <Input
                      {...form.register("TradingName")}
                      placeholder="Business trading name"
                      className={inputStyle}
                    />
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <FileText className="inline h-3 w-3 mr-1" />
                      Registration Number
                    </label>
                    <Input
                      {...form.register("RegistrationNumber")}
                      placeholder="Company registration number"
                      className={cn(inputStyle, errors.RegistrationNumber && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.RegistrationNumber && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.RegistrationNumber.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <Mail className="inline h-3 w-3 mr-1" />
                      Business Email
                    </label>
                    <Input
                      {...form.register("Email")}
                      type="email"
                      placeholder="company@example.com"
                      className={cn(inputStyle, errors.Email && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.Email && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.Email.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <Phone className="inline h-3 w-3 mr-1" />
                      Phone Number
                    </label>
                    <Input
                      {...form.register("Phone")}
                      placeholder="+254 700 000 000"
                      className={cn(inputStyle, errors.Phone && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.Phone && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.Phone.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <MapPin className="inline h-3 w-3 mr-1" />
                      Country
                    </label>
                    <select
                      {...form.register("Country")}
                      className={cn(inputStyle, errors.Country && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    >
                      <option value="">Select Country...</option>
                      {metadata.countries.map((c) => (
                        <option key={c.id} value={c.code}>{c.name}</option>
                      ))}
                    </select>
                    {errors.Country && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.Country.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>City / Locality</label>
                    <select
                      {...form.register("Location")}
                      className={cn(inputStyle, errors.Location && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                      disabled={metadata.localities.length === 0}
                    >
                      <option value="">Select City...</option>
                      {metadata.localities.map((l) => (
                        <option key={l.id} value={l.id}>{l.name}</option>
                      ))}
                    </select>
                    {errors.Location && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.Location.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <FileText className="inline h-3 w-3 mr-1" />
                      Tax PIN
                    </label>
                    <Input
                      {...form.register("TaxPIN")}
                      placeholder="A000000000Z"
                      className={cn(inputStyle, errors.TaxPIN && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.TaxPIN && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.TaxPIN.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      VAT Number <span className="text-slate-400">(Optional)</span>
                    </label>
                    <Input
                      {...form.register("VATNumber")}
                      placeholder="VAT registration number"
                      className={inputStyle}
                    />
                  </div>

                  <div>
                    <label className={labelStyle}>Business Type</label>
                    <select
                      {...form.register("BusinessType")}
                      className={cn(inputStyle, errors.BusinessType && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    >
                      <option value="">Select Type...</option>
                      {metadata.businessTypes.map((t) => (
                        <option key={t.value} value={t.value}>{t.description}</option>
                      ))}
                    </select>
                    {errors.BusinessType && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.BusinessType.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      Physical Address <span className="text-slate-400">(Optional)</span>
                    </label>
                    <Input
                      {...form.register("PhysicalAddress")}
                      placeholder="Street address"
                      className={inputStyle}
                    />
                  </div>

                  <div className="md:col-span-2">
                    <label className={labelStyle}>
                      Website <span className="text-slate-400">(Optional)</span>
                    </label>
                    <Input
                      {...form.register("Website")}
                      type="url"
                      placeholder="https://www.company.com"
                      className={cn(inputStyle, errors.Website && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.Website && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.Website.message}
                      </p>
                    )}
                  </div>

                  <AnimatePresence>
                    {isSupplier && (
                      <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: "auto" }}
                        exit={{ opacity: 0, height: 0 }}
                        transition={{ duration: 0.3 }}
                        className="md:col-span-2"
                      >
                        <label className={cn(labelStyle, "text-blue-600")}>
                          Supplier Category <span className="text-red-600">*</span>
                        </label>
                        <select
                          {...form.register("supplier_category_id")}
                          className={cn(inputStyle, "border-blue-200 focus:border-blue-500", errors.supplier_category_id && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                        >
                          <option value="">Select Category...</option>
                          {metadata.supplierCategories.map((s) => (
                            <option key={s.id} value={s.id}>{s.name}</option>
                          ))}
                        </select>
                        {errors.supplier_category_id && (
                          <p className={errorStyle}>
                            <AlertCircle className="h-3 w-3" />
                            {errors.supplier_category_id.message}
                          </p>
                        )}
                      </motion.div>
                    )}

                    {isTenant && (
                      <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: "auto" }}
                        exit={{ opacity: 0, height: 0 }}
                        transition={{ duration: 0.3 }}
                        className="md:col-span-2"
                      >
                        <label className={cn(labelStyle, "text-purple-600")}>
                          Tenant Remarks <span className="text-red-600">*</span>
                        </label>
                        <textarea
                          {...form.register("tenant_Remarks")}
                          rows={4}
                          placeholder="Please provide details about your tenancy requirements, property preferences, or any specific information..."
                          className={cn(textareaStyle, "border-purple-200 focus:border-purple-500", errors.tenant_Remarks && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                        />
                        {errors.tenant_Remarks && (
                          <p className={errorStyle}>
                            <AlertCircle className="h-3 w-3" />
                            {errors.tenant_Remarks.message}
                          </p>
                        )}
                      </motion.div>
                    )}

                    {isCustomer && (
                      <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: "auto" }}
                        exit={{ opacity: 0, height: 0 }}
                        transition={{ duration: 0.3 }}
                        className="md:col-span-2"
                      >
                        <label className={cn(labelStyle, "text-green-600")}>
                          <CreditCard className="inline h-3 w-3 mr-1" />
                          Preferred Payment Method <span className="text-slate-400">(Optional)</span>
                        </label>
                        <select
                          {...form.register("customer_PreferredPaymentMethod")}
                          className={cn(inputStyle, "border-green-200 focus:border-green-500")}
                        >
                          <option value="">Select Payment Method...</option>
                          <option value="cash">Cash</option>
                          <option value="bank_transfer">Bank Transfer</option>
                          <option value="mobile_money">Mobile Money</option>
                          <option value="credit_card">Credit Card</option>
                          <option value="check">Check</option>
                        </select>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>

                <Button
                  type="button"
                  onClick={nextStep}
                  className="h-14 w-full md:w-64 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-[0.2em] rounded-lg shadow-lg shadow-blue-200 transition-all"
                >
                  Continue to Admin Setup
                </Button>
              </motion.div>
            ) : (
              <motion.div
                key="step2"
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -20 }}
                transition={{ duration: 0.3 }}
                className="space-y-10"
              >
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                  <div>
                    <label className={labelStyle}>First Name</label>
                    <Input
                      {...form.register("user_FirstName")}
                      placeholder="John"
                      className={cn(inputStyle, errors.user_FirstName && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.user_FirstName && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.user_FirstName.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>Last Name</label>
                    <Input
                      {...form.register("user_LastName")}
                      placeholder="Doe"
                      className={cn(inputStyle, errors.user_LastName && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.user_LastName && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.user_LastName.message}
                      </p>
                    )}
                  </div>

                  <div className="md:col-span-2">
                    <label className={labelStyle}>
                      <Mail className="inline h-3 w-3 mr-1" />
                      Admin Email
                    </label>
                    <Input
                      {...form.register("user_Email")}
                      type="email"
                      placeholder="admin@example.com"
                      className={cn(inputStyle, errors.user_Email && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.user_Email && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.user_Email.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <Phone className="inline h-3 w-3 mr-1" />
                      Admin Phone
                    </label>
                    <Input
                      {...form.register("user_Phone")}
                      placeholder="+254 700 000 000"
                      className={cn(inputStyle, errors.user_Phone && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    />
                    {errors.user_Phone && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.user_Phone.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>Gender</label>
                    <select
                      {...form.register("user_Gender")}
                      className={cn(inputStyle, errors.user_Gender && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                    >
                      <option value="">Select Gender...</option>
                      {metadata.genders.map((g) => (
                        <option key={g.value} value={g.value}>{g.description}</option>
                      ))}
                    </select>
                    {errors.user_Gender && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.user_Gender.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>
                      <Lock className="inline h-3 w-3 mr-1" />
                      Password
                    </label>
                    <div className="relative">
                      <Input
                        type={showPassword ? "text" : "password"}
                        {...form.register("user_Password")}
                        placeholder="Create a strong password"
                        className={cn(inputStyle, "pr-12", errors.user_Password && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                      >
                        {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </button>
                    </div>
                    {passwordValue && passwordStrength.score > 0 && (
                      <div className="mt-2 space-y-1">
                        <div className="flex gap-1">
                          {[1, 2, 3, 4, 5].map((level) => (
                            <div
                              key={level}
                              className={cn(
                                "h-1 flex-1 rounded-full transition-all",
                                level <= passwordStrength.score ? passwordStrength.color : "bg-slate-200"
                              )}
                            />
                          ))}
                        </div>
                        <p className="text-xs text-slate-600">
                          Strength: <span className="font-semibold">{passwordStrength.label}</span>
                        </p>
                      </div>
                    )}
                    {errors.user_Password && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.user_Password.message}
                      </p>
                    )}
                  </div>

                  <div>
                    <label className={labelStyle}>Confirm Password</label>
                    <div className="relative">
                      <Input
                        type={showConfirmPassword ? "text" : "password"}
                        {...form.register("user_Password_confirmation")}
                        placeholder="Re-enter your password"
                        className={cn(inputStyle, "pr-12", errors.user_Password_confirmation && "border-red-300 focus:border-red-500 focus:ring-red-100")}
                      />
                      <button
                        type="button"
                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                      >
                        {showConfirmPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </button>
                    </div>
                    {errors.user_Password_confirmation && (
                      <p className={errorStyle}>
                        <AlertCircle className="h-3 w-3" />
                        {errors.user_Password_confirmation.message}
                      </p>
                    )}
                  </div>
                </div>

                <div className="flex flex-col sm:flex-row gap-4">
                  <Button
                    type="button"
                    onClick={() => {
                      setStep(1)
                      window.scrollTo({ top: 0, behavior: "smooth" })
                    }}
                    className="h-14 w-full sm:w-32 border-2 border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs uppercase tracking-[0.2em] rounded-lg transition-all"
                  >
                    Back
                  </Button>
                  <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="h-14 w-full sm:flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold text-xs uppercase tracking-[0.2em] rounded-lg shadow-lg shadow-blue-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {isSubmitting ? (
                      <span className="flex items-center justify-center gap-2">
                        <Loader2 className="h-4 w-4 animate-spin" />
                        Creating Account...
                      </span>
                    ) : (
                      "Complete Registration"
                    )}
                  </Button>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        <div className="lg:col-span-4">
          <div className="sticky top-8 space-y-6">
            <div className="bg-gradient-to-br from-blue-50 to-slate-50 rounded-2xl p-6 border border-blue-100">
              <h3 className="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <Shield className="h-4 w-4 text-blue-600" />
                Registration Progress
              </h3>
              <div className="space-y-3 text-sm">
                <div className="flex items-center gap-3">
                  <div className={cn(
                    "flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold",
                    step >= 1 ? "bg-blue-600 text-white" : "bg-slate-200 text-slate-500"
                  )}>
                    {step > 1 ? <CheckCircle2 className="h-3 w-3" /> : "1"}
                  </div>
                  <span className={step >= 1 ? "text-slate-900 font-medium" : "text-slate-500"}>
                    Organization Details
                  </span>
                </div>
                <div className="flex items-center gap-3">
                  <div className={cn(
                    "flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold",
                    step >= 2 ? "bg-blue-600 text-white" : "bg-slate-200 text-slate-500"
                  )}>
                    2
                  </div>
                  <span className={step >= 2 ? "text-slate-900 font-medium" : "text-slate-500"}>
                    Admin User Setup
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form >
    </div >
  )
}