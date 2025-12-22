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
  Search
} from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Field, FieldLabel, FieldError } from "@/components/common/field"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn, handleApiErrors } from "@/lib/utils"
import { Spinner } from "../common/spinner"
import { useAuthStore } from "@/store/auth-store"
import { useRegisterForm } from "@/hooks/use-register"

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
    <Suspense fallback={<div className="h-screen w-full flex items-center justify-center bg-slate-50"><Spinner /></div>}>
      <div className="flex min-h-screen w-full bg-[#FDFDFD]">
        <aside className="hidden lg:flex w-[440px] bg-slate-950 flex-col justify-between p-12 fixed inset-y-0 left-0 overflow-hidden">
          <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-[0.03]" />
          <div className="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-blue-600/10 blur-[100px]" />

          <div className="relative z-10 space-y-12">
            <div className="flex items-center gap-4">
              <div className="h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center shadow-2xl shadow-blue-500/20">
                <ShieldCheck className="text-white h-7 w-7" />
              </div>
              <div className="flex flex-col">
                <span className="text-xl font-black tracking-tight text-white uppercase">Craft Silicon</span>
                <span className="text-[9px] font-black uppercase tracking-[0.3em] text-blue-500">Partner Enrollment</span>
              </div>
            </div>

            <div className="space-y-4">
              <h2 className="text-4xl font-black text-white leading-tight">Join the <span className="text-blue-500">Ecosystem.</span></h2>
              <p className="text-slate-400 font-medium leading-relaxed">Streamline your enterprise collaboration with our unified provider network.</p>
            </div>

            <nav className="space-y-8 pt-12 border-t border-white/5">
              {[
                { title: "Authentication", subtitle: "Personal Identity", status: step === "form" ? "Active" : "Done" },
                { title: "Organization", subtitle: "Business Entity Details", status: step === "profile" ? "Active" : step === "success" ? "Done" : "Pending" }
              ].map((item, idx) => (
                <div key={idx} className="flex items-center gap-5">
                  <div className={cn(
                    "h-12 w-12 rounded-2xl border-2 flex items-center justify-center font-black transition-all duration-500",
                    item.status === "Active" ? "bg-blue-600 border-blue-600 text-white shadow-xl shadow-blue-500/30" :
                      item.status === "Done" ? "bg-emerald-500/10 border-emerald-500/20 text-emerald-500" :
                        "border-white/5 text-slate-700"
                  )}>
                    {item.status === "Done" ? <Check className="h-6 w-6 stroke-[3]" /> : idx + 1}
                  </div>
                  <div className="flex flex-col">
                    <p className={cn("text-sm font-black uppercase tracking-widest", item.status === "Active" ? "text-white" : "text-slate-600")}>{item.title}</p>
                    <p className="text-[11px] font-bold text-slate-500">{item.subtitle}</p>
                  </div>
                </div>
              ))}
            </nav>
          </div>

          <div className="relative z-10">
            <p className="text-slate-700 text-[10px] font-black tracking-[0.4em] uppercase">Enterprise Standard v2.5</p>
          </div>
        </aside>

        <main className="flex-1 lg:ml-[440px] flex items-center justify-center p-6 lg:p-20">
          <AnimatePresence mode="wait">
            {step !== "success" ? (
              <motion.div
                key={step}
                initial={{ opacity: 0, scale: 0.98 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.98 }}
                transition={{ duration: 0.4 }}
                className="w-full max-w-2xl"
              >
                <header className="mb-10">
                  {step === "profile" && (
                    <button type="button" onClick={() => setStep("form")} className="flex items-center gap-2 text-slate-400 hover:text-blue-600 transition-all text-[10px] font-black mb-8 uppercase tracking-widest group">
                      <ArrowLeft className="h-4 w-4 transition-transform group-hover:-translate-x-1" /> Return to Identity
                    </button>
                  )}
                  <div className="flex items-center gap-3 mb-2">
                    <div className="h-1 w-8 bg-blue-600 rounded-full" />
                    <span className="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600">Step {step === "form" ? "01" : "02"}</span>
                  </div>
                  <h1 className="text-4xl font-black text-slate-900 tracking-tight">
                    {step === "form" ? "Personal Credentials" : "Business Information"}
                  </h1>
                </header>

                {errors?.root && (
                  <div className="mb-8 bg-rose-50 border border-rose-100 p-5 rounded-3xl flex gap-4 items-center">
                    <div className="h-10 w-10 rounded-2xl bg-rose-500 flex items-center justify-center shrink-0 shadow-lg shadow-rose-200">
                      <AlertCircle className="h-5 w-5 text-white" />
                    </div>
                    <p className="flex-1 text-sm font-bold text-rose-900">{errors.root.message}</p>
                    <button type="button" onClick={() => clearErrors("root")} className="p-2 hover:bg-rose-100 rounded-xl transition-colors">
                      <X className="h-4 w-4 text-rose-400" />
                    </button>
                  </div>
                )}

                <form onSubmit={handleFormSubmit} className="space-y-6">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-1 bg-white">
                    {step === "form" ? (
                      <>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">First Name</FieldLabel>
                          <Input placeholder="John" className="h-14 bg-slate-50/50 border-slate-100 focus:bg-white focus:ring-4 focus:ring-blue-600/5 rounded-2xl transition-all" {...register("firstName")} />
                          {errors?.firstName && <FieldError className="font-bold text-rose-600 ml-1">{errors.firstName.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Last Name</FieldLabel>
                          <Input placeholder="Doe" className="h-14 bg-slate-50/50 border-slate-100 focus:bg-white focus:ring-4 focus:ring-blue-600/5 rounded-2xl transition-all" {...register("lastName")} />
                          {errors?.lastName && <FieldError className="font-bold text-rose-600 ml-1">{errors.lastName.message}</FieldError>}
                        </Field>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Email Address</FieldLabel>
                          <div className="relative group">
                            <Mail className="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                            <Input type="email" placeholder="john.doe@company.com" className="h-14 pl-14 bg-slate-50/50 border-slate-100 focus:bg-white focus:ring-4 focus:ring-blue-600/5 rounded-2xl transition-all" {...register("email")} />
                          </div>
                          {errors?.email && <FieldError className="font-bold text-rose-600 ml-1">{errors.email.message}</FieldError>}
                        </Field>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Phone Number</FieldLabel>
                          <Input placeholder="+254 700..." className="h-14 bg-slate-50/50 border-slate-100 focus:bg-white focus:ring-4 focus:ring-blue-600/5 rounded-2xl transition-all" {...register("phone")} />
                          {errors?.phone && <FieldError className="font-bold text-rose-600 ml-1">{errors.phone.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Password</FieldLabel>
                          <div className="relative group">
                            <Input type={pwdShown ? "text" : "password"} className="h-14 bg-slate-50/50 border-slate-100 focus:bg-white focus:ring-4 focus:ring-blue-600/5 rounded-2xl transition-all" {...register("password")} />
                            <button type="button" onClick={togglePwd} className="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors">
                              {pwdShown ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                            </button>
                          </div>
                          {errors?.password && <FieldError className="font-bold text-rose-600 ml-1">{errors.password.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Confirm Password</FieldLabel>
                          <div className="relative group">
                            <Input type={confirmShown ? "text" : "password"} className="h-14 bg-slate-50/50 border-slate-100 focus:bg-white focus:ring-4 focus:ring-blue-600/5 rounded-2xl transition-all" {...register("confirmPassword")} />
                            <button type="button" onClick={toggleConfirm} className="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors">
                              {confirmShown ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                            </button>
                          </div>
                          {errors?.confirmPassword && <FieldError className="font-bold text-rose-600 ml-1">{errors.confirmPassword.message}</FieldError>}
                        </Field>
                      </>
                    ) : (
                      <>
                        <Field className="md:col-span-2">
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Legal Entity Name</FieldLabel>
                          <div className="relative group">
                            <Building2 className="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors" />
                            <Input placeholder="Global Solutions Ltd" className="h-14 pl-14 bg-slate-50/50 border-slate-100 focus:ring-4 focus:ring-blue-600/5 rounded-2xl transition-all" {...register("thirdPartyName")} />
                          </div>
                          {errors?.thirdPartyName && <FieldError className="font-bold text-rose-600 ml-1">{errors.thirdPartyName.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Business Type</FieldLabel>
                          <Select onValueChange={(v) => setValue("businessType", Number(v), { shouldValidate: true })} value={selectedBusinessTypeId?.toString()}>
                            <SelectTrigger className="h-14 bg-slate-50/50 border-slate-100 rounded-2xl">
                              <SelectValue placeholder="Category" />
                            </SelectTrigger>
                            <SelectContent className="rounded-2xl border-slate-100 shadow-2xl">
                              {businessTypes.map((t) => (
                                <SelectItem key={t.id} value={t.id.toString()}>{t.name}</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                          {errors?.businessType && <FieldError className="font-bold text-rose-600 ml-1">Required</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Reg Number</FieldLabel>
                          <div className="relative group">
                            <Hash className="absolute left-5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors" />
                            <Input placeholder="PVT-X..." className="h-14 pl-14 bg-slate-50/50 border-slate-100 rounded-2xl uppercase font-bold" {...register("registrationNumber")} />
                          </div>
                          {errors?.registrationNumber && <FieldError className="font-bold text-rose-600 ml-1">{errors.registrationNumber.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Tax PIN</FieldLabel>
                          <Input placeholder="P051..." className="h-14 bg-slate-50/50 border-slate-100 rounded-2xl uppercase font-bold" {...register("taxPIN")} />
                          {errors?.taxPIN && <FieldError className="font-bold text-rose-600 ml-1">{errors.taxPIN.message}</FieldError>}
                        </Field>
                        <Field>
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Primary Jurisdiction</FieldLabel>
                          <Select onValueChange={(v) => setValue("countryId", Number(v), { shouldValidate: true })} value={selectedCountryId?.toString()}>
                            <SelectTrigger className="h-14 bg-slate-50/50 border-slate-100 rounded-2xl">
                              <SelectValue placeholder="Country" />
                            </SelectTrigger>
                            <SelectContent className="rounded-2xl border-slate-100 shadow-2xl">
                              {countries.map((c) => (
                                <SelectItem key={c.id} value={c.id.toString()}>
                                  <span className="mr-2">{c.flag}</span> {c.name}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                          {errors?.countryId && <FieldError className="font-bold text-rose-600 ml-1">Required</FieldError>}
                        </Field>

                        <Field className="md:col-span-2">
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-3 ml-1">
                            Industry Service Categories <span className="text-rose-500">*</span>
                          </FieldLabel>

                          <div className="flex flex-wrap gap-2 mb-3 min-h-[40px] p-2 rounded-2xl bg-slate-50/30 border border-dashed border-slate-100">
                            <AnimatePresence>
                              {selectedCats.length === 0 && <span className="text-[10px] text-slate-400 font-medium my-auto ml-1 italic">No categories selected</span>}
                              {selectedCats.map(catId => {
                                const cat = supplierCategories.find(c => c.id === catId);
                                return (
                                  <motion.div
                                    key={catId}
                                    initial={{ scale: 0.8, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    exit={{ scale: 0.8, opacity: 0 }}
                                    className="bg-blue-600 text-white px-3 py-1.5 rounded-xl flex items-center gap-2 shadow-sm group"
                                  >
                                    <span className="text-[10px] font-black uppercase tracking-wider">{cat?.name}</span>
                                    <button
                                      type="button"
                                      onClick={() => setSelectedCats(prev => prev.filter(id => id !== catId))}
                                      className="hover:bg-white/20 rounded-full p-0.5 transition-colors"
                                    >
                                      <X className="h-3 w-3" />
                                    </button>
                                  </motion.div>
                                );
                              })}
                            </AnimatePresence>
                          </div>

                          <Select onValueChange={(v) => {
                            const id = Number(v);
                            if (!selectedCats.includes(id)) {
                              setSelectedCats(prev => [...prev, id]);
                              setCatSearch("");
                            }
                          }}>
                            <SelectTrigger className="h-14 bg-slate-50/50 border-slate-100 rounded-2xl">
                              <SelectValue placeholder="Select Business Categories" />
                            </SelectTrigger>
                            <SelectContent className="rounded-2xl border-slate-100 shadow-2xl max-h-64">
                              <div className="p-2 sticky top-0 bg-white border-b z-10">
                                <div className="relative">
                                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                                  <Input
                                    placeholder="Filter categories..."
                                    className="h-10 pl-9 text-xs border-slate-100 bg-slate-50 rounded-xl"
                                    value={catSearch}
                                    onChange={(e) => setCatSearch(e.target.value)}
                                    onKeyDown={(e) => e.stopPropagation()}
                                  />
                                </div>
                              </div>
                              <div className="overflow-y-auto">
                                {filteredCats.map((cat) => (
                                  <SelectItem key={cat.id} value={cat.id.toString()} className="rounded-lg m-1">
                                    <span className="text-xs font-bold text-slate-700">{cat.name}</span>
                                  </SelectItem>
                                ))}
                                {filteredCats.length === 0 && (
                                  <div className="p-6 text-center text-xs text-slate-400 font-medium">
                                    All matching categories selected
                                  </div>
                                )}
                              </div>
                            </SelectContent>
                          </Select>
                        </Field>

                        <Field className="md:col-span-2">
                          <FieldLabel className="text-slate-500 font-black text-[10px] uppercase tracking-[0.15em] mb-2 ml-1">Physical HQ Address</FieldLabel>
                          <div className="relative group">
                            <MapPin className="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-blue-600 transition-colors" />
                            <Input placeholder="Plaza, 4th Floor, Suite 12" className="h-14 pl-14 bg-slate-50/50 border-slate-100 rounded-2xl transition-all" {...register("physicalAddress")} />
                          </div>
                          {errors?.physicalAddress && <FieldError className="font-bold text-rose-600 ml-1">{errors.physicalAddress.message}</FieldError>}
                        </Field>
                      </>
                    )}
                  </div>

                  <div className="pt-6">
                    <Button
                      type="submit"
                      disabled={isSubmitting}
                      className="w-full h-16 bg-slate-900 hover:bg-blue-600 text-white font-black text-[13px] uppercase tracking-[0.2em] rounded-2xl shadow-2xl shadow-slate-200 transition-all duration-300 active:scale-[0.98] group"
                    >
                      {isSubmitting ? (
                        <div className="flex items-center gap-3">
                          <Loader2 className="h-5 w-5 animate-spin" />
                          <span>Syncing Profile...</span>
                        </div>
                      ) : (
                        <div className="flex items-center justify-center gap-3">
                          <span>{step === "form" ? "Business Details" : "Initialize Account"}</span>
                          <ChevronRight className="h-5 w-5 group-hover:translate-x-1 transition-transform" />
                        </div>
                      )}
                    </Button>
                  </div>
                </form>
              </motion.div>
            ) : (
              <SuccessState router={router} />
            )}
          </AnimatePresence>
        </main>
      </div>
    </Suspense>
  )
}

function SuccessState({ router }: { router: any }) {
  useEffect(() => {
    const timer = setTimeout(() => {
      router.push("/signin")
    }, 4000)
    return () => clearTimeout(timer)
  }, [router])

  return (
    <motion.div
      initial={{ scale: 0.95, opacity: 0 }}
      animate={{ scale: 1, opacity: 1 }}
      className="text-center max-w-lg bg-white p-16 lg:p-20 rounded-[3rem] shadow-2xl border border-slate-50"
    >
      <div className="mb-10 h-24 w-24 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
        <Check className="h-12 w-12 stroke-[3]" />
      </div>
      <h2 className="text-3xl font-black text-slate-900 mb-4 tracking-tight">Onboarding Complete.</h2>
      <p className="text-slate-500 font-medium leading-relaxed mb-10">Verification initialized. Your partner console is being prepared.</p>
      <div className="flex items-center justify-center gap-4 text-blue-600 font-black text-[11px] uppercase tracking-[0.3em]">
        <Spinner className="h-4 w-4" /> Finalizing Session
      </div>
    </motion.div>
  )
}