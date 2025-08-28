'use client';

import React, { useState, useEffect } from 'react';
import { useSession } from 'next-auth/react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { toast } from 'sonner';
import {
    Plus,
    Edit,
    Trash2,
    Loader2,
    Save,
    X,
    Building2,
    CreditCard,
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/common/table';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/common/alert-dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/common/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/card';
import { Badge } from '@/components/common/badge';
import { Currency } from '@/data/currencies'; // Assuming this path is correct

const bankDetailSchema = z.object({
    id: z.number().optional(),
    bankName: z.string().min(1, { message: "Bank Name is required." }),
    branch: z.string().min(1, { message: "Branch is required." }),
    accountNumber: z.string()
        .min(1, { message: "Account Number is required." })
        .regex(/^\d+$/, { message: "Account Number must contain only digits." }),
    currencyId: z.coerce.number()
        .min(1, { message: "Currency is required." })
        .int({ message: "Currency ID must be an integer." }),
    swiftCode: z.string()
        .nullable()
        .optional()
        .transform(e => e === "" ? null : e),
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
    hidden: { opacity: 0, y: 20 },
    visible: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.4,
            staggerChildren: 0.08,
            when: "beforeChildren"
        }
    },
    exit: {
        opacity: 0,
        y: -20,
        transition: { duration: 0.3 }
    }
};

const itemVariants: Variants = {
    hidden: { opacity: 0, x: -20 },
    visible: {
        opacity: 1,
        x: 0,
        transition: { duration: 0.3 }
    },
    exit: {
        opacity: 0,
        x: 20,
        transition: { duration: 0.2 }
    }
};

const formVariants: Variants = {
    hidden: { opacity: 0, height: 0, scale: 0.98 },
    visible: {
        opacity: 1,
        height: 'auto',
        scale: 1,
        transition: {
            duration: 0.4,
            ease: [0.22, 1, 0.36, 1]
        }
    },
    exit: {
        opacity: 0,
        height: 0,
        scale: 0.98,
        transition: {
            duration: 0.3,
            ease: [0.22, 1, 0.36, 1]
        }
    }
};

