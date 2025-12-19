"use client"

import { useState, Suspense } from "react"
import { useRouter } from "next/navigation"
import Link from "next/link"
import { motion, AnimatePresence } from "framer-motion"
import {
  Eye,
  EyeOff,
  Loader2,
  Mail,
  Lock,
  User,
  Phone,
  AlertCircle,
  X,
  Check,
  Building2,
  FileText,
  Globe,
  Briefcase,
  ArrowLeft
} from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Field, FieldLabel, FieldError } from "@/components/common/field"
import { cn, handleApiErrors } from "@/lib/utils"
import { AuthHeader } from "../layout/auth-header"
import { Spinner } from "../common/spinner"
import { useAuthStore } from "@/store/auth-store"
import { useRegisterForm } from "@/hooks/use-register"

export default function SignUpPage() {
  const router = useRouter()
  const { step, setStep } = useAuthStore()
  const [authToken, setAuthToken] = useState<string | null>(null)

  const {
    register,
    handleSubmitAsync,
    errors,
    pwdShown,
    confirmShown,
    togglePwd,
    toggleConfirm,
    isSubmitting,
    form,
    setError,
    triggerFields
  } = useRegisterForm()

  const onRegisterAccount = async (payload: any) => {
    const isStepValid = await triggerFields(['firstName', 'lastName', 'email', 'phone', 'password', 'confirmPassword']);
    if (!isStepValid) return;

    form.clearErrors("root")
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify(payload),
      })

      const result = await res.json()

      if (res.status === 422) {
        handleApiErrors(result.errors, setError)
        return
      }

      if (!res.ok) {
        setError("root", { message: result.message || "Registration failed." })
        return
      }

      setAuthToken(result.token)
      setStep("profile")
    } catch {
      setError("root", { message: "Network error. Please check your connection." })
    }
  }

  const onCompleteProfile = async (payload: any) => {
    const isStepValid = await triggerFields(['thirdPartyName', 'registrationNumber', 'taxPIN', 'businessType', 'countryId']);
    if (!isStepValid) return;

    form.clearErrors("root")
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/complete-profile`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "Authorization": `Bearer ${authToken}`
        },
        body: JSON.stringify(payload),
      })

      const result = await res.json()

      if (res.status === 422) {
        handleApiErrors(result.errors, setError)
        return
      }

      if (!res.ok) {
        setError("root", { message: result.message || "Profile completion failed." })
        return
      }

      setStep("success")
      setTimeout(() => router.push("/signin?registrationSuccess=true"), 3500)
    } catch {
      setError("root", { message: "Network error. Please check your connection." })
    }
  }

  return (
    <Suspense fallback={<div className="flex min-h-screen items-center justify-center"><Spinner className="h-8 w-8 animate-spin text-blue-600" /></div>}>
      <div className="flex w-full min-h-screen">
        <motion.div
          initial={{ x: -100, opacity: 0 }}
          animate={{ x: 0, opacity: 1 }}
          className="hidden lg:flex w-1/3 bg-blue-700 text-white flex-col justify-between p-12"
        >
          <h1 className="text-2xl font-semibold">Craft Silicon</h1>
          <div>
            <h2 className="text-4xl font-bold leading-tight">Supplier Portal</h2>
            <p className="mt-4 text-white/90 text-lg">Streamline your business interactions with our self-service platform.</p>
          </div>
          <p className="text-white/60 text-sm">© 2025 Craft Silicon Ltd.</p>
        </motion.div>

        <div className="flex w-full lg:w-2/3 items-start justify-center px-8 lg:px-16 py-12 overflow-y-auto">
          <AnimatePresence mode="wait">
            {step === "form" || step === "profile" ? (
              <motion.div
                key={step}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                className="w-full max-w-3xl"
              >
                <div className="mb-8 lg:hidden"><AuthHeader /></div>

                {step === "profile" && (
                  <button
                    onClick={() => setStep("form")}
                    className="flex items-center gap-2 text-blue-600 hover:text-blue-800 text-sm font-medium mb-4 transition-colors"
                  >
                    <ArrowLeft className="h-4 w-4" /> Back to Personal Info
                  </button>
                )}

                <h2 className="text-3xl font-semibold text-gray-900">
                  {step === "form" ? "Create an account" : "Business Details"}
                </h2>
                <p className="mt-2 text-gray-500 text-sm">
                  {step === "form"
                    ? "Provide your basic info to get started."
                    : "Complete your profile with your company details."}
                </p>

                <AnimatePresence>
                  {errors.root && (
                    <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} className="mt-6 mb-6 flex items-center gap-3 bg-red-50 p-4 border-l-4 border-red-500 rounded-r-md">
                      <AlertCircle className="h-5 w-5 text-red-500" />
                      <span className="text-sm flex-1 text-red-800">{errors.root.message}</span>
                      <button onClick={() => form.clearErrors("root")}><X className="h-4 w-4 text-red-500" /></button>
                    </motion.div>
                  )}
                </AnimatePresence>

                <form onSubmit={(e) => {
                  e.preventDefault()
                  handleSubmitAsync(step === "form" ? onRegisterAccount : onCompleteProfile)
                }} className="space-y-8 mt-8">

                  {step === "form" && (
                    <div className="space-y-6">
                      <h3 className="text-lg font-medium border-b pb-2 flex items-center gap-2">
                        <User className="h-5 w-5 text-blue-600" /> Personal Info
                      </h3>
                      <div className="grid sm:grid-cols-2 gap-6">
                        <Field>
                          <FieldLabel>First Name</FieldLabel>
                          <div className="relative">
                            <User className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input placeholder="John" className={cn("pl-11", errors.firstName && "border-red-500")} {...register("firstName")} />
                          </div>
                          {errors.firstName && <FieldError>{errors.firstName.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel>Last Name</FieldLabel>
                          <div className="relative">
                            <User className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input placeholder="Doe" className={cn("pl-11", errors.lastName && "border-red-500")} {...register("lastName")} />
                          </div>
                          {errors.lastName && <FieldError>{errors.lastName.message}</FieldError>}
                        </Field>
                      </div>

                      <div className="grid sm:grid-cols-2 gap-6">
                        <Field>
                          <FieldLabel>Email Address</FieldLabel>
                          <div className="relative">
                            <Mail className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input type="email" placeholder="john.doe@company.com" className={cn("pl-11", errors.email && "border-red-500")} {...register("email")} />
                          </div>
                          {errors.email && <FieldError>{errors.email.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel>Phone Number</FieldLabel>
                          <div className="relative">
                            <Phone className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input type="tel" placeholder="+254712345678" className={cn("pl-11", errors.phone && "border-red-500")} {...register("phone")} />
                          </div>
                          {errors.phone && <FieldError>{errors.phone.message}</FieldError>}
                        </Field>
                      </div>

                      <div className="grid sm:grid-cols-2 gap-6">
                        <Field>
                          <FieldLabel>Password</FieldLabel>
                          <div className="relative">
                            <Lock className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input type={pwdShown ? "text" : "password"} placeholder="••••••••" className={cn("pl-11 pr-10", errors.password && "border-red-500")} {...register("password")} />
                            <button type="button" onClick={togglePwd} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                              {pwdShown ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                            </button>
                          </div>
                          {errors.password && <FieldError>{errors.password.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel>Confirm Password</FieldLabel>
                          <div className="relative">
                            <Lock className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input type={confirmShown ? "text" : "password"} placeholder="••••••••" className={cn("pl-11 pr-10", errors.confirmPassword && "border-red-500")} {...register("confirmPassword")} />
                            <button type="button" onClick={toggleConfirm} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                              {confirmShown ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                            </button>
                          </div>
                          {errors.confirmPassword && <FieldError>{errors.confirmPassword.message}</FieldError>}
                        </Field>
                      </div>
                    </div>
                  )}

                  {step === "profile" && (
                    <div className="space-y-6">
                      <h3 className="text-lg font-medium border-b pb-2 flex items-center gap-2">
                        <Building2 className="h-5 w-5 text-blue-600" /> Business Details
                      </h3>
                      <div className="grid sm:grid-cols-2 gap-6">
                        <Field>
                          <FieldLabel>Company Name</FieldLabel>
                          <div className="relative">
                            <Building2 className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input placeholder="Legal Entity Name" className={cn("pl-11", errors.thirdPartyName && "border-red-500")} {...register("thirdPartyName")} />
                          </div>
                          {errors.thirdPartyName && <FieldError>{errors.thirdPartyName.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel>Tax PIN</FieldLabel>
                          <div className="relative">
                            <FileText className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input placeholder="KRA PIN" className={cn("pl-11", errors.taxPIN && "border-red-500")} {...register("taxPIN")} />
                          </div>
                          {errors.taxPIN && <FieldError>{errors.taxPIN.message}</FieldError>}
                        </Field>
                      </div>

                      <div className="grid sm:grid-cols-2 gap-6">
                        <Field>
                          <FieldLabel>Registration Number</FieldLabel>
                          <div className="relative">
                            <Briefcase className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input placeholder="PVT-XXXXXX" className={cn("pl-11", errors.registrationNumber && "border-red-500")} {...register("registrationNumber")} />
                          </div>
                          {errors.registrationNumber && <FieldError>{errors.registrationNumber.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel>Business Type</FieldLabel>
                          <Input placeholder="e.g. Limited Company" className={errors.businessType && "border-red-500"} {...register("businessType")} />
                          {errors.businessType && <FieldError>{errors.businessType.message}</FieldError>}
                        </Field>
                      </div>

                      <Field>
                        <FieldLabel>Country</FieldLabel>
                        <div className="relative">
                          <Globe className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                          <Input placeholder="Kenya" className={cn("pl-11", errors.countryId && "border-red-500")} {...register("countryId")} />
                        </div>
                        {errors.countryId && <FieldError>{errors.countryId.message}</FieldError>}
                      </Field>
                    </div>
                  )}

                  <Button type="submit" disabled={isSubmitting} className="w-full h-12 bg-blue-700 hover:bg-blue-800 text-white font-semibold shadow-sm transition-all">
                    {isSubmitting ? (
                      <span className="flex items-center gap-2"><Loader2 className="h-5 w-5 animate-spin" /> Processing...</span>
                    ) : (
                      step === "form" ? "Next: Business Details" : "Complete Registration"
                    )}
                  </Button>

                  {step === "form" && (
                    <p className="text-sm text-gray-500 text-center mt-6">
                      Already have an account? <Link href="/signin" className="text-blue-700 hover:underline font-semibold">Sign in</Link>
                    </p>
                  )}
                </form>
              </motion.div>
            ) : (
              <SuccessState />
            )}
          </AnimatePresence>
        </div>
      </div>
    </Suspense>
  )
}

function SuccessState() {
  return (
    <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="flex flex-col items-center justify-center py-16 text-center">
      <div className="mb-6 flex h-24 w-24 items-center justify-center bg-green-50 rounded-full">
        <Check className="h-12 w-12 text-green-600" />
      </div>
      <h2 className="text-3xl font-bold mb-4 text-gray-900">Registration successful!</h2>
      <p className="text-gray-500 mb-8 max-w-md">Your profile is complete. Please check your email to verify your address before logging in.</p>
      <div className="flex items-center gap-3 text-sm text-blue-600 font-medium">
        <Spinner className="h-4 w-4 animate-spin" /> Redirecting to login...
      </div>
    </motion.div>
  )
}