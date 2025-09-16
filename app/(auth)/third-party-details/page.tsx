'use client';

import { useState, useEffect, useCallback, useMemo } from 'react';
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
    MapPin
} from 'lucide-react';

import { Button } from '@/components/common/button';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/common/form';
import { Input } from '@/components/common/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/common/select';
import { useEnums } from '@/hooks/use-enums';

const formSchema = z.object({
    ThirdPartyName: z.string()
        .min(2, 'Company name must be at least 2 characters')
        .max(100, 'Company name must be less than 100 characters')
        .regex(/^[a-zA-Z0-9\s&.-]+$/, 'Company name contains invalid characters'),
    TradingName: z.string()
        .max(100, 'Trading name must be less than 100 characters')
        .optional(),
    BusinessType: z.string().min(1, 'Please select a business type'),
    RegistrationNumber: z.string()
        .min(1, 'Registration number is required')
        .max(50, 'Registration number must be less than 50 characters'),
    TaxPIN: z.string()
        .max(20, 'Tax PIN must be less than 20 characters')
        .optional(),
    VATNumber: z.string()
        .max(20, 'VAT number must be less than 20 characters')
        .optional(),
    Country: z.string()
        .min(2, 'Country is required')
        .max(50, 'Country name must be less than 50 characters'),
    PhysicalAddress: z.string()
        .min(5, 'Physical address is required')
        .max(200, 'Address must be less than 200 characters'),
    Email: z.string()
        .email('Please enter a valid email address')
        .max(100, 'Email must be less than 100 characters'),
    Phone: z.string()
        .min(10, 'Please enter a valid phone number')
        .max(20, 'Phone number must be less than 20 characters')
        .regex(/^[\+]?[0-9\s\-\(\)]+$/, 'Please enter a valid phone number'),
    Website: z.string()
        .url('Please enter a valid URL')
        .max(100, 'Website URL must be less than 100 characters')
        .optional()
        .or(z.literal('')),
    ThirdPartyType: z.string().min(1, 'Please select a third party type'),
});

type FormData = z.infer<typeof formSchema>;

const businessTypes = [
    { value: 'Sole', label: 'Sole Proprietorship' },
    { value: 'Partnership', label: 'Partnership' },
    { value: 'Corporation', label: 'Corporation' },
    { value: 'LLC', label: 'Limited Liability Company' },
    { value: 'NGO', label: 'Non-Profit Organization' },
];

// Removed hardcoded thirdPartyTypes; now fetched dynamically via useEnums("third-party-types")

//animation variants
const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            duration: 0.6,
            ease: [0.22, 1, 0.36, 1],
            staggerChildren: 0.08,
        },
    },
};

const itemVariants: Variants = {
    hidden: { opacity: 0, y: 24 },
    visible: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.5,
            ease: [0.22, 1, 0.36, 1],
        },
    },
};

const formFieldVariants: Variants = {
    hidden: { opacity: 0, y: 12 },
    visible: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.4,
            ease: [0.22, 1, 0.36, 1],
        },
    },
};

const statusVariants: Variants = {
    hidden: { opacity: 0, y: -16, scale: 0.96 },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: {
            duration: 0.4,
            ease: [0.22, 1, 0.36, 1],
        },
    },
    exit: {
        opacity: 0,
        y: -16,
        scale: 0.96,
        transition: {
            duration: 0.3,
            ease: [0.22, 1, 0.36, 1],
        },
    },
};

const formFields = [
    {
        name: 'ThirdPartyName' as const,
        label: 'Company Name',
        placeholder: 'Enter your company name',
        required: true,
        icon: Building2,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'TradingName' as const,
        label: 'Trading Name',
        placeholder: 'Enter trading name (optional)',
        required: false,
        icon: Building2,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'BusinessType' as const,
        label: 'What is the business type?',
        placeholder: 'Select business type',
        required: true,
        type: 'select',
        options: businessTypes,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'RegistrationNumber' as const,
        label: 'Registration Number',
        placeholder: 'Enter registration number',
        required: true,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'TaxPIN' as const,
        label: 'Tax PIN',
        placeholder: 'Enter tax PIN (optional)',
        required: false,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'VATNumber' as const,
        label: 'VAT Number',
        placeholder: 'Enter VAT number (optional)',
        required: false,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'Country' as const,
        label: 'Country',
        placeholder: 'Enter country',
        required: true,
        icon: MapPin,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'PhysicalAddress' as const,
        label: 'Physical Address',
        placeholder: 'Enter physical address',
        required: true,
        icon: MapPin,
        gridSpan: 'col-span-full',
    },
    {
        name: 'Email' as const,
        label: 'Company Email',
        placeholder: 'Enter company email',
        required: true,
        type: 'email',
        icon: Mail,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'Phone' as const,
        label: 'Company Phone',
        placeholder: 'Enter company phone',
        required: true,
        type: 'tel',
        icon: Phone,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'Website' as const,
        label: 'Website',
        placeholder: 'https://example.com (optional)',
        required: false,
        type: 'url',
        icon: Globe,
        gridSpan: 'col-span-full sm:col-span-1',
    },
    {
        name: 'ThirdPartyType' as const,
        label: 'Third Party Type',
        placeholder: 'Select type',
        required: true,
        type: 'select',
        // options will be injected dynamically from hook data in render
        options: [],
        gridSpan: 'col-span-full sm:col-span-1',
    },
];

