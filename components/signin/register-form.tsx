"use client"

import * as React from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"
import {
  UserPlus, Loader2, AlertCircle,
  CheckCircle2, ChevronDown
} from "lucide-react"

import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

const registerSchema = z.object({
  Name: z.string().min(2, "Company name is required"),
  TradingName: z.string().optional(),
  BusinessType: z.string().min(1, "Business type is required"),
  RegistrationNumber: z.string().min(2, "Registration number is required"),
  TaxPIN: z.string().min(2, "Tax PIN is required"),
  VATNumber: z.string().optional(),
  Country: z.string().min(1, "Country is required"),
  Location: z.string().min(1, "Location is required"),
  Email: z.string().optional().or(z.literal("")).refine(val => !val || z.string().email().safeParse(val).success, "Invalid email address"),
  Phone: z.string().min(10, "Invalid phone number").refine(
    (val) => /^\+?\d{10,15}$/.test(val.replace(/\s/g, '')),
    "Phone must be in format +254700000000"
  ),
  PhysicalAddress: z.string().optional(),
  Website: z.string().optional().or(z.literal("")).refine(val => !val || z.string().url().safeParse(val).success, "Invalid URL"),
  types: z.array(z.string()).min(1, "Select at least one account type"),
  createUser: z.boolean(),
  user_FirstName: z.string().optional(),
  user_LastName: z.string().optional(),
  user_Email: z.string().optional().refine(val => !val || z.string().email().safeParse(val).success, "Invalid email address"),
  user_Phone: z.string().optional(),
  user_Gender: z.string().optional(),
  user_Password: z.string().optional(),
  user_Password_confirmation: z.string().optional(),
  supplier_category: z.string().optional(),
}).refine((data) => {
  if (data.createUser) {
    return !!data.user_FirstName && !!data.user_LastName && !!data.user_Email && !!data.user_Password && !!data.user_Password_confirmation
  }
  return true
}, {
  message: "Admin details are required",
  path: ["user_FirstName"],
}).refine((data) => {
  if (data.createUser && data.user_Password && data.user_Password_confirmation) {
    return data.user_Password === data.user_Password_confirmation
  }
  return true
}, {
  message: "Passwords must match",
  path: ["user_Password_confirmation"],
})

type FormValues = z.infer<typeof registerSchema>

