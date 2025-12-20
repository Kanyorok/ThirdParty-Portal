'use client';

import { useState, useEffect, useCallback, useMemo } from 'react';
import { signOut } from 'next-auth/react';
import { useRouter, useSearchParams } from 'next/navigation';
import Link from 'next/link';
import { motion, AnimatePresence, Variants } from 'framer-motion';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import {
    Check,
    AlertCircle,
    ArrowLeft,
    Loader2,
    Building2,
    Globe,
    Mail,
    Phone,
    MapPin,
    UserCircle,
    FileText
} from 'lucide-react';

import { Button } from '@/components/common/button';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/common/form';
import { Input } from '@/components/common/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/common/select';

import { useEnums } from '@/hooks/use-enums';
import { Popover, PopoverContent, PopoverTrigger } from "@/components/common/popover";
import { format } from "date-fns";
import { CalendarIcon } from "lucide-react";
import { Calendar } from "@/components/common/calendar";
import { cn } from "@/lib/utils";
import { toast } from "sonner";

interface Country {
    id: number;
    name: string;
    code: string;
    iso2?: string;
}

interface Locality {
    ID: number;
    Name: string;
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
    // Backend expects 'Country' as CountryCode for Phone validation, but also 'CountryId'? 
    // NewThirdPartyRequest::getCountry() looks up by CountryCode.
    Country: z.string().min(2, 'Country is required'),
    Location: z.string().min(1, 'Location is required'), // ID
    PhysicalAddress: z.string()
        .min(5, 'Physical address is required')
        .max(200, 'Address must be less than 200 characters'),
    Email: z.string()
        .email('Please enter a valid email address')
        .max(100, 'Email must be less than 100 characters'),
    Phone: z.string()
        .min(10, 'Please enter a valid phone number')
        .max(20, 'Phone number must be less than 20 characters'),
    Website: z.string()
        .url('Please enter a valid URL')
        .max(100, 'Website URL must be less than 100 characters')
        .optional()
        .or(z.literal('')),

    // Types - Single Select
    types: z.string().min(1, 'Please select a third party type'),

    // Conditional Fields
    tenant_Remarks: z.string().optional(),

    customer_DateOfBirth: z.date().optional(),
    customer_Gender: z.string().optional(),
    customer_MaritalStatus: z.string().optional(),
    customer_Occupation: z.string().optional(),
}).superRefine((data, ctx) => {
    // Tenant Validation
    if (data.types === 'TN') {
        // Remarks optional for tenant? Blade says required_if:type,TN|nullable. nullable allows empty.
    }
    // Customer Validation
    if (data.types === 'CU') {
        if (!data.customer_DateOfBirth) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Date of Birth is required for Customers",
                path: ["customer_DateOfBirth"]
            });
        }
        if (!data.customer_Gender) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Gender is required for Customers",
                path: ["customer_Gender"]
            });
        }
        if (!data.customer_MaritalStatus) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Marital Status is required for Customers",
                path: ["customer_MaritalStatus"]
            });
        }
        if (!data.customer_Occupation) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                message: "Occupation is required for Customers",
                path: ["customer_Occupation"]
            });
        }
    }
});

type FormData = z.infer<typeof formSchema>;

