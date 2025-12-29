"use client"

import { useState, Suspense, useEffect, useMemo } from "react"
import { useRouter } from "next/navigation"
import { motion, AnimatePresence } from "framer-motion"
import {
  Eye,
  EyeOff,
  Loader2,
  Mail,
  Building2,
  ArrowLeft,
  ChevronRight,
  ShieldCheck,
  Check,
  AlertCircle,
  X,
  MapPin,
  Hash,
  Search,
  Globe
} from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Field, FieldLabel, FieldError } from "@/components/common/field"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn, handleApiErrors } from "@/lib/utils"
import { Spinner } from "../common/spinner"
import { useAuthStore } from "@/store/auth-store"
import { useRegisterForm } from "@/hooks/use-register"
import Link from "next/link"

interface Metadata {
  id: number
  name: string
  flag?: string
}

export default function SignUpPage() {
  const router = useRouter()
  const { step, setStep } = useAuthStore()
  const [authToken, setAuthToken] = useState<string | null>(null)
  const [countries, setCountries] = useState<Metadata[]>([])
  const [businessTypes, setBusinessTypes] = useState<Metadata[]>([])
  const [supplierCategories, setSupplierCategories] = useState<Metadata[]>([])
  const [selectedCats, setSelectedCats] = useState<number[]>([])
  const [catSearch, setCatSearch] = useState("")

  const {
    form,
    pwdShown,
    confirmShown,
    togglePwd,
    toggleConfirm,
    triggerFields,
  } = useRegisterForm()

  const {
    register,
    watch,
    setValue,
    setError,
    clearErrors,
    getValues,
    formState: { errors, isSubmitting }
  } = form

  const selectedCountryId = watch("countryId")
  const selectedBusinessTypeId = watch("businessType")
  const accountType = "supplier"

  const filteredCats = useMemo(() =>
    supplierCategories.filter(cat =>
      cat.name.toLowerCase().includes(catSearch.toLowerCase()) &&
      !selectedCats.includes(cat.id)
    ), [supplierCategories, catSearch, selectedCats]
  )

  useEffect(() => {
    if (step === "profile") {
      const fetchMetadata = async () => {
        try {
          const baseUrl = process.env.NEXT_PUBLIC_API_URL
          const [cRes, bRes, sRes] = await Promise.all([
            fetch(`${baseUrl}/api/v1/portal/auth/metadata/countries`),
            fetch(`${baseUrl}/api/v1/portal/auth/metadata/business-types`),
            fetch(`${baseUrl}/api/v1/portal/auth/metadata/supplier-categories`)
          ])

          if (!cRes.ok || !bRes.ok || !sRes.ok) throw new Error("Metadata sync failed")

          const [cData, bData, sData] = await Promise.all([cRes.json(), bRes.json(), sRes.json()])
          setCountries(cData.data || [])
          setBusinessTypes(bData.data || [])
          setSupplierCategories(sData.data || [])
        } catch (err) {
          setError("root", { message: "Connectivity issue: Unable to load regional settings." })
        }
      }
      fetchMetadata()
    }
  }, [step, setError])

  const onRegisterAccount = async () => {
    const isValid = await triggerFields(['firstName', 'lastName', 'email', 'phone', 'password', 'confirmPassword'])
    if (!isValid) return

    clearErrors("root")
    const values = getValues()

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/register`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify({
          FirstName: values.firstName,
          LastName: values.lastName,
          Email: values.email,
          Phone: values.phone,
          Password: values.password,
          Password_confirmation: values.confirmPassword
        }),
      })

      const result = await res.json()
      if (res.status === 422) {
        handleApiErrors(result.errors, setError)
        return
      }
      if (!res.ok) throw new Error(result.message || "Credential verification failed")

      setAuthToken(result.token)
      setStep("profile")
    } catch (err: any) {
      setError("root", { message: err.message })
    }
  }

  const onCompleteProfile = async () => {
    const isValid = await triggerFields(['thirdPartyName', 'registrationNumber', 'taxPIN', 'businessType', 'countryId', 'physicalAddress'])
    if (!isValid) return

    if (selectedCats.length === 0) {
      setError("root", { message: "Selection Required: Industry service categories must be defined." })
      return
    }

    clearErrors("root")
    const values = getValues()

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/complete-profile`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "Authorization": `Bearer ${authToken}`
        },
        body: JSON.stringify({
          ThirdPartyName: values.thirdPartyName,
          TradingName: values.thirdPartyName,
          RegistrationNumber: values.registrationNumber.toUpperCase(),
          TaxPIN: values.taxPIN.toUpperCase(),
          BusinessType: Number(values.businessType),
          CountryId: Number(values.countryId),
          PhysicalAddress: values.physicalAddress,
          Website: values.website || null,
          accountType: accountType,
          supplierCategories: selectedCats
        }),
      })

      const result = await res.json()
      if (res.status === 422) {
        handleApiErrors(result.errors, setError)
        return
      }
      if (!res.ok) throw new Error(result.message || "Organization profiling failed")

      setStep("success")
    } catch (err: any) {
      setError("root", { message: err.message })
    }
  }

  const handleFormSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (step === "form") {
      await onRegisterAccount()
    } else {
      await onCompleteProfile()
    }
  }

  return (
    <Suspense fallback={<div className="h-screen w-full flex items-center justify-center bg-background"><Spinner /></div>}>
      <div className="min-h-screen w-full flex flex-col items-center justify-center p-6 font-sans">
        <div className="w-full max-w-[580px] space-y-8">
          <AnimatePresence mode="wait">
            {step !== "success" ? (
              <motion.div
                key={step}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                transition={{ duration: 0.3 }}
              >
                <div className="text-center space-y-6 mb-8">
                  <div>
                    <h2 className="text-2xl font-bold tracking-tight text-foreground">
                      Craft Silicon
                    </h2>
                  </div>

                  <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight text-foreground">
                      {step === "form" ? "Create your account" : "Business Information"}
                    </h1>
                    <p className="text-sm text-muted-foreground max-w-md mx-auto">
                      {step === "form"
                        ? "Enter your personal details to begin sign up process."
                        : "Please provide your business information."}
                    </p>
                  </div>
                </div>

                {errors?.root && (
                  <div className="mb-6 p-3.5 bg-destructive/10 border border-destructive/20 rounded-lg flex items-center gap-3">
                    <AlertCircle className="h-4 w-4 text-destructive shrink-0" />
                    <p className="text-xs font-medium text-destructive">{errors.root.message}</p>
                    <button type="button" onClick={() => clearErrors("root")} className="ml-auto">
                      <X className="h-4 w-4 text-destructive/50 hover:text-destructive" />
                    </button>
                  </div>
                )}

                <form onSubmit={handleFormSubmit} className="space-y-5">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {step === "form" ? (
                      <>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">First Name</FieldLabel>
                          <Input placeholder="John" className="h-11 rounded-lg border-input/60 focus:border-primary transition-colors" {...register("firstName")} />
                          {errors?.firstName && <FieldError className="text-xs text-destructive mt-1">{errors.firstName.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Last Name</FieldLabel>
                          <Input placeholder="Doe" className="h-11 rounded-lg border-input/60 focus:border-primary transition-colors" {...register("lastName")} />
                          {errors?.lastName && <FieldError className="text-xs text-destructive mt-1">{errors.lastName.message}</FieldError>}
                        </Field>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Email Address</FieldLabel>
                          <div className="relative">
                            <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input type="email" placeholder="john.doe@company.com" className="h-11 pl-10 rounded-lg border-input/60 focus:border-primary transition-colors" {...register("email")} />
                          </div>
                          {errors?.email && <FieldError className="text-xs text-destructive mt-1">{errors.email.message}</FieldError>}
                        </Field>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Phone Number</FieldLabel>
                          <Input placeholder="+254 700..." className="h-11 rounded-lg border-input/60 focus:border-primary transition-colors" {...register("phone")} />
                          {errors?.phone && <FieldError className="text-xs text-destructive mt-1">{errors.phone.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Password</FieldLabel>
                          <div className="relative">
                            <Input type={pwdShown ? "text" : "password"} className="h-11 rounded-lg border-input/60 focus:border-primary transition-colors pr-10" {...register("password")} />
                            <button type="button" onClick={togglePwd} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors">
                              {pwdShown ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                            </button>
                          </div>
                          {errors?.password && <FieldError className="text-xs text-destructive mt-1">{errors.password.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Confirm Password</FieldLabel>
                          <div className="relative">
                            <Input type={confirmShown ? "text" : "password"} className="h-11 rounded-lg border-input/60 focus:border-primary transition-colors pr-10" {...register("confirmPassword")} />
                            <button type="button" onClick={toggleConfirm} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors">
                              {confirmShown ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                            </button>
                          </div>
                          {errors?.confirmPassword && <FieldError className="text-xs text-destructive mt-1">{errors.confirmPassword.message}</FieldError>}
                        </Field>
                      </>
                    ) : (
                      <>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Legal Business Name</FieldLabel>
                          <div className="relative">
                            <Building2 className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input placeholder="Global Solutions Ltd" className="h-11 pl-10 rounded-lg border-input/60 focus:border-primary transition-colors" {...register("thirdPartyName")} />
                          </div>
                          {errors?.thirdPartyName && <FieldError className="text-xs text-destructive mt-1">{errors.thirdPartyName.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Business Type</FieldLabel>
                          <Select onValueChange={(v) => setValue("businessType", Number(v), { shouldValidate: true })} value={selectedBusinessTypeId?.toString()}>
                            <SelectTrigger className="h-11 rounded-lg border-input/60">
                              <SelectValue placeholder="Select category" />
                            </SelectTrigger>
                            <SelectContent className="rounded-lg">
                              {businessTypes.map((t) => (
                                <SelectItem key={t.id} value={t.id.toString()}>{t.name}</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                          {errors?.businessType && <FieldError className="text-xs text-destructive mt-1">Required</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Registration Number</FieldLabel>
                          <div className="relative">
                            <Hash className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input placeholder="PVT-X..." className="h-11 pl-10 rounded-lg border-input/60 focus:border-primary transition-colors uppercase" {...register("registrationNumber")} />
                          </div>
                          {errors?.registrationNumber && <FieldError className="text-xs text-destructive mt-1">{errors.registrationNumber.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Tax PIN</FieldLabel>
                          <Input placeholder="P051..." className="h-11 rounded-lg border-input/60 focus:border-primary transition-colors uppercase" {...register("taxPIN")} />
                          {errors?.taxPIN && <FieldError className="text-xs text-destructive mt-1">{errors.taxPIN.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Primary Jurisdiction</FieldLabel>
                          <Select onValueChange={(v) => setValue("countryId", Number(v), { shouldValidate: true })} value={selectedCountryId?.toString()}>
                            <SelectTrigger className="h-11 rounded-lg border-input/60">
                              <SelectValue placeholder="Select country" />
                            </SelectTrigger>
                            <SelectContent className="rounded-lg">
                              {countries.map((c) => (
                                <SelectItem key={c.id} value={c.id.toString()}>
                                  <span className="mr-2">{c.flag}</span> {c.name}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                          {errors?.countryId && <FieldError className="text-xs text-destructive mt-1">Required</FieldError>}
                        </Field>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Industry Service Categories</FieldLabel>
                          <div className="flex flex-wrap gap-2 mb-2.5 min-h-[40px] p-2.5 rounded-lg bg-muted/20 border border-dashed border-input/60">
                            <AnimatePresence>
                              {selectedCats.length === 0 && <span className="text-xs text-muted-foreground/50 my-auto italic">No categories selected</span>}
                              {selectedCats.map(catId => {
                                const cat = supplierCategories.find(c => c.id === catId);
                                return (
                                  <motion.div
                                    key={catId}
                                    initial={{ scale: 0.8, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    exit={{ scale: 0.8, opacity: 0 }}
                                    className="bg-primary text-primary-foreground px-2.5 py-1 rounded-md flex items-center gap-1.5 text-xs font-medium"
                                  >
                                    <span>{cat?.name}</span>
                                    <button type="button" onClick={() => setSelectedCats(prev => prev.filter(id => id !== catId))} className="hover:bg-white/20 rounded-full p-0.5">
                                      <X className="h-3 w-3" />
                                    </button>
                                  </motion.div>
                                );
                              })}
                            </AnimatePresence>
                          </div>
                          <Select onValueChange={(v) => {
                            const id = Number(v);
                            if (!selectedCats.includes(id)) { setSelectedCats(prev => [...prev, id]); setCatSearch(""); }
                          }}>
                            <SelectTrigger className="h-11 rounded-lg border-input/60">
                              <SelectValue placeholder="Select categories" />
                            </SelectTrigger>
                            <SelectContent className="rounded-lg max-h-64">
                              <div className="p-2 sticky top-0 bg-popover border-b z-10">
                                <div className="relative">
                                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                  <Input placeholder="Filter..." className="h-9 pl-9 text-xs rounded-md" value={catSearch} onChange={(e) => setCatSearch(e.target.value)} onKeyDown={(e) => e.stopPropagation()} />
                                </div>
                              </div>
                              {filteredCats.map((cat) => (
                                <SelectItem key={cat.id} value={cat.id.toString()}>{cat.name}</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </Field>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Physical Address</FieldLabel>
                          <div className="relative">
                            <MapPin className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input placeholder="Plaza, Suite 12" className="h-11 pl-10 rounded-lg border-input/60 focus:border-primary transition-colors" {...register("physicalAddress")} />
                          </div>
                          {errors?.physicalAddress && <FieldError className="text-xs text-destructive mt-1">{errors.physicalAddress.message}</FieldError>}
                        </Field>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-sm font-medium text-foreground mb-1.5">Website (Optional)</FieldLabel>
                          <div className="relative">
                            <Globe className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input placeholder="https://www.example.com" className="h-11 pl-10 rounded-lg border-input/60 focus:border-primary transition-colors" {...register("website")} />
                          </div>
                          {errors?.website && <FieldError className="text-xs text-destructive mt-1">{errors.website.message}</FieldError>}
                        </Field>
                      </>
                    )}
                  </div>

                  <div className="pt-4 space-y-3.5">
                    <Button
                      type="submit"
                      disabled={isSubmitting}
                      className="w-full h-11 rounded-lg font-semibold text-sm transition-all active:scale-[0.98] bg-primary hover:bg-primary/90 text-primary-foreground"
                    >
                      {isSubmitting ? (
                        <div className="flex items-center gap-2">
                          <Loader2 className="h-4 w-4 animate-spin" />
                          <span>Processing...</span>
                        </div>
                      ) : (
                        <div className="flex items-center justify-center gap-1.5">
                          <span>{step === "form" ? "Continue" : "Complete Registration"}</span>
                          <ChevronRight className="h-4 w-4" />
                        </div>
                      )}
                    </Button>

                    {step === "profile" && (
                      <button type="button" onClick={() => setStep("form")} className="w-full text-center text-sm font-medium text-muted-foreground hover:text-primary transition-colors">
                        Go back to personal details
                      </button>
                    )}
                  </div>
                </form>

                <div className="text-center pt-6 border-t border-border/30 mt-6">
                  <p className="text-sm text-muted-foreground">
                    Already have an account?{" "}
                    <Link href="/signin" className="text-primary font-semibold hover:underline underline-offset-2">
                      Log In
                    </Link>
                  </p>
                </div>
              </motion.div>
            ) : (
              <SuccessState router={router} />
            )}
          </AnimatePresence>
        </div>
      </div>
    </Suspense>
  )
}

function SuccessState({ router }: { router: any }) {
  useEffect(() => {
    const timer = setTimeout(() => { router.push("/signin") }, 4000)
    return () => clearTimeout(timer)
  }, [router])

  return (
    <motion.div initial={{ scale: 0.95, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} className="text-center space-y-8">
      <div className="h-20 w-20 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto shadow-sm">
        <Check className="h-10 w-10 stroke-[3]" />
      </div>
      <div className="space-y-3">
        <h2 className="text-3xl font-bold text-foreground tracking-tight">Registration Complete</h2>
        <p className="text-muted-foreground font-medium max-w-xs mx-auto">Your account is being initialized. Redirecting to login...</p>
      </div>
      <div className="flex items-center justify-center gap-3 text-primary font-bold text-sm">
        <Spinner className="h-4 w-4" /> Finalizing session
      </div>
    </motion.div>
  )
}