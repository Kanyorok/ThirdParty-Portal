'use client';

import { useState, useEffect, useCallback } from 'react';
import { useSession } from 'next-auth/react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import { Plus, Edit, Trash2, Save, X, Building2, CreditCard } from 'lucide-react';
import { motion, AnimatePresence, Variants } from 'framer-motion';

import { Button } from '@/components/common/button';
import { Input } from '@/components/common/input';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/common/form';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/common/table';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/common/alert-dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/common/select';
import { Badge } from '@/components/common/badge';
import { Currency } from '@/types/currencies';
import { Spinner } from '@/components/common/spinner'

const bankDetailSchema = z.object({
    id: z.number().optional(),
    bankName: z.string().min(1),
    branch: z.string().min(1),
    accountNumber: z.string().min(1).regex(/^\d+$/),
    currencyId: z.coerce.number().min(1).int(),
    swiftCode: z.string().nullable().optional().transform((e) => (e === '' ? null : e)),
});

type BankDetailInputs = z.infer<typeof bankDetailSchema>;

interface BankDetail {
    id: number;
    thirdPartyId: number;
    bankName: string;
    branch: string;
    accountNumber: string;
    currencyId: number;
    swiftCode: string | null;
    createdOn: string;
    modifiedOn: string | null;
    currency?: {
        id: number;
        name: string;
        code: string;
        symbol: string;
    };
}

const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: { staggerChildren: 0.06 }
    }
};

const itemVariants: Variants = {
    hidden: { opacity: 0, y: 8 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { type: 'spring', stiffness: 450, damping: 32 }
    },
    exit: { opacity: 0, y: -8, transition: { duration: 0.15 } }
};

const formVariants: Variants = {
    hidden: { opacity: 0, height: 0 },
    visible: { opacity: 1, height: 'auto', transition: { duration: 0.3 } },
    exit: { opacity: 0, height: 0, transition: { duration: 0.2 } }
};