export default function RegisterThirdPartyDetails() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const userId = searchParams.get('user_id');

    // Fetch dynamic third party types
    const { data: thirdPartyTypeOptions, isLoading: thirdPartyTypesLoading, error: thirdPartyTypesError, refetch: refetchThirdPartyTypes } = useEnums('third-party-types');

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);

    const form = useForm<FormData>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            ThirdPartyName: '',
            TradingName: '',
            BusinessType: '',
            RegistrationNumber: '',
            TaxPIN: '',
            VATNumber: '',
            Country: '',
            PhysicalAddress: '',
            Email: '',
            Phone: '',
            Website: '',
            ThirdPartyType: '',
        },
        mode: 'onBlur',
    });

    const isFormValid = useMemo(() => {
        return form.formState.isValid;
    }, [form.formState.isValid]);

    const handleError = useCallback((errorMessage: string) => {
        setError(errorMessage);
        // Auto-clear error after 10 seconds
        setTimeout(() => setError(null), 10000);
    }, []);

    useEffect(() => {
        if (!userId) {
            handleError('User ID is missing. Please complete step 1 first.');
        }
    }, [userId, handleError]);

    const onSubmit = async (data: FormData) => {
        if (!userId) {
            handleError('User ID is missing. Cannot submit company details.');
            return;
        }

        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            const payload = {
                ...data,
                user_id: userId,
            };

            const response = await fetch(`${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/third-parties/register-details`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const responseData = await response.json();

            if (!response.ok) {
                if (response.status === 422 && responseData.errors) {
                    const errorMessages = Object.entries(responseData.errors)
                        .map(([field, messages]) => `${field}: ${(messages as string[]).join(', ')}`)
                        .join('; ');
                    handleError(`Validation failed: ${errorMessages}`);
                } else {
                    handleError(responseData.message || 'Registration failed. Please try again.');
                }
                return;
            }

            setSuccess(responseData.message || 'Company details registered successfully!');

            // Auto-redirect after success
            setTimeout(() => {
                router.push('/signin');
            }, 3000);

        } catch (err) {
            console.error('Registration error:', err);
            handleError('Network error occurred. Please check your connection and try again.');
        } finally {
            setLoading(false);
        }
    };

    if (error && error.includes('User ID is missing')) {
        return (
            <div className="min-h-screen  flex items-center justify-center p-4 sm:p-8">
                <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
                    className="w-full max-w-md"
                >
                    <div className="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 text-center space-y-6">
                        <div className="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto">
                            <AlertCircle className="w-8 h-8 text-red-500" />
                        </div>
                        <div className="space-y-3">
                            <h2 className="text-xl sm:text-2xl font-semibold text-slate-900">Access Required</h2>
                            <p className="text-slate-600 text-sm sm:text-base leading-relaxed">{error}</p>
                        </div>
                        <Button
                            asChild
                            className="w-full h-12 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl font-medium transition-all duration-200"
                        >
                            <Link href="/auth/signup">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Return to Step 1
                            </Link>
                        </Button>
                    </div>
                </motion.div>
            </div>
        );
    }

    return (
        <div className="min-h-screen">
            <div className="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
                <motion.div
                    variants={containerVariants}
                    initial="hidden"
                    animate="visible"
                    className="space-y-8 sm:space-y-12"
                >
                    <motion.div variants={itemVariants} className="text-center space-y-4 sm:space-y-6">
                        <div className="space-y-2 sm:space-y-3">
                            <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">
                                Complete Your Setup
                            </h1>
                            <p className="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed px-4">
                                Tell us about your business to unlock full platform access
                            </p>
                        </div>
                    </motion.div>

                    <AnimatePresence mode="wait">
                        {success && (
                            <motion.div
                                variants={statusVariants}
                                initial="hidden"
                                animate="visible"
                                exit="exit"
                                className="max-w-2xl mx-auto px-4"
                            >
                                <div className="bg-emerald-50 border border-emerald-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6">
                                    <div className="flex items-start space-x-3 sm:space-x-4">
                                        <div className="flex-shrink-0">
                                            <div className="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center">
                                                <Check className="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" />
                                            </div>
                                        </div>
                                        <div className="flex-1 space-y-3">
                                            <p className="text-emerald-800 font-medium text-sm sm:text-base">{success}</p>
                                            <p className="text-emerald-700 text-xs sm:text-sm">
                                                Redirecting to login in 3 seconds...
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </motion.div>
                        )}

                        {error && !success && (
                            <motion.div
                                variants={statusVariants}
                                initial="hidden"
                                animate="visible"
                                exit="exit"
                                className="max-w-2xl mx-auto px-4"
                            >
                                <div className="bg-red-50 border border-red-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6">
                                    <div className="flex items-start space-x-3 sm:space-x-4">
                                        <div className="flex-shrink-0">
                                            <div className="w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
                                                <AlertCircle className="w-4 h-4 sm:w-5 sm:h-5 text-red-600" />
                                            </div>
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-red-800 font-medium text-sm sm:text-base break-words">{error}</p>
                                        </div>
                                    </div>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    <motion.div variants={itemVariants} className="max-w-8xl mx-auto">
                        <div className="bg-white border border-slate-50 rounded-xl overflow-hidden">
                            <div className="p-6 sm:p-8 lg:p-10">
                                <Form {...form}>
                                    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">
                                        <motion.div
                                            variants={formFieldVariants}
                                            className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6"
                                        >
                                            {formFields.map((fieldConfig) => {
                                                const isThirdPartyType = fieldConfig.name === 'ThirdPartyType';
                                                const dynamicOptions = isThirdPartyType ? thirdPartyTypeOptions : fieldConfig.options;
                                                return (
                                                <FormField
                                                    key={fieldConfig.name}
                                                    control={form.control}
                                                    name={fieldConfig.name}
                                                    render={({ field }) => (
                                                        <FormItem className={`space-y-3 ${fieldConfig.gridSpan}`}>
                                                            <FormLabel className="text-sm font-medium text-slate-700 flex items-center gap-2">
                                                                {fieldConfig.icon && (
                                                                    <fieldConfig.icon className="w-4 h-4 text-slate-500" />
                                                                )}
                                                                {fieldConfig.label}
                                                                {fieldConfig.required && (
                                                                    <span className="text-red-500 text-xs">*</span>
                                                                )}
                                                            </FormLabel>
                                                            <FormControl>
                                                                {fieldConfig.type === 'select' ? (
                                                                    <Select onValueChange={field.onChange} value={field.value}>
                                                                        <SelectTrigger className="h-12 border-slate-200 rounded-2xl focus:border-slate-400 focus:ring-0 transition-all duration-200 hover:border-slate-300">
                                                                            <SelectValue placeholder={fieldConfig.placeholder} />
                                                                        </SelectTrigger>
                                                                        <SelectContent className="rounded-2xl border-slate-200">
                                                                            {isThirdPartyType && thirdPartyTypesLoading && (
                                                                                <div className="px-3 py-2 text-sm text-slate-500">Loading...</div>
                                                                            )}
                                                                            {isThirdPartyType && thirdPartyTypesError && (
                                                                                <div className="px-3 py-2 text-sm text-red-500 flex flex-col gap-2">
                                                                                    <span>Failed to load options.</span>
                                                                                    <button type="button" onClick={refetchThirdPartyTypes} className="underline text-blue-600 text-left">Retry</button>
                                                                                </div>
                                                                            )}
                                                                            {!thirdPartyTypesLoading && dynamicOptions?.map((option) => (
                                                                                <SelectItem
                                                                                    key={option.value}
                                                                                    value={option.value}
                                                                                    className="rounded-xl"
                                                                                >
                                                                                    <div className="flex flex-col">
                                                                                        <span className="font-medium">{option.label}</span>
                                                                                    </div>
                                                                                </SelectItem>
                                                                            ))}
                                                                        </SelectContent>
                                                                    </Select>
                                                                ) : (
                                                                    <Input
                                                                        type={fieldConfig.type || 'text'}
                                                                        placeholder={fieldConfig.placeholder}
                                                                        className="h-12 border-slate-200 rounded-2xl focus:border-slate-400 focus:ring-0 transition-all duration-200 hover:border-slate-300"
                                                                        {...field}
                                                                    />
                                                                )}
                                                            </FormControl>
                                                            <FormMessage className="text-xs text-red-600" />
                                                        </FormItem>
                                                    )}
                                                />
                                                );
                                            })}
                                        </motion.div>

                                        {/* Submit Button */}
                                        <motion.div
                                            variants={formFieldVariants}
                                            className="pt-6 border-t border-slate-100"
                                        >
                                            <Button
                                                type="submit"
                                                disabled={loading || !!success || !isFormValid}
                                                className="w-full h-12 sm:h-14 
             bg-blue-500 hover:bg-blue-600 
             disabled:bg-blue-200 text-white 
             rounded-2xl font-medium text-sm sm:text-base 
             transition-all duration-200 
             disabled:cursor-not-allowed"
                                            >
                                                {loading ? (
                                                    <>
                                                        <Loader2 className="w-4 h-4 sm:w-5 sm:h-5 mr-3 animate-spin" />
                                                        Processing Registration...
                                                    </>
                                                ) : (
                                                    'Complete Registration'
                                                )}
                                            </Button>


                                            {/* Form progress indicator */}
                                            <div className="mt-4 text-center">
                                                <p className="text-xs text-slate-500">
                                                    Step 2 of 2 • All fields marked with * are required
                                                </p>
                                            </div>
                                        </motion.div>
                                    </form>
                                </Form>
                            </div>
                        </div>
                    </motion.div>

                    {/* Footer */}
                    <motion.div variants={itemVariants} className="text-center">
                        <p className="text-xs sm:text-sm text-slate-500">
                            Need help? Contact our support team for assistance.
                        </p>
                    </motion.div>
                </motion.div>
            </div>
        </div>
    );
}
