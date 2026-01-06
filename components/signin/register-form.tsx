"use client"

import * as React from "react"
import { useForm, SubmitHandler } from "react-hook-form"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"
import { AlertCircle, CheckCircle2, ChevronDown } from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Spinner } from "@/components/common/spinner"
import { useRouter } from "next/navigation"

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

const registerSchema = z.object({
  Name: z.string().min(2),
  TradingName: z.string().optional(),
  BusinessType: z.string().min(1),
  RegistrationNumber: z.string().min(2),
  TaxPIN: z.string().min(2),
  VATNumber: z.string().optional(),
  Country: z.string().min(1),
  Location: z.coerce.number().min(1),
  Email: z.string().email(),
  Phone: z.string().min(10),
  PhysicalAddress: z.string().optional(),
  Website: z.string().url().optional().or(z.literal("")),
  types: z.array(z.string()).min(1),
  supplier_category_id: z.coerce.number().optional(),
  createUser: z.boolean(),
  user_FirstName: z.string().min(2),
  user_LastName: z.string().min(2),
  user_Email: z.string().email(),
  user_Phone: z.string().min(10),
  user_Gender: z.string().min(1),
  user_Password: z.string().min(8),
  user_Password_confirmation: z.string()
}).refine((data) => data.user_Password === data.user_Password_confirmation, {
  message: "Passwords must match",
  path: ["user_Password_confirmation"],
})

type FormValues = z.infer<typeof registerSchema>