//animation variants
const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: { opacity: 1, transition: { duration: 0.6, staggerChildren: 0.08 } },
};
const itemVariants: Variants = {
    hidden: { opacity: 0, y: 24 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
};
const statusVariants: Variants = {
    hidden: { opacity: 0, y: -16, scale: 0.96 },
    visible: { opacity: 1, y: 0, scale: 1, transition: { duration: 0.4 } },
    exit: { opacity: 0, y: -16, scale: 0.96, transition: { duration: 0.3 } },
};

export default function RegisterThirdPartyDetails() {
    const router = useRouter();
    // searchParams and userId moved to lower scope to avoid duplication with new logic


    // Fetch Enums
    const { data: businessTypes } = useEnums('BusinessType');
    const { data: typeOptions } = useEnums('third-party-types');
    const { data: genderOptions } = useEnums('Gender');
    const { data: maritalStatusOptions } = useEnums('MaritalStatus');
    const { data: occupationOptions } = useEnums('Occupation');

    const [countries, setCountries] = useState<Country[]>([]);
    const [localities, setLocalities] = useState<Locality[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);

    // File upload state (Step 3/4 effectively)
    const [uploading, setUploading] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const [uploadedDocs, setUploadedDocs] = useState<Array<{ id: number; name: string; size?: number; previewUrl?: string; }>>([]);
    const [selectedFiles, setSelectedFiles] = useState<FileList | null>(null);
    const [thirdPartyId, setThirdPartyId] = useState<number | null>(null);

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
            types: 'SU', // Default to Supplier
            tenant_Remarks: '',
            customer_Gender: '',
            customer_MaritalStatus: '',
            customer_Occupation: '',
        },
        mode: 'onChange',
    });

    // Helper to check selected types
    const selectedType = form.watch('types');
    const isTenant = selectedType === 'TN';
    const isCustomer = selectedType === 'CU';
    const selectedCountry = form.watch('Country');

    // Fetch Countries
    useEffect(() => {
        fetch('/api/v1/countries')
            .then(res => res.json())
            .then(data => {
                const list = data.data || [];
                setCountries(list.map((c: any) => ({
                    id: c.id,
                    name: c.name,
                    code: c.code, // Expecting CountryCode here
                    iso2: c.iso2
                })));
                if (list.length > 0) {
                    // Default Kenya if exists or first
                    const ke = list.find((c: any) => c.code === 'KE');
                    if (ke) form.setValue('Country', ke.code);
                    else form.setValue('Country', list[0].code);
                }
            })
            .catch(err => console.error("Failed to fetch countries", err));
    }, [form]);

    // Fetch Localities when Country changes
    useEffect(() => {
        if (!selectedCountry) {
            setLocalities([]);
            return;
        }
        // Fetch localities for country code
        fetch(`${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/v1/countries/${selectedCountry}/localities`)
            .then(res => res.json())
            .then(data => {

                const list = (data.data || []).map((l: any, index: number) => ({
                    ID: l.ID || l.id || l.iD || l.Id || index,
                    Name: l.Name || l.name || `Locality ${index}`,
                }));
                setLocalities(list);
            })
            .catch(err => console.error("Failed to fetch localities", err));
    }, [selectedCountry]);


    // Capture userId from URL query params
    const searchParams = useSearchParams();
    const [userId, setUserId] = useState<string | null>(null);

    useEffect(() => {
        const uid = searchParams.get('userId'); // Matches 'userId' from backend redirect
        if (uid) {
            setUserId(uid);
            console.log("User ID set from URL:", uid);
        } else {
            // Fallback: try reading from session/auth if logged in, or localStorage?
            // Since we are moving to NO AUTH flow, URL param is critical.
            // Maybe show error or redirect if missing?
            console.warn("No User ID found in URL.");
        }
    }, [searchParams]);

    // Check for existing party (if re-visiting) - original useEffect removed as userId is now stateful
    // useEffect(() => {
    //     if (userId) {
    //         // Logic to check if user already has a party could go here,
    //         // but usually this page is for NEW registration.
    //     }
    // }, [userId]);

    const [isSubmitting, setIsSubmitting] = useState(false); // Added for submission state

    const onSubmit = async (data: FormData) => {
        setIsSubmitting(true);
        try {
            if (!userId) {
                toast.error("User identification missing. Please use the link from your email.");
                setIsSubmitting(false);
                return;
            }

            const payload = {
                ...data,
                types: [data.types], // Backend expects array
                user_id: userId,
                // Ensure dates are strings if needed, though JSON.stringify handles Date -> ISO string
                // Backend NewThirdPartyRequest expects 'customer_DateOfBirth' as 'date' so ISO string works.
            };

            const response = await fetch(`${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/third-parties/register-details`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });

            const resData = await response.json();

            if (!response.ok) {
                if (response.status === 422 && resData.errors) {
                    const errorMessages = Object.entries(resData.errors)
                        .map(([field, messages]) => `${field}: ${(messages as string[]).join(', ')}`)
                        .join('; ');
                    throw new Error(`Validation failed: ${errorMessages}`);
                }
                throw new Error(resData.message || 'Registration failed.');
            }

            setSuccess(resData.message || 'Details registered successfully!');
            // Extract created party ID for file upload step if we were to stay on page
            if (resData.third_party?.Id) setThirdPartyId(resData.third_party.Id);

            // Redirect logic
            try {
                await signOut({ redirect: false });
            } catch { }
            setTimeout(() => {
                router.replace('/signin?registrationSuccess=true');
            }, 1000);

        } catch (err: any) {
            setError(err.message || 'An error occurred.');
        } finally {
            setLoading(false);
        }
    };

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
        );
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

                                    {/* Compnay Info Section */}
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

                                    {/* Contact & Location */}
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
                                                    <FormControl><Input type="tel" {...field} /></FormControl>
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

                                    {/* Account Types */}
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

                                        {/* Tenant Fields */}
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

                                        {/* Customer Fields */}
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
    );
}
