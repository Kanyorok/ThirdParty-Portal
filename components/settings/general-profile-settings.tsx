'use client';

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Loader2, Save } from 'lucide-react';
import { Button } from '@/components/common/button';
import { Input } from '@/components/common/input';
import { Label } from '@/components/common/label';
import { useSession } from 'next-auth/react';

const generalProfileSchema = z.object({
    firstName: z.string().min(1, "First name is required.").max(50),
    lastName: z.string().min(1, "Last name is required.").max(50),
    email: z.string().email("Invalid email address."),
    phone: z.string().nullable().optional(),
    gender: z.string().nullable().optional(),
    imageId: z.number().nullable().optional(),
    tradingName: z.string().nullable().optional(),
    businessType: z.string().nullable().optional(),
    registrationNumber: z.string().nullable().optional(),
    taxPin: z.string().nullable().optional(),
    vatNumber: z.string().nullable().optional(),
    country: z.string().nullable().optional(),
    physicalAddress: z.string().nullable().optional(),
    website: z.string().url("Invalid URL format.").nullable().optional(),
}).partial();

type GeneralProfileInputs = z.infer<typeof generalProfileSchema>;

interface GeneralProfileSettingsProps {
    initialData: {
        firstName: string;
        lastName: string;
        email: string;
        phone: string | null;
        gender: string | null;
        imageId: number | null;
        thirdParty: {
            tradingName: string | null;
            businessType: string | null;
            registrationNumber: string | null;
            taxPin: string | null;
            vatNumber: string | null;
            country: string | null;
            physicalAddress: string | null;
            website: string | null;
        } | null;
    };
}

export default function GeneralProfileSettings({ initialData }: GeneralProfileSettingsProps) {
    const { data: session } = useSession();

    const {
        register,
        handleSubmit,
        reset,
        formState: { errors, isSubmitting, isDirty },
    } = useForm<GeneralProfileInputs>({
        resolver: zodResolver(generalProfileSchema),
        defaultValues: {
            firstName: initialData.firstName,
            lastName: initialData.lastName,
            email: initialData.email,
            phone: initialData.phone,
            gender: initialData.gender,
            imageId: initialData.imageId,
            tradingName: initialData.thirdParty?.tradingName,
            businessType: initialData.thirdParty?.businessType,
            registrationNumber: initialData.thirdParty?.registrationNumber,
            taxPin: initialData.thirdParty?.taxPin,
            vatNumber: initialData.thirdParty?.vatNumber,
            country: initialData.thirdParty?.country,
            physicalAddress: initialData.thirdParty?.physicalAddress,
            website: initialData.thirdParty?.website,
        },
    });

    const handleFormSubmit = async (data: GeneralProfileInputs) => {
        if (!session?.accessToken) {
            toast.error("Authentication required to save changes.");
            return;
        }

        toast.promise(
            fetch('/api/third-party-profile', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${session.accessToken}`,
                },
                body: JSON.stringify(data),
            }).then(async (res) => {
                const responseData = await res.json();
                if (!res.ok) {
                    const errorMessage = responseData.message || 'Failed to update profile.';
                    const errorDetails = responseData.errors ? Object.values(responseData.errors).flat().join('\n') : '';
                    throw new Error(`${errorMessage}\n${errorDetails}`);
                }
                reset(data);
                return responseData.message || 'Profile updated successfully!';
            }),
            {
                loading: 'Saving profile...',
                success: (message) => message,
                error: (error) => error.message,
            }
        );
    };

    return (
        <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-6">
            <h3 className="text-xl font-medium mb-3">Personal Details</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="firstName">First Name</Label>
                    <Input id="firstName" {...register('firstName')} />
                    {errors.firstName && <p className="text-red-500 text-sm mt-1">{errors.firstName.message}</p>}
                </div>
                <div>
                    <Label htmlFor="lastName">Last Name</Label>
                    <Input id="lastName" {...register('lastName')} />
                    {errors.lastName && <p className="text-red-500 text-sm mt-1">{errors.lastName.message}</p>}
                </div>
                <div>
                    <Label htmlFor="email">Email</Label>
                    <Input id="email" type="email" {...register('email')} />
                    {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email.message}</p>}
                </div>
                <div>
                    <Label htmlFor="phone">Phone</Label>
                    <Input id="phone" {...register('phone')} />
                    {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone.message}</p>}
                </div>
                <div>
                    <Label htmlFor="gender">Gender</Label>
                    <Input id="gender" {...register('gender')} placeholder="e.g., Male, Female" />
                    {errors.gender && <p className="text-red-500 text-sm mt-1">{errors.gender.message}</p>}
                </div>
            </div>

            <h3 className="text-xl font-medium mt-8 mb-3">Business Details</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <Label htmlFor="tradingName">Trading Name</Label>
                    <Input id="tradingName" {...register('tradingName')} />
                    {errors.tradingName && <p className="text-red-500 text-sm mt-1">{errors.tradingName.message}</p>}
                </div>
                <div>
                    <Label htmlFor="businessType">Business Type</Label>
                    <Input id="businessType" {...register('businessType')} />
                    {errors.businessType && <p className="text-red-500 text-sm mt-1">{errors.businessType.message}</p>}
                </div>
                <div>
                    <Label htmlFor="registrationNumber">Registration Number</Label>
                    <Input id="registrationNumber" {...register('registrationNumber')} />
                    {errors.registrationNumber && <p className="text-red-500 text-sm mt-1">{errors.registrationNumber.message}</p>}
                </div>
                <div>
                    <Label htmlFor="taxPin">Tax PIN</Label>
                    <Input id="taxPin" {...register('taxPin')} />
                    {errors.taxPin && <p className="text-red-500 text-sm mt-1">{errors.taxPin.message}</p>}
                </div>
                <div>
                    <Label htmlFor="vatNumber">VAT Number</Label>
                    <Input id="vatNumber" {...register('vatNumber')} />
                    {errors.vatNumber && <p className="text-red-500 text-sm mt-1">{errors.vatNumber.message}</p>}
                </div>
                <div>
                    <Label htmlFor="country">Country</Label>
                    <Input id="country" {...register('country')} />
                    {errors.country && <p className="text-red-500 text-sm mt-1">{errors.country.message}</p>}
                </div>
                <div>
                    <Label htmlFor="physicalAddress">Physical Address</Label>
                    <Input id="physicalAddress" {...register('physicalAddress')} />
                    {errors.physicalAddress && <p className="text-red-500 text-sm mt-1">{errors.physicalAddress.message}</p>}
                </div>
                <div>
                    <Label htmlFor="website">Website</Label>
                    <Input id="website" {...register('website')} />
                    {errors.website && <p className="text-red-500 text-sm mt-1">{errors.website.message}</p>}
                </div>
            </div>

            <div className="flex justify-end mt-6">
                <Button type="submit" disabled={isSubmitting || !isDirty}>
                    {isSubmitting ? (
                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    ) : (
                        <Save className="mr-2 h-4 w-4" />
                    )}
                    Save Changes
                </Button>
            </div>
        </form>
    );
}