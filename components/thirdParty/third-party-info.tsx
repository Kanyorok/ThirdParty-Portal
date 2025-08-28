'use client';

import React, { useState, useEffect } from 'react';
import { useSession } from 'next-auth/react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import {
    Save,
    Loader2,
    Info,
    Edit,
    XCircle,
    Building2,
    CheckCircle2,
    Clock,
    AlertCircle,
    MapPin,
    Mail,
    Phone,
    Globe,
    FileText,
    Percent,
} from 'lucide-react';
import { motion, AnimatePresence, Variants } from 'framer-motion';
import { Button } from '@/components/common/button';
import { Input } from '@/components/common/input';
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/common/form';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/common/select';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle
} from '@/components/common/card';
import { Badge } from '@/components/common/badge';
import { Separator } from '@/components/common/separator';

export enum BusinessTypeEnum {
    Sole = 1,
    Partnership = 2,
    LLC = 3,
    Corporation = 4,
    NGO = 5,
}

const businessTypeOptions = [
    { value: BusinessTypeEnum.Sole, label: 'Sole Proprietorship' },
    { value: BusinessTypeEnum.Partnership, label: 'Partnership' },
    { value: BusinessTypeEnum.LLC, label: 'Limited Liability Company' },
    { value: BusinessTypeEnum.Corporation, label: 'Corporation' },
    { value: BusinessTypeEnum.NGO, label: 'NGO' },
];

const thirdPartySchema = z.object({
    id: z.number().optional(),
    thirdPartyName: z.string().min(1, { message: 'Legal Name is required.' }),
    tradingName: z.string().nullable().optional().transform(e => e === '' ? null : e),
    businessType: z.coerce.number().min(1).int({ message: 'Invalid business type.' }),
    registrationNumber: z.string().min(1, { message: 'Registration Number is required.' }),
    taxPIN: z.string().min(1, { message: 'Tax PIN is required.' }),
    vatNumber: z.string().nullable().optional().transform(e => e === '' ? null : e),
    country: z.string().min(1, { message: 'Country is required.' }),
    physicalAddress: z.string().min(1, { message: 'Physical Address is required.' }),
    email: z.string().email({ message: 'Invalid email address.' }),
    phone: z.string().min(1, { message: 'Phone number is required.' }).regex(/^\+?[0-9()\s-]+$/, { message: 'Invalid phone number format.' }),
    website: z.string().url({ message: 'Invalid URL format.' }).nullable().optional().transform(e => e === '' ? null : e),
});

type ThirdPartyInputs = z.infer<typeof thirdPartySchema>;

interface ThirdPartyProfile {
    id: number;
    thirdPartyName: string;
    tradingName: string | null;
    businessType: BusinessTypeEnum;
    registrationNumber: string;
    taxPIN: string;
    vatNumber: string | null;
    country: string;
    physicalAddress: string;
    email: string;
    phone: string;
    website: string | null;
    createdOn: string;
    modifiedOn: string | null;
    status: number;
    thirdPartyType: number;
    approvalStatus: string;
}

const containerVariants: Variants = {
    hidden: { opacity: 0, y: 20 },
    visible: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.5,
            ease: [0.25, 0.46, 0.45, 0.94],
            staggerChildren: 0.08
        }
    },
    exit: {
        opacity: 0,
        y: -20,
        transition: { duration: 0.3 }
    }
};

const itemVariants = {
    hidden: { opacity: 0, y: 10 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.3 }
    }
};

const FieldDisplay: React.FC<{ label: string; value: string | null | undefined; icon?: React.ElementType }> = ({ label, value, icon: Icon }) => (
    <div className="space-y-1">
        <p className="text-sm font-medium text-muted-foreground">{label}</p>
        <div className="flex items-center gap-2 p-3 bg-muted rounded-lg border border-transparent group-hover:border-border transition-colors">
            {Icon && <Icon className="h-4 w-4 text-muted-foreground" />}
            <p className="font-semibold text-foreground break-words">{value || 'N/A'}</p>
        </div>
    </div>
);

