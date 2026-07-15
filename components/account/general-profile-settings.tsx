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
import { motion } from 'framer-motion';
import { parseJsonResponse } from '@/lib/parse-json-response';

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
    const { status } = useSession();

    const { register, handleSubmit, reset, formState: { errors, isSubmitting, isDirty } } = useForm<GeneralProfileInputs>({
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
        if (status !== 'authenticated') {
            toast.error("Authentication required to save changes.");
            return;
        }

        toast.promise(
            fetch('/api/third-party-profile', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(data),
            }).then(async res => {
                const responseData = await parseJsonResponse<{ message?: string; errors?: Record<string, string[]> }>(res);
                if (!res.ok) {
                    const errorMessage = responseData?.message || 'Failed to update profile.';
                    const errorDetails = responseData?.errors ? Object.values(responseData.errors).flat().join('\n') : '';
                    throw new Error(`${errorMessage}\n${errorDetails}`);
                }
                reset(data);
                return responseData?.message || 'Profile updated successfully.';
            }),
            { loading: 'Saving profile', success: m => m, error: e => e.message }
        );
    };

    const sections = [
        {
            title: 'Personal Details', fields: [
                { label: 'First Name', id: 'firstName' },
                { label: 'Last Name', id: 'lastName' },
                { label: 'Email', id: 'email', type: 'email' },
                { label: 'Phone', id: 'phone' },
                { label: 'Gender', id: 'gender', placeholder: 'e.g., Male, Female' },
            ]
        },
        {
            title: 'Business Details', fields: [
                { label: 'Trading Name', id: 'tradingName' },
                { label: 'Business Type', id: 'businessType' },
                { label: 'Registration Number', id: 'registrationNumber' },
                { label: 'Tax PIN', id: 'taxPin' },
                { label: 'VAT Number', id: 'vatNumber' },
                { label: 'Country', id: 'country' },
                { label: 'Physical Address', id: 'physicalAddress' },
                { label: 'Website', id: 'website' },
            ]
        }
    ];

    return (
        <motion.form
            onSubmit={handleSubmit(handleFormSubmit)}
            className="space-y-8 max-w-4xl mx-auto p-6 bg-white dark:bg-muted rounded-xl shadow-lg"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4 }}
        >
            {sections.map(section => (
                <div key={section.title} className="space-y-4">
                    <h3 className="text-xl font-semibold text-foreground">{section.title}</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {section.fields.map(field => (
                            <div key={field.id} className="flex flex-col">
                                <Label htmlFor={field.id}>{field.label}</Label>
                                <Input
                                    id={field.id}
                                    type={field.type || 'text'}
                                    placeholder={field.placeholder}
                                    {...register(field.id as keyof GeneralProfileInputs)}
                                    className={errors[field.id as keyof GeneralProfileInputs] ? 'border-red-500' : ''}
                                />
                                {errors[field.id as keyof GeneralProfileInputs] && (
                                    <p className="text-red-500 text-sm mt-1">
                                        {errors[field.id as keyof GeneralProfileInputs]?.message as string}
                                    </p>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            ))}

            <div className="flex justify-end">
                <Button type="submit" disabled={isSubmitting || !isDirty} className="flex items-center gap-2">
                    {isSubmitting ? <Loader2 className="animate-spin h-4 w-4" /> : <Save className="h-4 w-4" />}
                    Save Changes
                </Button>
            </div>
        </motion.form>
    );
}
