'use client'

import { useState, useEffect } from 'react'
import { signOut } from 'next-auth/react'
import { useRouter, useSearchParams } from 'next/navigation'
import Link from 'next/link'
import { motion, AnimatePresence, Variants } from 'framer-motion'
import { useForm, useWatch } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import * as z from 'zod'
import {
    Check,
    AlertCircle,
    Loader2,
    Building2,
    MapPin,
    UserCircle,
} from 'lucide-react'

import { Button } from '@/components/common/button'
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/common/form'
import { Input } from '@/components/common/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/common/select'

import { useEnums } from '@/hooks/use-enums'
import { Popover, PopoverContent, PopoverTrigger } from "@/components/common/popover"
import { format } from "date-fns"
import { CalendarIcon } from "lucide-react"
import { Calendar } from "@/components/common/calendar"
import { cn } from "@/lib/utils"
import { toast } from "sonner"

interface Country {
    id: number
    name: string
    code: string
    iso2?: string
}

interface Locality {
    ID: number
    Name: string
}

const SENSITIVE_ERROR_PATTERN = /(exception|stack|trace|sql|syntax|internal server|undefined|vendor|route|line\s+\d+)/i

const SAFE_FIELD_MESSAGES: Record<string, string> = {
    Name: "Please enter a valid company name.",
    TradingName: "Please enter a valid trading name.",
    BusinessType: "Please select a valid business type.",
    RegistrationNumber: "Please enter a valid registration number.",
    TaxPIN: "Please enter a valid tax PIN.",
    VATNumber: "Please enter a valid VAT number.",
    Country: "Please select a valid country.",
    Location: "Please select a valid location.",
    PhysicalAddress: "Please enter a valid physical address.",
    Email: "Please enter a valid email address.",
    Phone: "Please enter a valid phone number.",
    Website: "Please enter a valid website URL.",
    types: "Please select a valid account type.",
    tenant_Remarks: "Please enter valid remarks.",
    customer_DateOfBirth: "Please provide a valid date of birth.",
    customer_Gender: "Please select a valid gender.",
    customer_MaritalStatus: "Please select a valid marital status.",
    customer_Occupation: "Please select a valid occupation.",
}

const getSafeFieldMessage = (field: string, candidate: unknown) => {
    const fallback = SAFE_FIELD_MESSAGES[field] ?? "Please provide a valid value."
    if (typeof candidate !== "string") return fallback

    const normalized = candidate.replace(/\s+/g, " ").trim()
    if (!normalized || normalized.length > 140 || SENSITIVE_ERROR_PATTERN.test(normalized)) {
        return fallback
    }

    return normalized
}

const parseApiPayload = async (response: Response): Promise<Record<string, unknown>> => {
    const text = await response.text().catch(() => "")
    if (!text) return {}
    try {
        return JSON.parse(text) as Record<string, unknown>
    } catch {
        return { message: text }
    }
}

const formSchema = z.object({
    Name: z.string()
        .min(2, 'Company name must be at least 2 characters')
        .max(100, 'Company name must be less than 100 characters')
        .regex(/^[a-zA-Z0-9\s&.-]+$/, 'Company name contains invalid characters'),
    TradingName: z.string()
        .max(100, 'Trading name must be less than 100 characters')
        .optional()
        .or(z.literal('')),
    BusinessType: z.string().min(1, 'Please select a business type'),
    RegistrationNumber: z.string()
        .min(1, 'Registration number is required')
        .max(50, 'Registration number must be less than 50 characters'),
    TaxPIN: z.string()
        .max(20, 'Tax PIN must be less than 20 characters')
        .optional()
        .or(z.literal('')),
    VATNumber: z.string()
        .max(20, 'VAT number must be less than 20 characters')
        .optional()
        .or(z.literal('')),
    Country: z.string().min(2, 'Country is required'),
    Location: z.string().min(1, 'Location is required'),
    PhysicalAddress: z.string()
        .min(5, 'Physical address is required')
        .max(200, 'Address must be less than 200 characters'),
    Email: z.string()
        .email('Please enter a valid email address')
        .max(100, 'Email must be less than 100 characters'),
    Phone: z.string()
        .trim()
        .min(1, 'Phone number is required')
        .regex(/^\+?[0-9]{8,15}$/, 'Phone number must be 8 to 15 digits and may start with +'),
    Website: z.string()
        .url('Please enter a valid URL')
        .max(100, 'Website URL must be less than 100 characters')
        .optional()
        .or(z.literal('')),

    types: z.string().min(1, 'Please select a third party type'),

    tenant_Remarks: z.string().optional(),

    customer_DateOfBirth: z.date().optional(),
    customer_Gender: z.string().optional(),
    customer_MaritalStatus: z.string().optional(),
    customer_Occupation: z.string().optional(),
}).superRefine((data, ctx) => {
    if (data.types === 'CU') {
        if (!data.customer_DateOfBirth) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Date of Birth is required for Customers",
                path: ["customer_DateOfBirth"]
            })
        }
        if (!data.customer_Gender) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Gender is required for Customers",
                path: ["customer_Gender"]
            })
        }
        if (!data.customer_MaritalStatus) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Marital Status is required for Customers",
                path: ["customer_MaritalStatus"]
            })
        }
        if (!data.customer_Occupation) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Occupation is required for Customers",
                path: ["customer_Occupation"]
            })
        }
    }
})