export default function SignUpPage() {
  const router = useRouter()
  const [authError, setAuthError] = React.useState<string | null>(null)
  const [success, setSuccess] = React.useState(false)
  const [metadata, setMetadata] = React.useState({
    countries: [],
    businessTypes: [],
    supplierCategories: [],
    localities: []
  })

  const {
    register,
    handleSubmit,
    setValue,
    watch,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({
    resolver: zodResolver(registerSchema),
    mode: "onBlur",
    defaultValues: {
      Name: "",
      TradingName: "",
      BusinessType: "",
      RegistrationNumber: "",
      TaxPIN: "",
      VATNumber: "",
      Country: "",
      Location: "",
      Email: "",
      Phone: "",
      PhysicalAddress: "",
      Website: "",
      types: [],
      createUser: true,
      user_FirstName: "",
      user_LastName: "",
      user_Email: "",
      user_Phone: "",
      user_Gender: "",
      user_Password: "",
      user_Password_confirmation: "",
      supplier_category: ""
    }
  })

  const accountTypes = watch("types") || []
  const isSupplier = accountTypes.includes("Supplier")
  const createUser = watch("createUser")
  const selectedCountry = watch("Country")

  const fetchMetadata = React.useCallback(async () => {
    try {
      const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
      const endpoints = [
        { key: 'countries', url: `${baseUrl}/api/v1/portal/auth/metadata/countries` },
        { key: 'businessTypes', url: `${baseUrl}/api/v1/portal/auth/metadata/business-types` }
      ]
      if (isSupplier) endpoints.push({ key: 'supplierCategories', url: `${baseUrl}/api/v1/portal/auth/metadata/supplier-categories` })

      const results = await Promise.all(endpoints.map(e => fetch(e.url).then(res => res.json())))
      const newMetadata: any = {}
      endpoints.forEach((e, i) => {
        newMetadata[e.key] = results[i].data || []
      })
      setMetadata(prev => ({ ...prev, ...newMetadata }))
    } catch (err) {
      console.error(err)
    }
  }, [isSupplier])

  React.useEffect(() => { fetchMetadata() }, [fetchMetadata])

  React.useEffect(() => {
    const fetchLocalities = async () => {
      if (!selectedCountry) {
        setMetadata(prev => ({ ...prev, localities: [] }))
        setValue("Location", "")
        return
      }

      try {
        const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"
        const country = metadata.countries.find((c: any) => c.code === selectedCountry || c.name === selectedCountry)
        if (!country) return

        const response = await fetch(`${baseUrl}/api/v1/portal/auth/metadata/localities/${(country as any).id}`)
        if (!response.ok) throw new Error(`HTTP ${response.status}`)

        const result = await response.json()
        setMetadata(prev => ({ ...prev, localities: result.data || [] }))
      } catch (err) {
        setMetadata(prev => ({ ...prev, localities: [] }))
      }
    }

    fetchLocalities()
  }, [selectedCountry, metadata.countries, setValue])

  const onSubmit = async (data: FormValues) => {
    setAuthError(null)
    try {
      const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000"

      const formatPhone = (phone: string) => {
        if (!phone) return phone
        let cleaned = phone.replace(/[\s\-\(\)\.\+]/g, '')
        return '+' + cleaned
      }

      const payload: any = {
        Name: data.Name,
        TradingName: data.TradingName || data.Name,
        BusinessType: data.BusinessType,
        RegistrationNumber: data.RegistrationNumber,
        TaxPIN: data.TaxPIN,
        Country: data.Country,
        Location: data.Location,
        Phone: formatPhone(data.Phone),
        types: data.types,
        createUser: data.createUser
      }

      if (data.VATNumber) payload.VATNumber = data.VATNumber
      if (data.Email) payload.Email = data.Email
      if (data.PhysicalAddress) payload.PhysicalAddress = data.PhysicalAddress
      if (data.Website) payload.Website = data.Website

      if (data.createUser) {
        payload.user_FirstName = data.user_FirstName
        payload.user_LastName = data.user_LastName
        payload.user_Email = data.user_Email
        payload.user_Password = data.user_Password
        payload.user_Password_confirmation = data.user_Password_confirmation
        if (data.user_Phone) payload.user_Phone = formatPhone(data.user_Phone)
        if (data.user_Gender) payload.user_Gender = data.user_Gender
      }

      if (data.supplier_category && data.types.includes("Supplier")) {
        payload.supplier_category = data.supplier_category
      }

      const response = await fetch(`${baseUrl}/api/v1/portal/auth/register`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify(payload)
      })

      const result = await response.json()
      if (!response.ok) {
        // Handle validation errors from backend
        if (result.errors) {
          const errorMessages = Object.values(result.errors).flat() as string[]
          throw new Error(result.message || errorMessages.join(', '))
        }
        throw new Error(result.message || result.error || "Registration failed")
      }
      setSuccess(true)
    } catch (err: any) {
      setAuthError(err.message || "Registration failed. Please try again.")
    }
  }

  const onError = (errors: any) => {
    const firstError = Object.values(errors)[0] as any
    setAuthError(firstError?.message || "Please fill in all required fields correctly")
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  const fieldBase = "h-12 w-full rounded-lg border border-slate-200 bg-transparent px-4 text-sm transition-all outline-none focus:border-primary focus:ring-1 focus:ring-primary/20 text-slate-900 placeholder:text-slate-400"
  const labelBase = "text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5 block ml-1"

  if (success) return (
    <div className="flex min-h-screen items-center justify-center p-6 text-center">
      <div className="max-w-sm space-y-6">
        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-primary/5">
          <CheckCircle2 className="h-10 w-10 text-primary" />
        </div>
        <div className="space-y-2">
          <h2 className="text-2xl font-bold text-slate-900">Application Sent</h2>
          <p className="text-slate-500 text-sm leading-relaxed">We are reviewing your organization details. Check your email for an activation link shortly.</p>
        </div>
        <Button onClick={() => router.push("/signin")} className="w-full rounded-full h-12">Return to Login</Button>
      </div>
    </div>
  )

  return (
    <div className="min-h-screen w-full bg-white flex flex-col items-center py-12 px-6">
      <div className="w-full max-w-[480px] space-y-10">
        <div className="space-y-2">
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Third Party Registration</h1>
          <p className="text-slate-500 text-sm">Enter your details to create account.</p>
        </div>

        <form onSubmit={handleSubmit(onSubmit, onError)} className="space-y-6">
          {authError && (
            <div className="flex items-center gap-2 rounded-lg bg-red-50 p-3 text-xs font-medium text-red-600 border border-red-100">
              <AlertCircle className="h-4 w-4" /> {authError}
            </div>
          )}

          <div className="space-y-4">
            <h3 className="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Organization Details</h3>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div className="sm:col-span-2">
                <label className={labelBase}>Legal Company Name</label>
                <Input {...register("Name")} placeholder="e.g. Acme Corp" className={fieldBase} />
              </div>
              <div>
                <label className={labelBase}>Business Type</label>
                <div className="relative">
                  <select {...register("BusinessType")} className={cn(fieldBase, "appearance-none")}>
                    <option value="" className="bg-white text-slate-900">Select...</option>
                    {metadata.businessTypes.map((t: any) => (
                      <option key={t.id} value={t.value || t.id} className="bg-white text-slate-900">{t.name}</option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-3 top-3.5 h-4 w-4 text-slate-400 pointer-events-none" />
                </div>
              </div>
              <div>
                <label className={labelBase}>Country</label>
                <div className="relative">
                  <select {...register("Country")} className={cn(fieldBase, "appearance-none")}>
                    <option value="" className="bg-white text-slate-900">Select...</option>
                    {metadata.countries.map((c: any) => (
                      <option key={c.id} value={c.code || c.name} className="bg-white text-slate-900">
                        {c.flag} {c.name}
                      </option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-3 top-3.5 h-4 w-4 text-slate-400 pointer-events-none" />
                </div>
              </div>
              <div className="sm:col-span-2">
                <label className={labelBase}>Location / City</label>
                <div className="relative">
                  <select {...register("Location")} className={cn(fieldBase, "appearance-none")} disabled={!selectedCountry || metadata.localities.length === 0}>
                    <option value="" className="bg-white text-slate-900">
                      {!selectedCountry ? "Select country first..." : metadata.localities.length === 0 ? "Loading localities..." : "Select location..."}
                    </option>
                    {metadata.localities.map((l: any) => (
                      <option key={l.id} value={l.id} className="bg-white text-slate-900">{l.name}</option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-3 top-3.5 h-4 w-4 text-slate-400 pointer-events-none" />
                </div>
              </div>
            </div>

            {isSupplier && (
              <div className="animate-in fade-in duration-300">
                <label className={labelBase}>Supplier Category</label>
                <div className="relative">
                  <select {...register("supplier_category")} className={cn(fieldBase, "appearance-none border-primary/40")}>
                    <option value="" className="bg-white text-slate-900">Select Category...</option>
                    {metadata.supplierCategories.map((s: any) => (
                      <option key={s.id} value={s.id} className="bg-white text-slate-900">{s.name}</option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-3 top-3.5 h-4 w-4 text-primary/50 pointer-events-none" />
                </div>
              </div>
            )}
          </div>

          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div className="sm:col-span-2"><h3 className="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Identification & Contact</h3></div>
            <div>
              <label className={labelBase}>Reg Number</label>
              <Input {...register("RegistrationNumber")} placeholder="RC123456" className={fieldBase} />
            </div>
            <div>
              <label className={labelBase}>Tax PIN / ID</label>
              <Input {...register("TaxPIN")} placeholder="A0012345" className={fieldBase} />
            </div>
            <div className="sm:col-span-2">
              <label className={labelBase}>Company Phone</label>
              <Input {...register("Phone")} placeholder="+254700000000" className={fieldBase} />
            </div>
          </div>

          <div className="space-y-3">
            <label className={labelBase}>Applying as</label>
            <div className="flex flex-wrap gap-2">
              {['Supplier', 'Tenant', 'Customer'].map((type) => {
                const active = accountTypes.includes(type)
                return (
                  <label key={type} className={cn(
                    "flex-1 min-w-[100px] cursor-pointer rounded-lg border px-4 py-3 text-center transition-all",
                    active ? "border-primary bg-primary/5 text-primary" : "border-slate-200 text-slate-500 hover:border-slate-300"
                  )}>
                    <input type="checkbox" value={type} {...register("types")} className="hidden" />
                    <span className="text-xs font-bold">{type}</span>
                  </label>
                )
              })}
            </div>
          </div>

          <div className="rounded-xl border border-slate-100 p-4 space-y-4 bg-slate-50/30">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <UserPlus className="h-4 w-4 text-slate-400" />
                <span className="text-sm font-bold text-slate-900">Admin Account</span>
              </div>
              <input type="checkbox" {...register("createUser")} className="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" />
            </div>

            {createUser && (
              <div className="grid grid-cols-2 gap-3 animate-in fade-in duration-300">
                <Input {...register("user_FirstName")} placeholder="First Name" className={fieldBase} />
                <Input {...register("user_LastName")} placeholder="Last Name" className={fieldBase} />
                <div className="col-span-2">
                  <Input {...register("user_Email")} placeholder="Admin Work Email" type="email" className={fieldBase} />
                </div>
                <Input {...register("user_Phone")} placeholder="Phone (optional)" className={fieldBase} />
                <div className="relative">
                  <select {...register("user_Gender")} className={cn(fieldBase, "appearance-none")}>
                    <option value="" className="bg-white text-slate-900">Gender</option>
                    <option value="m" className="bg-white text-slate-900">Male</option>
                    <option value="f" className="bg-white text-slate-900">Female</option>
                    <option value="o" className="bg-white text-slate-900">Other</option>
                  </select>
                  <ChevronDown className="absolute right-3 top-3.5 h-4 w-4 text-slate-400 pointer-events-none" />
                </div>
                <div className="col-span-2">
                  <Input {...register("user_Password")} placeholder="Password" type="password" className={fieldBase} />
                  {errors.user_Password && (
                    <p className="text-xs text-red-500 mt-1 ml-1">{errors.user_Password.message}</p>
                  )}
                </div>
                <div className="col-span-2">
                  <Input {...register("user_Password_confirmation")} placeholder="Confirm Password" type="password" className={fieldBase} />
                  {errors.user_Password_confirmation && (
                    <p className="text-xs text-red-500 mt-1 ml-1">{errors.user_Password_confirmation.message}</p>
                  )}
                </div>
              </div>
            )}
          </div>

          <Button
            type="submit"
            disabled={isSubmitting}
            className="w-full h-12 rounded-lg bg-slate-900 hover:bg-black text-white font-bold transition-all disabled:opacity-50"
          >
            {isSubmitting ? <Loader2 className="h-5 w-5 animate-spin" /> : "Complete Registration"}
          </Button>

          <div className="text-center">
            <p className="text-sm text-slate-500">
              Already registered? <Link href="/signin" className="text-primary font-bold hover:underline">Sign In</Link>
            </p>
          </div>
        </form>
      </div>
    </div>
  )
}