export default function BankDetailsForm() {
    const { status } = useSession();

    const [bankDetails, setBankDetails] = useState<BankDetail[]>([]);
    const [currencies, setCurrencies] = useState<Currency[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [showInlineForm, setShowInlineForm] = useState(false);
    const [editingBankDetail, setEditingBankDetail] = useState<BankDetail | null>(null);
    const [isDeleteAlertOpen, setIsDeleteAlertOpen] = useState(false);
    const [bankDetailToDelete, setBankDetailToDelete] = useState<number | null>(null);

    const form = useForm<BankDetailInputs>({
        resolver: zodResolver(bankDetailSchema),
        defaultValues: {
            bankName: '',
            branch: '',
            accountNumber: '',
            currencyId: 1,
            swiftCode: '',
        },
    });

    const fetchCurrencies = useCallback(async () => {
        try {
            const r = await fetch('/api/currencies');
            if (!r.ok) throw new Error('Failed to fetch currencies');
            const { data } = await r.json();
            setCurrencies(data);
            if (!form.getValues('currencyId') && data.length > 0) {
                form.setValue('currencyId', data[0].id, { shouldValidate: true });
            }
        } catch (e: any) {
            toast.error(e.message);
        }
    }, [form]);

    const fetchBankDetails = useCallback(async () => {
        if (status !== 'authenticated') {
            setIsLoading(false);
            return;
        }

        setIsLoading(true);
        try {
            const r = await fetch(`/api/third-parties-bank-details`);
            const res = await r.json();
            if (!r.ok) throw new Error(res.message);
            setBankDetails(Array.isArray(res.data) ? res.data : []);
        } catch (e: any) {
            toast.error(e.message);
            setBankDetails([]);
        } finally {
            setIsLoading(false);
        }
    }, [status]);

    useEffect(() => {
        if (status === 'authenticated') {
            fetchCurrencies();
            fetchBankDetails();
        } else if (status === 'unauthenticated') {
            setIsLoading(false);
        }
    }, [status, fetchBankDetails, fetchCurrencies]);

    const openInlineFormForEdit = (detail?: BankDetail) => {
        setEditingBankDetail(detail || null);

        form.reset({
            bankName: detail?.bankName || '',
            branch: detail?.branch || '',
            accountNumber: detail?.accountNumber || '',
            currencyId: Number(detail?.currencyId ?? currencies[0]?.id ?? 1),
            swiftCode: detail?.swiftCode || '',
        });

        setShowInlineForm(true);
    };

    const closeInlineForm = () => {
        form.reset();
        setEditingBankDetail(null);
        setShowInlineForm(false);
    };

    const handleFormSubmit = async (data: BankDetailInputs) => {
        if (status !== 'authenticated') {
            toast.error('Unauthorized.');
            return;
        }

        const payload = data;
        const method = editingBankDetail ? 'PUT' : 'POST';
        const url = editingBankDetail ? `/api/third-parties-bank-details/${editingBankDetail.id}` : '/api/third-parties-bank-details';

        toast.promise(
            (async () => {
                const r = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const res = await r.json();

                if (!r.ok) {
                    const errorMsg = res.message || res.errors
                        ? Object.values(res.errors).flat().join(', ')
                        : 'Failed to save';
                    throw new Error(errorMsg);
                }

                await fetchBankDetails();
                closeInlineForm();
                return res.message || 'Saved successfully.';
            })(),
            {
                loading: editingBankDetail ? 'Updating...' : 'Saving...',
                success: (v) => v,
                error: (e) => e.message || 'An error occurred',
            }
        );
    };

    const confirmDelete = (id: number) => {
        setBankDetailToDelete(id);
        setIsDeleteAlertOpen(true);
    };

    const handleDelete = async () => {
        if (!bankDetailToDelete || status !== 'authenticated') return;

        toast.promise(
            (async () => {
                const r = await fetch(`/api/third-parties-bank-details/${bankDetailToDelete}`, {
                    method: 'DELETE',
                });
                const res = await r.json();
                if (!r.ok) throw new Error(res.message);
                await fetchBankDetails();
                return 'Deleted successfully.';
            })(),
            {
                loading: 'Deleting...',
                success: (v) => v,
                error: (e) => e.message,
            }
        );

        setIsDeleteAlertOpen(false);
        setBankDetailToDelete(null);
    };

    const formatAccountNumber = (acc: string) => acc.replace(/(.{4})/g, '$1 ').trim();

    if (status === 'loading') {
        return (
            <div className="flex justify-center items-center h-48">
                <Spinner className="animate-spin h-5 w-5 text-primary" />
            </div>
        );
    }

    return (
        <motion.div className="w-full space-y-4" variants={containerVariants} initial="hidden" animate="visible">
            <motion.div className="flex items-center justify-between pb-2 border-b" variants={itemVariants}>
                <p className="text-sm text-muted-foreground">Manage your banking information</p>
                {!showInlineForm && (
                    <Button size="sm" onClick={() => openInlineFormForEdit()}>
                        <Plus className="h-3.5 w-3.5 mr-1" /> Add Bank
                    </Button>
                )}
            </motion.div>

            <AnimatePresence>
                {showInlineForm && (
                    <motion.div variants={formVariants} initial="hidden" animate="visible" exit="exit" className="p-4 border rounded-lg bg-card">
                        <div className="flex items-center gap-2 mb-4 pb-2 border-b">
                            <Building2 className="h-4 w-4 text-primary" />
                            <h3 className="text-sm font-semibold">{editingBankDetail ? 'Edit Bank Detail' : 'Add Bank Detail'}</h3>
                        </div>

                        <Form {...form}>
                            <form onSubmit={form.handleSubmit(handleFormSubmit)} className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    <FormField name="bankName" control={form.control} render={({ field }) => (
                                        <FormItem>
                                            <FormLabel className="text-xs">Bank Name</FormLabel>
                                            <FormControl>
                                                <Input placeholder="e.g., KCB" {...field} />
                                            </FormControl>
                                            <FormMessage className="text-xs" />
                                        </FormItem>
                                    )} />

                                    <FormField name="branch" control={form.control} render={({ field }) => (
                                        <FormItem>
                                            <FormLabel className="text-xs">Branch</FormLabel>
                                            <FormControl>
                                                <Input placeholder="e.g., Main Branch" {...field} />
                                            </FormControl>
                                            <FormMessage className="text-xs" />
                                        </FormItem>
                                    )} />

                                    <FormField name="accountNumber" control={form.control} render={({ field }) => (
                                        <FormItem>
                                            <FormLabel className="text-xs">Account Number</FormLabel>
                                            <FormControl>
                                                <Input className="font-mono" placeholder="1234567890" {...field} />
                                            </FormControl>
                                            <FormMessage className="text-xs" />
                                        </FormItem>
                                    )} />

                                    <FormField name="currencyId" control={form.control} render={({ field }) => (
                                        <FormItem>
                                            <FormLabel className="text-xs">Currency</FormLabel>
                                            <Select value={String(field.value)} onValueChange={(v) => field.onChange(parseInt(v))}>
                                                <FormControl>
                                                    <SelectTrigger className="w-full">
                                                        <SelectValue placeholder="Select" />
                                                    </SelectTrigger>
                                                </FormControl>
                                                <SelectContent>
                                                    {currencies.map((c) => (
                                                        <SelectItem key={c.id} value={String(c.id)}>
                                                            <div className="flex items-center gap-2">
                                                                <Badge variant="outline" className="text-xs">{c.code}</Badge>
                                                                <span>{c.name}</span>
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <FormMessage className="text-xs" />
                                        </FormItem>
                                    )} />

                                    <FormField name="swiftCode" control={form.control} render={({ field }) => (
                                        <FormItem className="md:col-span-2">
                                            <FormLabel className="text-xs">SWIFT Code</FormLabel>
                                            <FormControl>
                                                <Input className="font-mono uppercase" placeholder="ABCDEFXX" {...field} value={field.value ?? ''} onChange={(e) => field.onChange(e.target.value.toUpperCase())} />
                                            </FormControl>
                                            <FormMessage className="text-xs" />
                                        </FormItem>
                                    )} />
                                </div>

                                <div className="flex justify-end gap-2 pt-2 border-t">
                                    <Button type="button" variant="outline" size="sm" onClick={closeInlineForm}>
                                        <X className="h-3.5 w-3.5 mr-1" /> Cancel
                                    </Button>
                                    <Button size="sm" disabled={form.formState.isSubmitting}>
                                        {form.formState.isSubmitting ? <Spinner className="animate-spin h-3.5 w-3.5 mr-1" /> : <Save className="h-3.5 w-3.5 mr-1" />}
                                        {editingBankDetail ? 'Update' : 'Save'}
                                    </Button>
                                </div>
                            </form>
                        </Form>
                    </motion.div>
                )}
            </AnimatePresence>

            <motion.div variants={itemVariants}>
                {isLoading ? (
                    <div className="flex justify-center items-center py-12 border rounded-lg">
                        <Spinner className="animate-spin h-5 w-5 text-primary" />
                    </div>
                ) : bankDetails.length === 0 ? (
                    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="border-2 border-dashed rounded-lg">
                        <div className="flex flex-col items-center justify-center py-12">
                            <div className="rounded-full bg-muted/50 p-3 mb-3">
                                <CreditCard className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="text-base font-medium mb-1">No bank details</h3>
                            <p className="text-sm text-muted-foreground mb-4">Add your first bank account</p>
                            <Button size="sm" onClick={() => openInlineFormForEdit()}>
                                <Plus className="h-3.5 w-3.5 mr-1" /> Add Bank Detail
                            </Button>
                        </div>
                    </motion.div>
                ) : (
                    <div className="border rounded-lg overflow-hidden bg-card">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/30">
                                    <TableHead className="text-xs font-semibold">Bank</TableHead>
                                    <TableHead className="text-xs font-semibold">Account</TableHead>
                                    <TableHead className="text-xs font-semibold">Currency</TableHead>
                                    <TableHead className="text-xs font-semibold">SWIFT</TableHead>
                                    <TableHead className="text-xs text-right font-semibold w-[100px]">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <AnimatePresence>
                                    {bankDetails.map((detail) => (
                                        <motion.tr key={detail.id} variants={itemVariants} initial="hidden" animate="visible" exit="exit" className="hover:bg-muted/20">
                                            <TableCell>{detail.bankName} – {detail.branch}</TableCell>
                                            <TableCell className="font-mono">{formatAccountNumber(detail.accountNumber)}</TableCell>
                                            <TableCell>{detail.currency?.code}</TableCell>
                                            <TableCell className="font-mono">{detail.swiftCode || '-'}</TableCell>
                                            <TableCell className="text-right flex items-center justify-end gap-2">
                                                <Button variant="ghost" size="icon" onClick={() => openInlineFormForEdit(detail)}>
                                                    <Edit className="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" onClick={() => confirmDelete(detail.id)}>
                                                    <Trash2 className="h-4 w-4 text-red-500" />
                                                </Button>
                                            </TableCell>
                                        </motion.tr>
                                    ))}
                                </AnimatePresence>
                            </TableBody>
                        </Table>
                    </div>
                )}
            </motion.div>

            <AlertDialog open={isDeleteAlertOpen} onOpenChange={setIsDeleteAlertOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Delete Bank Detail</AlertDialogTitle>
                        <AlertDialogDescription>This action cannot be undone.</AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction onClick={handleDelete}>Delete</AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </motion.div>
    );
}