type FormData = z.infer<typeof formSchema>

const formFields = new Set<keyof FormData>([
    'Name',
    'TradingName',
    'BusinessType',
    'RegistrationNumber',
    'TaxPIN',
    'VATNumber',
    'Country',
    'Location',
    'PhysicalAddress',
    'Email',
    'Phone',
    'Website',
    'types',
    'tenant_Remarks',
    'customer_DateOfBirth',
    'customer_Gender',
    'customer_MaritalStatus',
    'customer_Occupation',
])

const isFormField = (field: string): field is keyof FormData => formFields.has(field as keyof FormData)

const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: { opacity: 1, transition: { duration: 0.6, staggerChildren: 0.08 } },
}
const itemVariants: Variants = {
    hidden: { opacity: 0, y: 24 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
}
const statusVariants: Variants = {
    hidden: { opacity: 0, y: -16, scale: 0.96 },
    visible: { opacity: 1, y: 0, scale: 1, transition: { duration: 0.4 } },
    exit: { opacity: 0, y: -16, scale: 0.96, transition: { duration: 0.3 } },
}

export default function RegisterThirdPartyDetails() {
    const router = useRouter()
    const searchParams = useSearchParams()
    const userId = searchParams?.get('userId')?.trim() || null
    const externalApiBaseUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL

    const { data: businessTypes } = useEnums('BusinessType')
    const { data: typeOptions } = useEnums('third-party-types')
    const { data: genderOptions } = useEnums('Gender')
    const { data: maritalStatusOptions } = useEnums('MaritalStatus')
    const { data: occupationOptions } = useEnums('Occupation')

    const [countries, setCountries] = useState<Country[]>([])
    const [localities, setLocalities] = useState<Locality[]>([])
    const [loading, setLoading] = useState(false)
    const [error, setError] = useState<string | null>(null)
    const [success, setSuccess] = useState<string | null>(null)

    const form = useForm<FormData>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            Name: '',
            TradingName: '',
            BusinessType: '',
            RegistrationNumber: '',
            TaxPIN: '',
            VATNumber: '',
            Country: '',
            Location: '',
            PhysicalAddress: '',
            Email: '',
            Phone: '',
            Website: '',
            types: 'SU',
            tenant_Remarks: '',
            customer_Gender: '',
            customer_MaritalStatus: '',
            customer_Occupation: '',
        },
        mode: 'onChange',
    })

    const selectedType = useWatch({ control: form.control, name: 'types' })
    const isTenant = selectedType === 'TN'
    const isCustomer = selectedType === 'CU'
    const selectedCountry = useWatch({ control: form.control, name: 'Country' })

    useEffect(() => {
        let active = true
        const loadCountries = async () => {
            try {
                const response = await fetch('/api/v1/countries', { cache: 'no-store' })
                if (!response.ok) throw new Error('Failed to fetch countries')

                const payload = await parseApiPayload(response)
                const list = Array.isArray(payload.data) ? payload.data : []
                const mapped = list
                    .map((country): Country | null => {
                        if (!country || typeof country !== 'object') return null
                        const item = country as Record<string, unknown>
                        const id = Number(item.id)
                        const name = typeof item.name === 'string' ? item.name : ''
                        const code = typeof item.code === 'string' ? item.code : ''
                        const iso2 = typeof item.iso2 === 'string' ? item.iso2 : undefined
                        if (!Number.isFinite(id) || !name || !code) return null
                        return { id, name, code, iso2 }
                    })
                    .filter((country): country is Country => country !== null)

                if (!active) return
                setCountries(mapped)

                if (mapped.length > 0 && !form.getValues('Country')) {
                    const preferred = mapped.find((country) => country.code === 'KE') ?? mapped[0]
                    form.setValue('Country', preferred.code, { shouldValidate: true })
                }
            } catch {
                if (!active) return
                setError('Failed to load countries. Please refresh and try again.')
            }
        }

        void loadCountries()

        return () => {
            active = false
        }
    }, [form])

    useEffect(() => {
        if (!selectedCountry) {
            setLocalities([])
            return
        }
        if (!externalApiBaseUrl) {
            setLocalities([])
            setError('Location service is unavailable. Please try again later.')
            return
        }

        const controller = new AbortController()
        const loadLocalities = async () => {
            try {
                const response = await fetch(`${externalApiBaseUrl}/api/v1/countries/${selectedCountry}/localities`, {
                    signal: controller.signal,
                    cache: 'no-store',
                })
                if (!response.ok) throw new Error('Failed to fetch localities')

                const payload = await parseApiPayload(response)
                const raw = Array.isArray(payload.data) ? payload.data : []
                const mapped = raw
                    .map((locality, index): Locality | null => {
                        if (!locality || typeof locality !== 'object') return null
                        const item = locality as Record<string, unknown>
                        const rawId = item.ID ?? item.id ?? item.iD ?? item.Id
                        const parsedId = Number(rawId)
                        const ID = Number.isFinite(parsedId) ? parsedId : index + 1
                        const Name =
                            (typeof item.Name === 'string' && item.Name) ||
                            (typeof item.name === 'string' && item.name) ||
                            `Locality ${index + 1}`
                        return { ID, Name }
                    })
                    .filter((locality): locality is Locality => locality !== null)

                setLocalities(mapped)
            } catch {
                if (controller.signal.aborted) return
                setLocalities([])
                setError('Failed to load locations. Please reselect the country.')
            }
        }

        void loadLocalities()

        return () => {
            controller.abort()
        }
    }, [externalApiBaseUrl, selectedCountry])

    const onSubmit = async (data: FormData) => {
        setLoading(true)
        setError(null)

        try {
            if (!userId) {
                toast.error("User identification missing. Please use the link from your email.")
                throw new Error("User identification missing. Please use the link from your email.")
            }

            if (!externalApiBaseUrl) {
                throw new Error("Registration service is unavailable. Please try again.")
            }

            const payload = {
                ...data,
                types: [data.types],
                user_id: userId,
            }

            const response = await fetch(`${externalApiBaseUrl}/api/third-parties/register-details`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })

            const resData = await parseApiPayload(response)

            if (!response.ok) {
                const errors = resData.errors
                if (response.status === 422 && errors && typeof errors === 'object' && !Array.isArray(errors)) {
                    Object.entries(errors).forEach(([field, messages]) => {
                        if (!isFormField(field)) return
                        const first = Array.isArray(messages) ? messages[0] : messages
                        form.setError(field, {
                            type: 'server',
                            message: getSafeFieldMessage(field, first),
                        })
                    })
                    throw new Error("Please correct the highlighted fields and try again.")
                }

                throw new Error("We couldn't complete registration right now. Please try again.")
            }

            const successMessage = typeof resData.message === 'string' && resData.message.trim()
                ? resData.message
                : 'Details registered successfully!'
            setSuccess(successMessage)

            try {
                await signOut({ redirect: false })
            } catch { }
            setTimeout(() => {
                router.replace('/signin?registrationSuccess=true')
            }, 1000)

        } catch (err: unknown) {
            setError(err instanceof Error ? err.message : "We couldn't complete registration right now. Please try again.")
        } finally {
            setLoading(false)
        }
    }

    if (!userId && !success) {
        return (
            <div className="min-h-screen flex items-center justify-center p-4">
                <div className="bg-white p-8 rounded-2xl shadow-sm text-center border border-slate-100 max-w-md">
                    <AlertCircle className="w-12 h-12 text-red-500 mx-auto mb-4" />
                    <h2 className="text-xl font-bold mb-2">Access Required</h2>
                    <p className="text-slate-600 mb-6">User ID is missing. Please register a user first.</p>
                    <Button asChild className="w-full"><Link href="/signup">Go to Sign Up</Link></Button>
                </div>
            </div>
        )
    }

    return (
        <div className="min-h-screen">
            <div className="w-full max-w-5xl mx-auto px-4 sm:px-6 py-12">
                <motion.div variants={containerVariants} initial="hidden" animate="visible" className="space-y-8">

                    <motion.div variants={itemVariants} className="text-center space-y-4">
                        <h1 className="text-3xl font-bold text-slate-900">Complete Your Profile</h1>
                        <p className="text-slate-600 max-w-2xl mx-auto">
                            Provide your company details and select your account types.
                        </p>
                    </motion.div>

                    <AnimatePresence>
                        {error && (
                            <motion.div variants={statusVariants} initial="hidden" animate="visible" exit="exit" className="bg-red-50 border border-red-200 p-4 rounded-xl flex gap-3 text-red-700">
                                <AlertCircle className="w-5 h-5 shrink-0" />
                                <p>{error}</p>
                            </motion.div>
                        )}
                        {success && (
                            <motion.div variants={statusVariants} initial="hidden" animate="visible" exit="exit" className="bg-emerald-50 border border-emerald-200 p-4 rounded-xl flex gap-3 text-emerald-700">
                                <Check className="w-5 h-5 shrink-0" />
                                <div>
                                    <p className="font-medium">{success}</p>
                                    <p className="text-sm mt-1">Redirecting to login...</p>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    <motion.div variants={itemVariants} className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div className="p-8">
                            <Form {...form}>
                                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">

                                    <div className="space-y-6">
                                        <h3 className="text-lg font-semibold flex items-center gap-2 border-b pb-2">
                                            <Building2 className="w-5 h-5 text-blue-600" /> Company Information
                                        </h3>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <FormField control={form.control} name="Name" render={({ field }) => (
                                                <FormItem className="col-span-full">
                                                    <FormLabel>Company Name <span className="text-red-500">*</span></FormLabel>
                                                    <FormControl><Input placeholder="Legal Company Name" {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="TradingName" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Trading Name</FormLabel>
                                                    <FormControl><Input placeholder="Optional" {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="BusinessType" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Business Type <span className="text-red-500">*</span></FormLabel>
                                                    <Select onValueChange={field.onChange} value={field.value}>
                                                        <FormControl><SelectTrigger><SelectValue placeholder="Select type" /></SelectTrigger></FormControl>
                                                        <SelectContent>
                                                            {businessTypes.map(opt => <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>)}
                                                        </SelectContent>
                                                    </Select>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="RegistrationNumber" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Registration Number <span className="text-red-500">*</span></FormLabel>
                                                    <FormControl><Input {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="TaxPIN" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Tax PIN</FormLabel>
                                                    <FormControl><Input {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="VATNumber" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>VAT Number</FormLabel>
                                                    <FormControl><Input {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                        </div>
                                    </div>

                                    <div className="space-y-6">
                                        <h3 className="text-lg font-semibold flex items-center gap-2 border-b pb-2">
                                            <MapPin className="w-5 h-5 text-blue-600" /> Location & Contact
                                        </h3>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <FormField control={form.control} name="Email" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Email <span className="text-red-500">*</span></FormLabel>
                                                    <FormControl><Input type="email" {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="Phone" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Phone <span className="text-red-500">*</span></FormLabel>
                                                    <FormControl><Input type="tel" inputMode="tel" pattern="[+]?[0-9]{8,15}" placeholder="+254712345678" {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="Country" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Country <span className="text-red-500">*</span></FormLabel>
                                                    <Select onValueChange={field.onChange} value={field.value}>
                                                        <FormControl><SelectTrigger><SelectValue placeholder="Select country" /></SelectTrigger></FormControl>
                                                        <SelectContent>
                                                            {countries.map(c => <SelectItem key={c.id} value={c.code}>{c.name}</SelectItem>)}
                                                        </SelectContent>
                                                    </Select>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="Location" render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel>Location / City <span className="text-red-500">*</span></FormLabel>
                                                    <Select onValueChange={field.onChange} value={field.value} disabled={localities.length === 0}>
                                                        <FormControl><SelectTrigger><SelectValue placeholder={localities.length === 0 ? "Select country first" : "Select location"} /></SelectTrigger></FormControl>
                                                        <SelectContent>
                                                            {localities.map((l, index) => <SelectItem key={l.ID || `loc-${index}`} value={String(l.ID)}>{l.Name}</SelectItem>)}
                                                        </SelectContent>
                                                    </Select>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="PhysicalAddress" render={({ field }) => (
                                                <FormItem className="col-span-full">
                                                    <FormLabel>Physical Address <span className="text-red-500">*</span></FormLabel>
                                                    <FormControl><Input {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                            <FormField control={form.control} name="Website" render={({ field }) => (
                                                <FormItem className="col-span-full">
                                                    <FormLabel>Website</FormLabel>
                                                    <FormControl><Input placeholder="https://..." {...field} /></FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )} />
                                        </div>
                                    </div>

                                    <div className="space-y-6">
                                        <h3 className="text-lg font-semibold flex items-center gap-2 border-b pb-2">
                                            <UserCircle className="w-5 h-5 text-blue-600" /> Account Types
                                        </h3>
                                        <FormField control={form.control} name="types" render={({ field }) => (
                                            <FormItem>
                                                <FormLabel>Account Type <span className="text-red-500">*</span></FormLabel>
                                                <Select onValueChange={field.onChange} value={field.value}>
                                                    <FormControl><SelectTrigger><SelectValue placeholder="Select account type" /></SelectTrigger></FormControl>
                                                    <SelectContent>
                                                        {typeOptions.map(opt => <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>)}
                                                    </SelectContent>
                                                </Select>
                                                <FormMessage />
                                            </FormItem>
                                        )} />

                                        {isTenant && (
                                            <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} className="bg-slate-50 p-6 rounded-xl space-y-4">
                                                <h4 className="font-medium text-slate-800">Tenant Details</h4>
                                                <FormField control={form.control} name="tenant_Remarks" render={({ field }) => (
                                                    <FormItem>
                                                        <FormLabel>Remarks</FormLabel>
                                                        <FormControl><Input {...field} /></FormControl>
                                                        <FormMessage />
                                                    </FormItem>
                                                )} />
                                            </motion.div>
                                        )}

                                        {isCustomer && (
                                            <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} className="bg-slate-50 p-6 rounded-xl space-y-4">
                                                <h4 className="font-medium text-slate-800">Customer Details</h4>
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <FormField control={form.control} name="customer_DateOfBirth" render={({ field }) => (
                                                        <FormItem className="flex flex-col">
                                                            <FormLabel>Date of Birth <span className="text-red-500">*</span></FormLabel>
                                                            <Popover>
                                                                <PopoverTrigger asChild>
                                                                    <FormControl>
                                                                        <Button variant={"outline"} className={cn("w-full pl-3 text-left font-normal h-12 rounded-2xl", !field.value && "text-muted-foreground")}>
                                                                            {field.value ? format(field.value, "PPP") : <span>Pick a date</span>}
                                                                            <CalendarIcon className="ml-auto h-4 w-4 opacity-50" />
                                                                        </Button>
                                                                    </FormControl>
                                                                </PopoverTrigger>
                                                                <PopoverContent className="w-auto p-0" align="start">
                                                                    <Calendar
                                                                        mode="single"
                                                                        selected={field.value}
                                                                        onSelect={field.onChange}
                                                                        disabled={(date) => date > new Date() || date < new Date("1900-01-01")}
                                                                        initialFocus
                                                                    />
                                                                </PopoverContent>
                                                            </Popover>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )} />
                                                    <FormField control={form.control} name="customer_Gender" render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Gender <span className="text-red-500">*</span></FormLabel>
                                                            <Select onValueChange={field.onChange} value={field.value}>
                                                                <FormControl><SelectTrigger><SelectValue placeholder="Select" /></SelectTrigger></FormControl>
                                                                <SelectContent>
                                                                    {genderOptions.map(opt => <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>)}
                                                                </SelectContent>
                                                            </Select>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )} />
                                                    <FormField control={form.control} name="customer_MaritalStatus" render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Marital Status <span className="text-red-500">*</span></FormLabel>
                                                            <Select onValueChange={field.onChange} value={field.value}>
                                                                <FormControl><SelectTrigger><SelectValue placeholder="Select" /></SelectTrigger></FormControl>
                                                                <SelectContent>
                                                                    {maritalStatusOptions.map(opt => <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>)}
                                                                </SelectContent>
                                                            </Select>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )} />
                                                    <FormField control={form.control} name="customer_Occupation" render={({ field }) => (
                                                        <FormItem>
                                                            <FormLabel>Occupation <span className="text-red-500">*</span></FormLabel>
                                                            <Select onValueChange={field.onChange} value={field.value}>
                                                                <FormControl><SelectTrigger><SelectValue placeholder="Select" /></SelectTrigger></FormControl>
                                                                <SelectContent>
                                                                    {occupationOptions.map(opt => <SelectItem key={opt.value} value={opt.value}>{opt.label}</SelectItem>)}
                                                                </SelectContent>
                                                            </Select>
                                                            <FormMessage />
                                                        </FormItem>
                                                    )} />
                                                </div>
                                            </motion.div>
                                        )}
                                    </div>

                                    <Button type="submit" size="lg" className="w-full" disabled={loading}>
                                        {loading ? <><Loader2 className="w-4 h-4 mr-2 animate-spin" /> Processing...</> : 'Complete Registration'}
                                    </Button>

                                </form>
                            </Form>
                        </div>
                    </motion.div>
                </motion.div>
            </div>
        </div>
    )
}