export default function RegisterForm() {
  const router = useRouter()
  const [step, setStep] = React.useState(1)
  const [authError, setAuthError] = React.useState<string | null>(null)
  const [success, setSuccess] = React.useState(false)
  const [metadata, setMetadata] = React.useState({
    countries: [] as any[],
    businessTypes: [] as any[],
    supplierCategories: [] as any[],
    localities: [] as any[],
    genders: [] as any[]
  })

  const {
    register,
    handleSubmit,
    watch,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      types: ["Supplier"],
      createUser: true,
      Country: "",
      BusinessType: "",
      user_Gender: ""
    }
  })

  const selectedCountryCode = watch("Country")
  const isSupplier = watch("types")?.includes("Supplier")

  const fetchData = React.useCallback(async () => {
    try {
      const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
      const [countriesRes, categoriesRes, lookupsRes] = await Promise.all([
        fetch(`${baseUrl}/api/v1/portal/auth/metadata/countries`),
        fetch(`${baseUrl}/api/v1/portal/auth/metadata/supplier-categories`),
        fetch(`${baseUrl}/api/v1/portal/auth/lookups/bulk?codes=gender,businessType`)
      ])
      const countries = await countriesRes.json()
      const categories = await categoriesRes.json()
      const lookups = await lookupsRes.json()

      setMetadata(prev => ({
        ...prev,
        countries: countries.data || countries,
        supplierCategories: categories.data || categories,
        businessTypes: lookups.data?.businessType || [],
        genders: lookups.data?.gender || []
      }))
    } catch (err) {
      console.error(err)
    }
  }, [])

  React.useEffect(() => { fetchData() }, [fetchData])

  React.useEffect(() => {
    if (!selectedCountryCode) return
    const fetchLocalities = async () => {
      const country = metadata.countries.find(c => c.code === selectedCountryCode)
      if (!country?.id) return
      try {
        const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
        const res = await fetch(`${baseUrl}/api/v1/portal/auth/metadata/localities/${country.id}`)
        const result = await res.json()
        setMetadata(prev => ({ ...prev, localities: result.data || result || [] }))
      } catch (err) { console.error(err) }
    }
    fetchLocalities()
  }, [selectedCountryCode, metadata.countries])

  const onSubmit: SubmitHandler<FormValues> = async (data) => {
    setAuthError(null)
    try {
      const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
      const response = await fetch(`${baseUrl}/api/v1/portal/auth/register`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify(data)
      })
      const result = await response.json()
      if (!response.ok) throw new Error(result.message || "Registration failed")
      setSuccess(true)
    } catch (err: any) {
      setAuthError(err.message)
    }
  }

  const fieldBase = "h-12 w-full border border-slate-200 bg-transparent px-4 text-sm outline-none focus:border-slate-900 transition-colors"
  const labelBase = "text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5 block"

  if (success) return (
    <div className="text-center space-y-6">
      <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-50">
        <CheckCircle2 className="h-8 w-8 text-slate-900" />
      </div>
      <h2 className="text-xl font-bold text-slate-900">Account Created</h2>
      <p className="text-slate-500 text-sm">Check your email to activate Shenzi Logistics.</p>
      <Button onClick={() => router.push("/signin")} className="w-full h-12 bg-slate-900 text-white rounded-none">Sign In</Button>
    </div>
  )

  return (
    <div className="space-y-10">
      <div className="flex items-center justify-center gap-4" key="stepper">
        <div className={cn("flex items-center gap-2", step === 1 ? "text-slate-900" : "text-slate-300")}>
          <div className={cn("h-6 w-6 rounded-full flex items-center justify-center text-[10px] font-bold border", step >= 1 ? "bg-slate-900 border-slate-900 text-white" : "border-slate-200")}>1</div>
          <span className="text-[11px] font-bold uppercase tracking-wider">Organization</span>
        </div>
        <div className="w-8 h-[1px] bg-slate-100" />
        <div className={cn("flex items-center gap-2", step === 2 ? "text-slate-900" : "text-slate-300")}>
          <div className={cn("h-6 w-6 rounded-full flex items-center justify-center text-[10px] font-bold border", step === 2 ? "bg-slate-900 border-slate-900 text-white" : "border-slate-200")}>2</div>
          <span className="text-[11px] font-bold uppercase tracking-wider">Administrator</span>
        </div>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-8" key="register-form">
        {authError && <div className="p-4 bg-red-50 text-red-600 text-xs border border-red-100 flex items-center gap-2"><AlertCircle className="h-4 w-4" /> {authError}</div>}

        {step === 1 ? (
          <div className="space-y-6" key="step-1">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="sm:col-span-2">
                <label className={labelBase}>Company Name</label>
                <Input {...register("Name")} placeholder="Shenzi Logistics Ltd" className={fieldBase} />
              </div>
              <div className="sm:col-span-2">
                <label className={labelBase}>Contact Details</label>
                <div className="grid grid-cols-2 gap-2">
                  <Input {...register("Email")} placeholder="Company Email" className={fieldBase} />
                  <Input {...register("Phone")} placeholder="Company Phone" className={fieldBase} />
                </div>
              </div>
              <div className="sm:col-span-2">
                <label className={labelBase}>Registration & Tax</label>
                <div className="grid grid-cols-2 gap-2">
                  <Input {...register("RegistrationNumber")} placeholder="Reg No" className={fieldBase} />
                  <Input {...register("TaxPIN")} placeholder="Tax PIN" className={fieldBase} />
                </div>
              </div>
              <div>
                <label className={labelBase}>Country</label>
                <select {...register("Country")} className={fieldBase}>
                  <option value="">Select...</option>
                  {metadata.countries.map((c: any) => <option key={c.id} value={c.code}>{c.name}</option>)}
                </select>
              </div>
              <div>
                <label className={labelBase}>Location</label>
                <select {...register("Location")} className={fieldBase} disabled={!selectedCountryCode}>
                  <option value="">Select City...</option>
                  {metadata.localities.map((l: any) => <option key={l.id} value={l.id}>{l.name}</option>)}
                </select>
              </div>
              {isSupplier && (
                <div className="sm:col-span-2">
                  <label className={labelBase}>Supplier Category</label>
                  <select {...register("supplier_category_id")} className={fieldBase}>
                    <option value="">Select...</option>
                    {metadata.supplierCategories.map((s: any) => <option key={s.id} value={s.id}>{s.name}</option>)}
                  </select>
                </div>
              )}
            </div>
            <Button type="button" onClick={() => setStep(2)} className="w-full h-12 bg-slate-900 text-white rounded-none font-bold">Next Step</Button>
          </div>
        ) : (
          <div className="space-y-6" key="step-2">
            <div className="grid grid-cols-2 gap-4">
              <div className="col-span-2 grid grid-cols-2 gap-2">
                <div><label className={labelBase}>First Name</label><Input {...register("user_FirstName")} className={fieldBase} /></div>
                <div><label className={labelBase}>Last Name</label><Input {...register("user_LastName")} className={fieldBase} /></div>
              </div>
              <div className="col-span-2">
                <label className={labelBase}>Admin Email</label>
                <Input {...register("user_Email")} className={fieldBase} />
              </div>
              <div className="col-span-2">
                <label className={labelBase}>Admin Phone</label>
                <Input {...register("user_Phone")} placeholder="07XXXXXXXX" className={fieldBase} />
              </div>
              <div className="col-span-2">
                <label className={labelBase}>Gender</label>
                <select {...register("user_Gender")} className={fieldBase}>
                  <option value="">Select...</option>
                  {metadata.genders.map((g: any) => <option key={g.value} value={g.value}>{g.description}</option>)}
                </select>
              </div>
              <div><label className={labelBase}>Password</label><Input type="password" {...register("user_Password")} className={fieldBase} /></div>
              <div><label className={labelBase}>Confirm</label><Input type="password" {...register("user_Password_confirmation")} className={fieldBase} /></div>
            </div>
            <div className="flex gap-2">
              <Button type="button" onClick={() => setStep(1)} variant="outline" className="flex-1 h-12 border-slate-200 rounded-none">Back</Button>
              <Button type="submit" disabled={isSubmitting} className="flex-[2] h-12 bg-slate-900 text-white rounded-none font-bold">
                {isSubmitting ? <Spinner className="h-4 w-4 animate-spin" /> : "Complete Registration"}
              </Button>
            </div>
          </div>
        )}
      </form>
    </div>
  )
}