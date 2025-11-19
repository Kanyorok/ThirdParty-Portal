'use client';

import React, { useState, useEffect } from 'react';
import { useSession } from 'next-auth/react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { motion, AnimatePresence } from 'framer-motion';
import { toast } from 'sonner';
import { Badge } from '@/components/common/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/card';
import { Loader2, Building2, FileText, Percent, MapPin, Mail, Phone, Globe, CheckCircle2, AlertCircle, Clock } from 'lucide-react';

export enum BusinessTypeEnum {
    Sole = 1,
    Partnership,
    LLC,
    Corporation,
    NGO,
}

const businessTypeOptions = [
    { value: BusinessTypeEnum.Sole, label: 'Sole Proprietorship' },
    { value: BusinessTypeEnum.Partnership, label: 'Partnership' },
    { value: BusinessTypeEnum.LLC, label: 'Limited Liability Company' },
    { value: BusinessTypeEnum.Corporation, label: 'Corporation' },
    { value: BusinessTypeEnum.NGO, label: 'NGO' },
];

const thirdPartySchema = z.object({
    thirdPartyName: z.string().min(1),
    tradingName: z.string().nullable().optional().transform(e => e === '' ? null : e),
    businessType: z.coerce.number().min(1).int(),
    registrationNumber: z.string().min(1),
    taxPIN: z.string().min(1),
    vatNumber: z.string().nullable().optional().transform(e => e === '' ? null : e),
    country: z.string().min(1),
    physicalAddress: z.string().min(1),
    email: z.string().email(),
    phone: z.string().regex(/^\+?[0-9()\s-]+$/),
    website: z.string().url().nullable().optional().transform(e => e === '' ? null : e),
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
    status: number;
    approvalStatus: string;
}

const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.4 } },
};

const FieldDisplay: React.FC<{ label: string; value: string | null | undefined; icon?: React.ElementType }> = ({ label, value, icon: Icon }) => (
    <div className="flex flex-col gap-1">
        <p className="text-sm font-medium text-muted-foreground">{label}</p>
        <div className="flex items-center gap-2 p-3 bg-white dark:bg-muted rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            {Icon && <Icon className="h-5 w-5 text-muted-foreground" />}
            <p className="font-semibold text-foreground break-words">{value || 'N/A'}</p>
        </div>
    </div>
);

export default function ThirdPartyDashboard() {
    const { data: session, status } = useSession();
    const [details, setDetails] = useState<ThirdPartyProfile | null>(null);
    const [isEditing, setIsEditing] = useState(false);
    const [loading, setLoading] = useState(true);

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

    const fetchDetails = async () => {
        if (!session?.user?.thirdParty?.id) return setLoading(false);
        setLoading(true);
        try {
            const res = await fetch(`/api/third-party-details`);
            if (!res.ok) throw new Error((await res.json()).message || 'Failed to fetch');
            const data: ThirdPartyProfile = await res.json();
            setDetails(data);
            form.reset({ ...data });
        } catch (err: any) {
            toast.error(err.message || 'Error fetching data');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { if (status === 'authenticated') fetchDetails(); }, [status]);

    const handleSubmit = async (data: ThirdPartyInputs) => {
        if (!details) return;
        toast.promise(
            (async () => {
                const res = await fetch(`/api/third-party-details`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${session?.accessToken}` },
                    body: JSON.stringify(data),
                });
                const resData = await res.json();
                if (!res.ok) throw new Error(resData.message || 'Failed to update');
                await fetchDetails();
                setIsEditing(false);
                return 'Updated successfully!';
            })(),
            { loading: 'Saving...', success: m => m, error: e => e.message }
        );
    };

    const cancelEdit = () => { setIsEditing(false); if (details) form.reset({ ...details }); };

    const approvalBadge = (status: string) => {
        switch (status) {
            case 'A': return { label: 'Approved', icon: CheckCircle2, color: 'bg-green-500/10 text-green-500' };
            case 'R': return { label: 'Rejected', icon: AlertCircle, color: 'bg-red-500/10 text-red-500' };
            default: return { label: 'Pending', icon: Clock, color: 'bg-yellow-500/10 text-yellow-500' };
        }
    };

    const activeBadge = (status: number) => status === 1 ? { label: 'Active', color: 'bg-blue-500/10 text-blue-500' } : { label: 'Inactive', color: 'bg-gray-500/10 text-gray-500' };

    if (loading) return <div className="flex justify-center items-center h-64"><Loader2 className="animate-spin h-12 w-12 text-primary" /></div>;
    if (!details) return <div className="flex flex-col items-center justify-center h-64 text-muted-foreground">No company information found.</div>;

    return (
        <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
            <AnimatePresence>
                <motion.div variants={cardVariants} initial="hidden" animate="visible">
                    <Card className="hover:shadow-lg transition-shadow duration-300">
                        <CardHeader><CardTitle className="flex items-center gap-2"><Building2 />General Information</CardTitle></CardHeader>
                        <CardContent className="space-y-4">
                            <FieldDisplay label="Legal Name" value={details.thirdPartyName} icon={Building2} />
                            <FieldDisplay label="Trading Name" value={details.tradingName} icon={Building2} />
                            <FieldDisplay label="Business Type" value={businessTypeOptions.find(b => b.value === details.businessType)?.label} />
                        </CardContent>
                    </Card>
                </motion.div>
                <motion.div variants={cardVariants} initial="hidden" animate="visible">
                    <Card className="hover:shadow-lg transition-shadow duration-300">
                        <CardHeader><CardTitle className="flex items-center gap-2"><FileText />Registration & Tax</CardTitle></CardHeader>
                        <CardContent className="space-y-4">
                            <FieldDisplay label="Registration Number" value={details.registrationNumber} icon={FileText} />
                            <FieldDisplay label="Tax PIN" value={details.taxPIN} icon={Percent} />
                            <FieldDisplay label="VAT Number" value={details.vatNumber} icon={Percent} />
                        </CardContent>
                    </Card>
                </motion.div>
                <motion.div variants={cardVariants} initial="hidden" animate="visible">
                    <Card className="hover:shadow-lg transition-shadow duration-300">
                        <CardHeader><CardTitle className="flex items-center gap-2"><MapPin />Contact & Location</CardTitle></CardHeader>
                        <CardContent className="space-y-4">
                            <FieldDisplay label="Country" value={details.country} icon={MapPin} />
                            <FieldDisplay label="Address" value={details.physicalAddress} icon={MapPin} />
                            <FieldDisplay label="Email" value={details.email} icon={Mail} />
                            <FieldDisplay label="Phone" value={details.phone} icon={Phone} />
                            <FieldDisplay label="Website" value={details.website} icon={Globe} />
                        </CardContent>
                    </Card>
                </motion.div>
                <motion.div variants={cardVariants} initial="hidden" animate="visible">
                    <Card className="hover:shadow-lg transition-shadow duration-300">
                        <CardHeader><CardTitle className="flex items-center gap-2">Status</CardTitle></CardHeader>
                        <CardContent className="flex gap-3 flex-wrap">
                            {(() => { const badge = approvalBadge(details.approvalStatus); const Icon = badge.icon; return <Badge className={`flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold ${badge.color}`}><Icon className="h-4 w-4" />{badge.label}</Badge>; })()}
                            <Badge className={`flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-semibold ${activeBadge(details.status).color}`}>{activeBadge(details.status).label}</Badge>
                        </CardContent>
                    </Card>
                </motion.div>
            </AnimatePresence>
        </div>
    );
}