export default function BankDetailsForm() {
    const { data: session, status } = useSession();
    const [bankDetails, setBankDetails] = useState<BankDetail[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [showInlineForm, setShowInlineForm] = useState(false);
    const [editingBankDetail, setEditingBankDetail] = useState<BankDetail | null>(null);
    const [isDeleteAlertOpen, setIsDeleteAlertOpen] = useState(false);
    const [bankDetailToDelete, setBankDetailToDelete] = useState<number | null>(null);
    const [currencies, setCurrencies] = useState<Currency[]>([]);

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

    const thirdPartyId = session?.user?.thirdParty?.id;

    const fetchCurrencies = async () => {
        try {
            const response = await fetch('/api/currencies');
            if (!response.ok) {
                throw new Error('Failed to fetch currencies.');
            }
            const { data }: { data: Currency[] } = await response.json();
            setCurrencies(data);

            if (data.length > 0 && !form.getValues('currencyId')) {
                form.setValue('currencyId', data[0].id, { shouldValidate: true });
            }
        } catch (error: any) {
            toast.error(error.message || 'Error fetching currencies.');
            console.error('Fetch currencies error:', error);
        }
    };

    const fetchBankDetails = async () => {
        if (status === 'loading' || !thirdPartyId) {
            setIsLoading(false);
            return;
        }

        setIsLoading(true);
        try {
            const response = await fetch(`/api/third-parties-bank-details?thirdPartyId=${thirdPartyId}`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to fetch bank details.');
            }
            const responseData = await response.json();
            setBankDetails(Array.isArray(responseData.data) ? responseData.data : []);
        } catch (error: any) {
            toast.error(error.message || 'Error fetching bank details.');
            console.error('Fetch bank details error:', error);
            setBankDetails([]);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        if (status === 'authenticated') {
            fetchCurrencies();
            fetchBankDetails();
        } else if (status === 'unauthenticated') {
            setIsLoading(false);
        }
    }, [thirdPartyId, status]);

    const openInlineFormForEdit = (detail?: BankDetail) => {
        setEditingBankDetail(detail || null);

        let initialCurrencyId: number;
        if (detail?.currencyId) {
            initialCurrencyId = detail.currencyId;
        } else if (currencies.length > 0) {
            initialCurrencyId = currencies[0].id;
        } else {
            initialCurrencyId = 1; // Fallback to a default ID: USD
        }

        form.reset({
            bankName: detail?.bankName || '',
            branch: detail?.branch || '',
            accountNumber: detail?.accountNumber || '',
            currencyId: initialCurrencyId,
            swiftCode: detail?.swiftCode || '',
        });
        setShowInlineForm(true);
    };

    const closeInlineForm = () => {
        setShowInlineForm(false);
        setEditingBankDetail(null);
        form.reset();
    };

    const handleFormSubmit = async (data: BankDetailInputs) => {
        if (!thirdPartyId || !session?.accessToken) {
            toast.error('Authentication or Third Party ID missing. Cannot save bank details.');
            return;
        }

        const payload = { ...data, thirdPartyId };

        toast.promise(
            (async () => {
                const method = editingBankDetail ? 'PUT' : 'POST';
                const url = editingBankDetail
                    ? `/api/third-parties-bank-details/${editingBankDetail.id}`
                    : '/api/third-parties-bank-details';

                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${session.accessToken}`,
                    },
                    body: JSON.stringify(payload),
                });

                const responseData = await response.json();

                if (!response.ok) {
                    const errorMessage = responseData.message || 'Failed to save bank detail.';
                    const errorDetails = responseData.errors
                        ? Object.values(responseData.errors).flat().join('\n')
                        : '';
                    throw new Error(`${errorMessage}\n${errorDetails}`);
                }
                await fetchBankDetails();
                closeInlineForm();
                return responseData.message || 'Bank detail saved successfully!';
            })(),
            {
                loading: editingBankDetail ? 'Updating bank detail...' : 'Adding bank detail...',
                success: (message) => message,
                error: (error) => error.message,
            }
        );
    };

    const confirmDelete = (id: number) => {
        setBankDetailToDelete(id);
        setIsDeleteAlertOpen(true);
    };

    const handleDelete = async () => {
        if (!bankDetailToDelete || !session?.accessToken) {
            toast.error("Authentication required to delete bank details.");
            return;
        }

        setIsDeleteAlertOpen(false);
        toast.promise(
            (async () => {
                const response = await fetch(`/api/third-parties-bank-details/${bankDetailToDelete}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${session.accessToken}` },
                });

                if (!response.ok) {
                    const responseData = await response.json();
                    throw new Error(responseData.message || 'Failed to delete bank detail.');
                }
                await fetchBankDetails();
                return 'Bank detail deleted successfully!';
            })(),
            {
                loading: 'Deleting bank detail...',
                success: (message) => message,
                error: (error) => error.message,
            }
        );
    };

    const formatAccountNumber = (accountNumber: string) => {
        return accountNumber.replace(/(.{4})/g, '$1 ').trim();
    };

    if (status === 'loading') {
        return (
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                className="flex justify-center items-center h-48 w-full max-w-4xl mx-auto p-6"
            >
                <div className="flex items-center space-x-3">
                    <Loader2 className="animate-spin h-6 w-6 text-primary" />
                    <p className="text-muted-foreground font-medium">Loading user session...</p>
                </div>
            </motion.div>
        );
    }

    return (
        <motion.div
            className="container mx-auto p-6 space-y-8 max-w-4xl"
            initial="hidden"
            animate="visible"
            variants={containerVariants}
        >
            <motion.div
                className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                variants={itemVariants}
            >
                <div className="space-y-1">
                    <h1 className="text-3xl font-bold tracking-tight text-foreground">
                        Banking Details
                    </h1>
                </div>
                <Button
                    onClick={() => openInlineFormForEdit()}
                    className="shrink-0 shadow-sm"
                    size="lg"
                >
                    <Plus className="mr-2 h-4 w-4"   />
                    Add Bank Detail
                </Button>
            </motion.div>

            <AnimatePresence mode="wait">
                {showInlineForm && (
                    <motion.div
                        variants={formVariants}
                        initial="hidden"
                        animate="visible"
                        exit="exit"
                        className="overflow-hidden"
                    >
                        <Card className="shadow-sm w-full">
                            <CardHeader className="pb-4">
                                <CardTitle className="flex items-center gap-2 text-xl">
                                    <Building2 className="h-5 w-5 text-primary" />
                                    {editingBankDetail ? 'Edit Bank Detail' : 'Add New Bank Detail'}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Form {...form}>
                                    <form
                                        onSubmit={form.handleSubmit(handleFormSubmit)}
                                        className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6"
                                    >
                                        <FormField
                                            control={form.control}
                                            name="bankName"
                                            render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm font-medium">
                                                        Bank Name
                                                    </FormLabel>
                                                    <FormControl>
                                                        <Input
                                                            placeholder="e.g., Central Bank"
                                                            className="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                                            {...field}
                                                        />
                                                    </FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )}
                                        />

                                        <FormField
                                            control={form.control}
                                            name="branch"
                                            render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm font-medium">
                                                        Branch
                                                    </FormLabel>
                                                    <FormControl>
                                                        <Input
                                                            placeholder="e.g., Main Branch"
                                                            className="transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                                                            {...field}
                                                        />
                                                    </FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )}
                                        />

                                        <FormField
                                            control={form.control}
                                            name="accountNumber"
                                            render={({ field }) => (
                                                <FormItem>
                                                    <FormLabel className="text-sm font-medium">
                                                        Account Number
                                                    </FormLabel>
                                                    <FormControl>
                                                        <Input
                                                            placeholder="e.g., 1234567890"
                                                            className="transition-all duration-200 focus:ring-2 focus:ring-primary/20 font-mono"
                                                            {...field}
                                                        />
                                                    </FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )}
                                        />

                                        <FormField
                                            control={form.control}
                                            name="currencyId"
                                            render={({ field }) => {
                                                const selectValue = field.value !== undefined && field.value !== null
                                                    ? String(field.value)
                                                    : String(currencies.length > 0 ? currencies[0].id : 1);

                                                return (
                                                    <FormItem>
                                                        <FormLabel className="text-sm font-medium">
                                                            Currency
                                                        </FormLabel>
                                                        <Select
                                                            onValueChange={value => field.onChange(parseInt(value))}
                                                            value={selectValue}
                                                            disabled={currencies.length === 0}
                                                        >
                                                            <FormControl>
                                                                <SelectTrigger className="transition-all duration-200 focus:ring-2 focus:ring-primary/20">
                                                                    <SelectValue placeholder={
                                                                        currencies.length === 0
                                                                            ? "Loading currencies..."
                                                                            : "Select a currency"
                                                                    } />
                                                                </SelectTrigger>
                                                            </FormControl>
                                                            <SelectContent>
                                                                {currencies.length > 0 ? (
                                                                    currencies.map((currency) => (
                                                                        <SelectItem
                                                                            key={currency.id}
                                                                            value={String(currency.id)}
                                                                            className="flex items-center justify-between"
                                                                        >
                                                                            <span className="flex items-center gap-2">
                                                                                <Badge variant="outline" className="text-xs">
                                                                                    {currency.code}
                                                                                </Badge>
                                                                                {currency.name}
                                                                            </span>
                                                                        </SelectItem>
                                                                    ))
                                                                ) : (
                                                                    <SelectItem value="loading" disabled>
                                                                        Loading currencies...
                                                                    </SelectItem>
                                                                )}
                                                            </SelectContent>
                                                        </Select>
                                                        <FormMessage />
                                                    </FormItem>
                                                );
                                            }}
                                        />

                                        <FormField
                                            control={form.control}
                                            name="swiftCode"
                                            render={({ field }) => (
                                                <FormItem className="md:col-span-2">                                                     <FormLabel className="text-sm font-medium">
                                                    SWIFT Code
                                                    <span className="text-muted-foreground ml-1">(Optional)</span>
                                                </FormLabel>
                                                    <FormControl>
                                                        <Input
                                                            placeholder="e.g., ABCDEFXX"
                                                            className="transition-all duration-200 focus:ring-2 focus:ring-primary/20 font-mono uppercase"
                                                            {...field}
                                                            value={field.value ?? ''}
                                                            onChange={(e) => field.onChange(e.target.value.toUpperCase())}
                                                        />
                                                    </FormControl>
                                                    <FormMessage />
                                                </FormItem>
                                            )}
                                        />

                                        <div className="md:col-span-2 flex justify-end space-x-3 pt-4 border-t mt-4">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={closeInlineForm}
                                                disabled={form.formState.isSubmitting}
                                                size="lg"
                                            >
                                                <X className="mr-2 h-4 w-4" />
                                                Cancel
                                            </Button>
                                            <Button
                                                type="submit"
                                                disabled={form.formState.isSubmitting}
                                                size="lg"
                                                className="min-w-[140px]"
                                            >
                                                {form.formState.isSubmitting ? (
                                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                ) : (
                                                    <Save className="mr-2 h-4 w-4" />
                                                )}
                                                {editingBankDetail ? 'Update' : 'Save'} Detail
                                            </Button>
                                        </div>
                                    </form>
                                </Form>
                            </CardContent>
                        </Card>
                    </motion.div>
                )}
            </AnimatePresence>

            <motion.div variants={itemVariants}>
                {isLoading ? (
                    <Card className="shadow-sm">
                        <CardContent className="flex justify-center items-center py-16">
                            <div className="flex items-center space-x-3">
                                <Loader2 className="animate-spin h-6 w-6 text-primary" />
                                <p className="text-muted-foreground font-medium">Loading bank details...</p>
                            </div>
                        </CardContent>
                    </Card>
                ) : bankDetails.length === 0 ? (
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ delay: 0.2 }}
                    >
                        <Card className="shadow-sm border-dashed border-2">
                            <CardContent className="flex flex-col items-center justify-center py-16 text-center">
                                <div className="rounded-full bg-muted p-4 mb-4">
                                    <CreditCard className="h-8 w-8 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-semibold mb-2">No bank details yet</h3>
                                <p className="text-muted-foreground mb-6 max-w-sm">
                                    Get started by adding your first bank account to manage payments and transactions.
                                </p>
                                <Button
                                    onClick={() => openInlineFormForEdit()}
                                    className="shadow-sm"
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add Your First Bank Detail
                                </Button>
                            </CardContent>
                        </Card>
                    </motion.div>
                ) : (
                    <Card className="shadow-sm w-full">
                        <CardContent className="p-0">
                            <div className="rounded-lg overflow-hidden">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/50">
                                            <TableHead className="font-semibold">Bank Information</TableHead>
                                            <TableHead className="font-semibold">Account Details</TableHead>
                                            <TableHead className="font-semibold">Currency</TableHead>
                                            <TableHead className="font-semibold">SWIFT Code</TableHead>
                                            <TableHead className="text-right font-semibold w-[120px]">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <AnimatePresence mode="popLayout">
                                            {bankDetails.map((detail, index) => (
                                                <motion.tr
                                                    key={detail.id}
                                                    variants={itemVariants}
                                                    initial="hidden"
                                                    animate="visible"
                                                    exit="exit"
                                                    transition={{ delay: index * 0.05 }}
                                                    className="group hover:bg-muted/30 transition-colors duration-200"
                                                >
                                                    <TableCell>
                                                        <div className="space-y-1">
                                                            <div className="font-semibold text-foreground flex items-center gap-2">
                                                                {detail.bankName}
                                                            </div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {detail.branch}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="font-mono text-sm bg-muted/50 px-2 py-1 rounded-md inline-block">
                                                            {formatAccountNumber(detail.accountNumber)}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        {detail.currency ? (
                                                            <Badge variant="secondary" className="gap-1">
                                                                {detail.currency.code} - {detail.currency.name}
                                                            </Badge>
                                                        ) : (
                                                            <Badge variant="outline">
                                                                ID: {detail.currencyId}
                                                            </Badge>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        {detail.swiftCode ? (
                                                            <code className="bg-muted px-2 py-1 rounded text-sm font-mono">
                                                                {detail.swiftCode}
                                                            </code>
                                                        ) : (
                                                            <span className="text-muted-foreground text-sm">- N/A -</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <div className="flex justify-end gap-2">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() => openInlineFormForEdit(detail)}
                                                                aria-label={`Edit ${detail.bankName}`}
                                                            >
                                                                <Edit className="h-4 w-4" />
                                                            </Button>
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                onClick={() => confirmDelete(detail.id)}
                                                                aria-label={`Delete ${detail.bankName}`}
                                                            >
                                                                <Trash2 className="h-4 w-4 text-destructive" />
                                                            </Button>
                                                        </div>
                                                    </TableCell>
                                                </motion.tr>
                                            ))}
                                        </AnimatePresence>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </motion.div>

            <AlertDialog open={isDeleteAlertOpen} onOpenChange={setIsDeleteAlertOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                        <AlertDialogDescription>
                            This action cannot be undone. This will permanently delete this bank detail
                            from our servers.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={form.formState.isSubmitting}>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={handleDelete}
                            className="bg-destructive hover:bg-destructive/90"
                            disabled={form.formState.isSubmitting}
                        >
                            {form.formState.isSubmitting ? (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            ) : null}
                            Delete
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </motion.div>
    );
}