export default function ThirdPartyDetailsForm() {
    const { data: session, status } = useSession();
    const [thirdPartyDetails, setThirdPartyDetails] = useState<ThirdPartyProfile | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isEditing, setIsEditing] = useState(false);

    const form = useForm<ThirdPartyInputs>({
        resolver: zodResolver(thirdPartySchema),
        defaultValues: {
            thirdPartyName: '',
            tradingName: '',
            businessType: BusinessTypeEnum.Sole,
            registrationNumber: '',
            taxPIN: '',
            vatNumber: '',
            country: '',
            physicalAddress: '',
            email: '',
            phone: '',
            website: '',
        },
    });

    const thirdPartyId = session?.user?.thirdParty?.id;

    const fetchThirdPartyDetails = async () => {
        if (status === 'loading' || !thirdPartyId) {
            setIsLoading(false);
            return;
        }

        setIsLoading(true);
        try {
            const response = await fetch(`/api/third-party-details`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to fetch third party details.');
            }
            const responseData: ThirdPartyProfile = await response.json();
            setThirdPartyDetails(responseData);
            form.reset({
                id: responseData.id,
                thirdPartyName: responseData.thirdPartyName || '',
                tradingName: responseData.tradingName || '',
                businessType: responseData.businessType,
                registrationNumber: responseData.registrationNumber || '',
                taxPIN: responseData.taxPIN || '',
                vatNumber: responseData.vatNumber || '',
                country: responseData.country || '',
                physicalAddress: responseData.physicalAddress || '',
                email: responseData.email || '',
                phone: responseData.phone || '',
                website: responseData.website || '',
            });
        } catch (error: any) {
            toast.error(error.message || 'Error fetching third party details.');
            setThirdPartyDetails(null);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        if (status === 'authenticated') {
            fetchThirdPartyDetails();
        } else if (status === 'unauthenticated') {
            setIsLoading(false);
        }
    }, [thirdPartyId, status]);

    const handleFormSubmit = async (data: ThirdPartyInputs) => {
        if (!thirdPartyId || !session?.accessToken) {
            toast.error('Authentication or Third Party ID missing. Cannot save details.');
            return;
        }

        const payload = { ...data };

        toast.promise(
            (async () => {
                const response = await fetch(`/api/third-party-details`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${session.accessToken}`,
                    },
                    body: JSON.stringify(payload),
                });

                const responseData = await response.json();

                if (!response.ok) {
                    const errorMessage = responseData.message || 'Failed to update details.';
                    const errorDetails = responseData.errors ? Object.values(responseData.errors).flat().join('\n') : '';
                    throw new Error(`${errorMessage}\n${errorDetails}`);
                }
                await fetchThirdPartyDetails();
                setIsEditing(false);
                return responseData.message || 'Third party details updated successfully!';
            })(),
            {
                loading: 'Updating details...',
                success: (message) => message,
                error: (error) => error.message,
            }
        );
    };

    const handleCancelEdit = () => {
        setIsEditing(false);
        if (thirdPartyDetails) {
            form.reset({
                id: thirdPartyDetails.id,
                thirdPartyName: thirdPartyDetails.thirdPartyName || '',
                tradingName: thirdPartyDetails.tradingName || '',
                businessType: thirdPartyDetails.businessType,
                registrationNumber: thirdPartyDetails.registrationNumber || '',
                taxPIN: thirdPartyDetails.taxPIN || '',
                vatNumber: thirdPartyDetails.vatNumber || '',
                country: thirdPartyDetails.country || '',
                physicalAddress: thirdPartyDetails.physicalAddress || '',
                email: thirdPartyDetails.email || '',
                phone: thirdPartyDetails.phone || '',
                website: thirdPartyDetails.website || '',
            });
        }
    };

    const getBusinessTypeName = (value: BusinessTypeEnum) => {
        const option = businessTypeOptions.find(opt => opt.value === value);
        return option ? option.label : 'N/A';
    };

    const getApprovalStatus = (status: string) => {
        switch (status) {
            case 'A':
                return { label: 'Approved', variant: 'default', icon: CheckCircle2, className: 'bg-green-500/10 text-green-500 border-green-500/50' };
            case 'R':
                return { label: 'Rejected', variant: 'destructive', icon: AlertCircle, className: 'bg-red-500/10 text-red-500 border-red-500/50' };
            default: // 'P'
                return { label: 'Pending', variant: 'secondary', icon: Clock, className: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/50' };
        }
    };

    const getActiveStatus = (status: number) => {
        return status === 1
            ? { label: 'Active', variant: 'default', className: 'bg-blue-500/10 text-blue-500 border-blue-500/50' }
            : { label: 'Inactive', variant: 'secondary', className: 'bg-gray-500/10 text-gray-500 border-gray-500/50' };
    };

    if (status === 'loading') {
        return (
            <div className="flex justify-center items-center h-64 bg-background rounded-xl border shadow-sm">
                <Loader2 className="animate-spin h-10 w-10 text-primary" />
            </div>
        );
    }

    if (!thirdPartyDetails && !isLoading) {
        return (
            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ duration: 0.4, ease: 'easeOut' }}
                className="flex flex-col items-center justify-center p-12 border-2 border-dashed border-muted-foreground/25 rounded-xl bg-muted/50 text-muted-foreground"
            >
                <Building2 className="h-16 w-16 mb-4 text-primary/50" />
                <h3 className="text-2xl font-semibold mb-2">No Company Information Found</h3>
                <p className="text-base text-center max-w-md">
                    It looks like you haven't set up your company details yet. Please contact support if you believe this is an error.
                </p>
            </motion.div>
        );
    }

    const approvalStatus = thirdPartyDetails ? getApprovalStatus(thirdPartyDetails.approvalStatus) : null;
    const activeStatus = thirdPartyDetails ? getActiveStatus(thirdPartyDetails.status) : null;

    return (
        <div className="max-w-7xl mx-auto space-y-6">
            <motion.div
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, ease: 'easeOut' }}
            >
                <Card className="overflow-hidden shadow-sm border-0 bg-gradient-to-br from-background to-muted/20">
                    <CardHeader className="bg-gradient-to-r from-primary/5 to-primary/10 border-b p-6 flex flex-row items-center justify-between">
                        <div className="flex items-center gap-3">
                            <Building2 className="h-7 w-7 text-primary" />
                            <CardTitle className="text-2xl font-bold text-foreground">Company Information</CardTitle>
                        </div>
                        {!isLoading && !isEditing && thirdPartyDetails && (
                            <Button
                                onClick={() => setIsEditing(true)}
                                size="lg"
                                variant="outline"
                                className="group border-primary/50 text-primary hover:bg-primary hover:text-primary-foreground transition-all duration-200 ease-in-out shadow-sm hover:shadow-md"
                            >
                                <Edit className="mr-2 h-5 w-5 group-hover:rotate-6 transition-transform" />
                                Edit Details
                            </Button>
                        )}
                    </CardHeader>

                    <CardContent className="p-8">
                        {isLoading ? (
                            <div className="flex flex-col justify-center items-center h-96 space-y-4">
                                <Loader2 className="animate-spin h-12 w-12 text-primary" />
                            </div>
                        ) : (
                            <AnimatePresence mode="wait">
                                {isEditing ? (
                                    <motion.div
                                        key="editing"
                                        variants={containerVariants}
                                        initial="hidden"
                                        animate="visible"
                                        exit="exit"
                                    >
                                        <Form {...form}>
                                            <form
                                                onSubmit={form.handleSubmit(handleFormSubmit)}
                                                className="space-y-8"
                                            >
                                                <motion.div variants={itemVariants} className="space-y-6">
                                                    <div className="flex items-center gap-4 mb-6">
                                                        <h3 className="text-xl font-semibold text-foreground">General Information</h3>
                                                        <Separator className="flex-grow" />
                                                    </div>

                                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                                        <FormField
                                                            control={form.control}
                                                            name="thirdPartyName"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Legal Company Name *</FormLabel>
                                                                    <FormControl>
                                                                        <Input placeholder="e.g., Acme Innovations Ltd." className="h-11" {...field} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="tradingName"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Trading Name</FormLabel>
                                                                    <FormControl>
                                                                        <Input placeholder="e.g., Acme Solutions" className="h-11" {...field} value={field.value ?? ''} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="businessType"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Business Type *</FormLabel>
                                                                    <Select onValueChange={value => field.onChange(parseInt(value))} value={String(field.value)}>
                                                                        <FormControl>
                                                                            <SelectTrigger className="h-11">
                                                                                <SelectValue placeholder="Select business type" />
                                                                            </SelectTrigger>
                                                                        </FormControl>
                                                                        <SelectContent>
                                                                            {businessTypeOptions.map((type) => (
                                                                                <SelectItem key={type.value} value={String(type.value)}>
                                                                                    {type.label}
                                                                                </SelectItem>
                                                                            ))}
                                                                        </SelectContent>
                                                                    </Select>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />
                                                    </div>
                                                </motion.div>

                                                <motion.div variants={itemVariants} className="space-y-6">
                                                    <div className="flex items-center gap-4 mb-6">
                                                        <h3 className="text-xl font-semibold text-foreground">Registration & Tax</h3>
                                                        <Separator className="flex-grow" />
                                                    </div>

                                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                                        <FormField
                                                            control={form.control}
                                                            name="registrationNumber"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Registration Number *</FormLabel>
                                                                    <FormControl>
                                                                        <Input placeholder="e.g., PVT/20XX/XXXX" className="h-11" {...field} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="taxPIN"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Tax PIN *</FormLabel>
                                                                    <FormControl>
                                                                        <Input placeholder="e.g., AXXXXXXXXX" className="h-11" {...field} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="vatNumber"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>VAT Number</FormLabel>
                                                                    <FormControl>
                                                                        <Input placeholder="e.g., 0123456K" className="h-11" {...field} value={field.value ?? ''} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />
                                                    </div>
                                                </motion.div>

                                                <motion.div variants={itemVariants} className="space-y-6">
                                                    <div className="flex items-center gap-4 mb-6">
                                                        <h3 className="text-xl font-semibold text-foreground">Contact & Location</h3>
                                                        <Separator className="flex-grow" />
                                                    </div>

                                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <FormField
                                                            control={form.control}
                                                            name="country"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Country *</FormLabel>
                                                                    <FormControl>
                                                                        <Input placeholder="e.g., Kenya" className="h-11" {...field} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="physicalAddress"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Physical Address *</FormLabel>
                                                                    <FormControl>
                                                                        <Input placeholder="e.g., 123 Main St, Nairobi" className="h-11" {...field} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="email"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Email Address *</FormLabel>
                                                                    <FormControl>
                                                                        <Input type="email" placeholder="e.g., info@acme.com" className="h-11" {...field} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="phone"
                                                            render={({ field }) => (
                                                                <FormItem>
                                                                    <FormLabel>Phone Number *</FormLabel>
                                                                    <FormControl>
                                                                        <Input type="tel" placeholder="e.g., +2547XXXXXXXX" className="h-11" {...field} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />

                                                        <FormField
                                                            control={form.control}
                                                            name="website"
                                                            render={({ field }) => (
                                                                <FormItem className="md:col-span-2">
                                                                    <FormLabel>Website</FormLabel>
                                                                    <FormControl>
                                                                        <Input type="url" placeholder="e.g., https://www.examplewebsite.com" className="h-11" {...field} value={field.value ?? ''} />
                                                                    </FormControl>
                                                                    <FormMessage />
                                                                </FormItem>
                                                            )}
                                                        />
                                                    </div>
                                                </motion.div>

                                                <motion.div variants={itemVariants} className="flex justify-end gap-4 pt-6">
                                                    <Button type="button" variant="outline" onClick={handleCancelEdit} disabled={form.formState.isSubmitting}>
                                                        <XCircle className="mr-2 h-4 w-4" />
                                                        Cancel
                                                    </Button>
                                                    <Button type="submit" disabled={form.formState.isSubmitting}>
                                                        {form.formState.isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                                                        <Save className="mr-2 h-4 w-4" />
                                                        Save Changes
                                                    </Button>
                                                </motion.div>
                                            </form>
                                        </Form>
                                    </motion.div>
                                ) : (
                                    <motion.div
                                        key="viewing"
                                        variants={containerVariants}
                                        initial="hidden"
                                        animate="visible"
                                        exit="exit"
                                        className="space-y-8"
                                    >
                                        <div className="flex items-center gap-4 mb-6">
                                            <h3 className="text-xl font-semibold text-foreground">General Information</h3>
                                            <Separator className="flex-grow" />
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                            <FieldDisplay label="Legal Company Name" value={thirdPartyDetails?.thirdPartyName} icon={Building2} />
                                            <FieldDisplay label="Trading Name" value={thirdPartyDetails?.tradingName} icon={Building2} />
                                            <FieldDisplay label="Business Type" value={thirdPartyDetails ? getBusinessTypeName(thirdPartyDetails.businessType) : ''} icon={Info} />
                                        </div>

                                        <div className="flex items-center gap-4 mb-6 mt-8">
                                            <h3 className="text-xl font-semibold text-foreground">Registration & Tax</h3>
                                            <Separator className="flex-grow" />
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                            <FieldDisplay label="Registration Number" value={thirdPartyDetails?.registrationNumber} icon={FileText} />
                                            <FieldDisplay label="Tax PIN" value={thirdPartyDetails?.taxPIN} icon={Percent} />
                                            <FieldDisplay label="VAT Number" value={thirdPartyDetails?.vatNumber} icon={Percent} />
                                        </div>

                                        <div className="flex items-center gap-4 mb-6 mt-8">
                                            <h3 className="text-xl font-semibold text-foreground">Contact & Location</h3>
                                            <Separator className="flex-grow" />
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <FieldDisplay label="Country" value={thirdPartyDetails?.country} icon={MapPin} />
                                            <FieldDisplay label="Physical Address" value={thirdPartyDetails?.physicalAddress} icon={MapPin} />
                                            <FieldDisplay label="Email Address" value={thirdPartyDetails?.email} icon={Mail} />
                                            <FieldDisplay label="Phone Number" value={thirdPartyDetails?.phone} icon={Phone} />
                                            <FieldDisplay label="Website" value={thirdPartyDetails?.website} icon={Globe} />
                                        </div>

                                        <div className="flex items-center gap-4 mb-6 mt-8">
                                            <h3 className="text-xl font-semibold text-foreground">Status</h3>
                                            <Separator className="flex-grow" />
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium text-muted-foreground">Approval Status</p>
                                                {approvalStatus && (
                                                    <Badge className={`flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold ${approvalStatus.className}`}>
                                                        <approvalStatus.icon className="h-4 w-4" />
                                                        {approvalStatus.label}
                                                    </Badge>
                                                )}
                                            </div>
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium text-muted-foreground">Active Status</p>
                                                {activeStatus && (
                                                    <Badge className={`flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold ${activeStatus.className}`}>
                                                        {activeStatus.label === 'Active' ? <CheckCircle2 className="h-4 w-4" /> : <XCircle className="h-4 w-4" />}
                                                        {activeStatus.label}
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        )}
                    </CardContent>
                </Card>
            </motion.div>
        </div>
    );
}
