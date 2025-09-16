"use client"

import { useEffect, useMemo, useState, useCallback } from "react"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { Loader2, CheckCircle2, AlertCircle, Clock, RefreshCw, Info, X, Send, Users, Calendar, Frown, XCircle, CheckCheck, Check, InfoIcon } from "lucide-react"
import { Button } from "@/components/common/button"
import { Label } from "@/components/common/label"
import { Select, SelectTrigger, SelectContent, SelectItem, SelectValue } from "@/components/common/select"
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger, SheetDescription } from "@/components/common/sheet"
import { toast } from "sonner"
import { useSession } from "next-auth/react"
import { getRounds, getSupplierCategories, submitApplicationSafe } from "@/lib/api-base"
import { cn } from "@/lib/utils"

// Local round shape used inside the form (separate from global Round but can overlap)
type Round = {
    id: string;
    name: string;
    status: string;
    deadline?: string;
    applicantCount?: number;
    hasApplied?: boolean;
    applicationId?: string;
};

type SupplierCategory = {
    id: string;
    name: string;
    is_active: boolean;
};

type LoadingState = "idle" | "loading" | "success" | "error" | "submitting" | "warning";

const FormSchema = z.object({
    roundId: z.string().min(1, "Please select a round to continue"),
    categoryIds: z.array(z.string()).min(1, "Please select at least one category"),
});

type FormValues = z.infer<typeof FormSchema>;

const RoundApiItemSchema = z.object({
    roundID: z.number().optional(),
    id: z.union([z.string(), z.number()]).optional(),
    title: z.string().optional(),
    name: z.string().optional(),
    status: z.union([
        z.string(),
        z.object({ value: z.string(), label: z.string().optional() }),
    ]).optional(),
    startDate: z.string().optional(),
    endDate: z.string().optional(),
    deadline: z.string().optional(),
    applicantCount: z.number().optional(),
});

const RoundsApiResponseSchema = z.object({
    data: z.array(RoundApiItemSchema).optional(),
});

const CategoryApiItemSchema = z.object({
    supplierCategoryID: z.number(),
    categoryName: z.string(),
    description: z.string().optional(),
    is_active: z.boolean().optional(),
});

const CategoriesApiResponseSchema = z.object({
    data: z.array(CategoryApiItemSchema).optional(),
});

const StatusBadge = ({ status, className = "" }: { status: string; className?: string }) => {
    const getStatusConfig = (s: string) => {
        switch (s.toLowerCase()) {
            case "o":
            case "open":
                return { label: "Open", className: "bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800 animate-pulse", icon: <CheckCircle2 className="w-3 h-3" /> };
            case "cl":
            case "closed":
                return { label: "Closed", className: "bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800", icon: <X className="w-3 h-3" /> };
            case "pending":
                return { label: "Pending", className: "bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800", icon: <Clock className="w-3 h-3" /> };
            default:
                return { label: s, className: "bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-900/20 dark:text-gray-400 dark:border-gray-800", icon: <Info className="w-3 h-3" /> };
        }
    };
    const config = getStatusConfig(status);
    return (
        <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full border transition-all duration-200 ${config.className} ${className}`} role="status" aria-label={`Status: ${config.label}`}>
            {config.icon}
            {config.label}
        </span>
    );
};

const LoadingSkeleton = () => (
    <div
        className="w-full flex flex-col gap-4 p-4 rounded-lg border bg-card text-card-foreground shadow-sm animate-pulse"
        role="status"
        aria-live="polite"
        aria-label="Loading ... "
    >
        {[...Array(3)].map((_, index) => (
            <div key={index} className="flex items-center space-x-4 p-4 border rounded-md">
                <div className="flex-1 space-y-2">
                    <div className="h-4 bg-gray-300 rounded w-full dark:bg-gray-700" />
                    <div className="h-4 bg-gray-200 rounded w-4/5 dark:bg-gray-800" />
                </div>
                <div className="w-16 h-8 bg-gray-200 rounded-full dark:bg-gray-800" />
            </div>
        ))}
    </div>
);

const EmptyState = ({ onRetry }: { onRetry: () => void }) => (
    <div className="flex flex-col items-center justify-center p-8 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300">
        <div className="flex items-center justify-center w-12 h-12 mb-4 text-blue-500 bg-blue-100 rounded-full dark:bg-blue-900/50 dark:text-blue-400">
            <Frown className="w-6 h-6" />
        </div>
        <h3 className="text-xl font-bold tracking-tight text-gray-900 dark:text-gray-100 mb-2">
            Nothing to see here
        </h3>
        <p className="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-6">
            No open prequalification rounds are available at the moment. Please check back later or try refreshing.
        </p>
        <Button
            variant="ghost"
            onClick={onRetry}
            className="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
            <RefreshCw className="w-4 h-4 mr-2" />
            Try Again
        </Button>
    </div>
);

const ErrorState = ({ error, onRetry }: { error: string; onRetry: () => void }) => (
    <div
        className="flex flex-col items-center justify-center p-8 text-center bg-red-50 rounded-xl border-2 border-dashed border-red-200 dark:bg-red-950 dark:border-red-800"
        role="alert"
    >
        <div className="flex items-center justify-center w-12 h-12 mb-4 text-red-500 bg-red-100 rounded-full dark:bg-red-900/50 dark:text-red-400">
            <XCircle className="w-6 h-6" />
        </div>
        <h3 className="text-xl font-bold tracking-tight text-red-900 dark:text-red-100 mb-2">
            Something went wrong
        </h3>
        <p className="text-sm text-red-600 dark:text-red-400 max-w-sm mb-6">
            {error || "An unexpected error occurred. Please try again later."}
        </p>
        <Button
            variant="ghost"
            onClick={onRetry}
            className="text-red-600 dark:text-red-300 hover:text-red-700 dark:hover:text-red-400 transition-colors"
        >
            <RefreshCw className="w-4 h-4 mr-2" />
            Try Again
        </Button>
    </div>
);

const WarningState = ({ message }: { message: string }) => (
    <div
        className="flex flex-col items-center justify-center p-8 text-center bg-yellow-50 rounded-xl border-2 border-dashed border-yellow-200 dark:bg-yellow-950 dark:border-yellow-800"
        role="alert"
    >
        <div className="flex items-center justify-center w-12 h-12 mb-4 text-yellow-500 bg-yellow-100 rounded-full dark:bg-yellow-900/50 dark:text-yellow-400">
            <InfoIcon className="w-6 h-6" />
        </div>
        <h3 className="text-xl font-bold tracking-tight text-yellow-900 dark:text-yellow-100 mb-2">
            Already Applied
        </h3>
        <p className="text-sm text-yellow-600 dark:text-yellow-400 max-w-sm mb-6">
            {message}
        </p>
    </div>
);

const SuccessState = ({ onClose }: { onClose: () => void }) => (
    <div
        className="flex flex-col items-center justify-center p-8 text-center bg-green-50 rounded-xl border-2 border-dashed border-green-200 dark:bg-green-950 dark:border-green-800"
        role="status"
    >
        <div className="flex items-center justify-center w-16 h-16 mb-6 text-green-500 bg-green-100 rounded-full dark:bg-green-900/50 dark:text-green-400">
            <CheckCheck className="w-8 h-8" />
        </div>
        <h3 className="text-2xl font-bold tracking-tight text-green-900 dark:text-green-100 mb-2">
            Application Submitted! 🎉
        </h3>
        <p className="text-sm text-green-600 dark:text-green-400 max-w-sm mb-6">
            Your prequalification application has been successfully submitted. We&apos;ll send you an email with the next steps.
        </p>
        <Button onClick={onClose} className="w-full sm:w-auto px-8">
            Continue
        </Button>
    </div>
);

const CategorySelector = ({ categories, selectedIds, onToggle, error }: { categories: SupplierCategory[]; selectedIds: string[]; onToggle: (id: string) => void; error?: string }) => (
    <div className="space-y-3" role="group" aria-labelledby="categories-label">
        {categories.map((category) => (
            <div
                key={category.id}
                onClick={() => onToggle(category.id)}
                className={cn(
                    "cursor-pointer p-4 rounded-lg border-2 transition-all duration-200",
                    "hover:border-blue-500 hover:shadow-md",
                    "dark:border-gray-700 dark:hover:border-blue-600",
                    selectedIds.includes(category.id)
                        ? "border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-md"
                        : "border-gray-200"
                )}
                role="checkbox"
                aria-checked={selectedIds.includes(category.id)}
                tabIndex={0}
                onKeyDown={(e) => {
                    if (e.key === "Enter" || e.key === " ") {
                        e.preventDefault();
                        onToggle(category.id);
                    }
                }}
            >
                <div className="flex items-center justify-between">
                    <span className={cn(
                        "font-medium transition-colors duration-200",
                        selectedIds.includes(category.id)
                            ? "text-blue-600 dark:text-blue-400"
                            : "text-gray-900 dark:text-gray-100"
                    )}>
                        {category.name}
                    </span>
                    <div className={cn(
                        "w-5 h-5 rounded-full border-2 transition-all duration-200 flex items-center justify-center",
                        selectedIds.includes(category.id)
                            ? "bg-blue-600 border-blue-600 scale-100"
                            : "bg-white border-gray-400 dark:bg-gray-900 dark:border-gray-600"
                    )}>
                        {selectedIds.includes(category.id) && <Check className="w-3 h-3 text-white" />}
                    </div>
                </div>
            </div>
        ))}
        {error && (
            <div className="flex items-center gap-2 text-red-600 text-sm animate-in slide-in-from-left-2" role="alert" aria-live="assertive">
                <AlertCircle className="w-4 h-4" />
                <span>{error}</span>
            </div>
        )}
    </div>
);

export default function ApplicationForm({ children, open = false, onOpenChange, defaultRoundId, onSuccess }: { children?: React.ReactNode; open?: boolean; onOpenChange?: (open: boolean) => void; defaultRoundId?: string; onSuccess?: (result: { applicationId: string; statusCode: "O" | "CL" | "S"; statusLabel: string; roundId: string }) => void }) {
    const { data: session } = useSession();
    const accessToken = session?.accessToken as string;
    const [rounds, setRounds] = useState<Round[]>([]);
    const [categories, setCategories] = useState<SupplierCategory[]>([]);
    const [roundsLoadingState, setRoundsLoadingState] = useState<LoadingState>("idle");
    const [categoriesLoadingState, setCategoriesLoadingState] = useState<LoadingState>("idle");
    const [formLoadingState, setFormLoadingState] = useState<LoadingState>("idle");
    const [roundError, setRoundError] = useState<string>("");
    const [categoryError, setCategoryError] = useState<string>("");
    const [formMessage, setFormMessage] = useState<{ type: "success" | "warning" | "error"; message: string } | null>(null);

    const form = useForm<FormValues>({
        resolver: zodResolver(FormSchema),
        defaultValues: { roundId: defaultRoundId ?? "", categoryIds: [] },
        mode: "onChange",
    });

    const selectedCategoryIds = form.watch("categoryIds");
    const selectedRoundId = form.watch("roundId");

    const fetchRounds = useCallback(async () => {
        if (!accessToken) return;
        setRoundsLoadingState("loading");
        setRoundError("");
        try {
            const roundsRes: unknown = await getRounds({}, accessToken);
            const parsed = RoundsApiResponseSchema.safeParse(roundsRes);
            const raw = parsed.success ? parsed.data.data ?? [] : Array.isArray(roundsRes) ? roundsRes : [];
            const normalizedAll = (raw as z.infer<typeof RoundApiItemSchema>[]).map((r) => {
                const statusCode = typeof r.status === "string" ? r.status : (r.status as { value?: string })?.value;
                const deadline = r.deadline || r.endDate || r.startDate;
                return {
                    id: (r.roundID ?? r.id ?? "").toString(),
                    name: r.title ?? r.name ?? "Untitled Round",
                    status: (statusCode || "O") as string,
                    deadline,
                    applicantCount: r.applicantCount,
                    hasApplied: Boolean((r as { hasApplied?: boolean }).hasApplied) || Boolean((r as { applicationId?: unknown }).applicationId),
                    applicationId: (r as { applicationId?: unknown }).applicationId ? String((r as { applicationId?: unknown }).applicationId) : undefined,
                };
            }).filter(r => r.id);
            const filtered = defaultRoundId
                ? normalizedAll.filter(r => r.id === defaultRoundId)
                : normalizedAll;
            setRounds(filtered);
            if (defaultRoundId && filtered.length === 0) {
                setRoundError("Selected round not found or is unavailable.");
                setRoundsLoadingState("error");
            } else {
                setRoundsLoadingState("success");
            }
        } catch (error: unknown) {
            const errorMessage = (error as Error)?.message || "Failed to load prequalification rounds. Please try again.";
            setRoundError(errorMessage);
            setRoundsLoadingState("error");
            setRounds([]);
        }
    }, [accessToken, defaultRoundId]);

    const fetchCategories = useCallback(async () => {
        if (!accessToken || !selectedRoundId) {
            setCategories([]);
            setCategoriesLoadingState("idle");
            return;
        }
        setCategoriesLoadingState("loading");
        setCategoryError("");
        try {
            const categoriesRes: unknown = await getSupplierCategories(accessToken);
            const parsed = CategoriesApiResponseSchema.safeParse(categoriesRes);
            const raw = parsed.success ? parsed.data.data ?? [] : Array.isArray(categoriesRes) ? categoriesRes : [];
            const mapped: SupplierCategory[] = (raw as z.infer<typeof CategoryApiItemSchema>[]).map((c) => ({
                id: c.supplierCategoryID?.toString() ?? "",
                name: c.categoryName ?? "Unnamed Category",
                is_active: c.is_active ?? true,
            }));
            const active = mapped.filter((c) => c.is_active);
            setCategories(active);
            setCategoriesLoadingState("success");
        } catch (error: unknown) {
            const errorMessage = (error as Error)?.message || "Failed to load supplier categories. Please try again.";
            setCategoryError(errorMessage);
            setCategoriesLoadingState("error");
            setCategories([]);
        }
    }, [accessToken, selectedRoundId]);

    useEffect(() => {
        if (open && accessToken) fetchRounds();
    }, [open, accessToken, fetchRounds]);

    // When defaultRoundId is provided, auto-select it after rounds load
    useEffect(() => {
        if (defaultRoundId && roundsLoadingState === "success") {
            const match = rounds.find(r => r.id === defaultRoundId);
            if (match) {
                form.setValue("roundId", match.id, { shouldValidate: true });
            }
        }
    }, [defaultRoundId, roundsLoadingState, rounds, form]);

    useEffect(() => {
        if (selectedRoundId) fetchCategories();
        else form.setValue("categoryIds", []);
    }, [selectedRoundId, fetchCategories, form]);

    useEffect(() => {
        if (defaultRoundId) form.setValue("roundId", defaultRoundId);
    }, [defaultRoundId, form]);

    const handleToggleCategory = useCallback(
        (categoryId: string) => {
            const current = form.getValues("categoryIds");
            const exists = current.includes(categoryId);
            const next = exists ? current.filter((id) => id !== categoryId) : [...current, categoryId];
            form.setValue("categoryIds", next, { shouldValidate: true });
        },
        [form]
    );

    const onSubmit = useCallback(
        async (values: FormValues) => {
            if (!accessToken) {
                toast.error("Authentication failed. Please log in again.");
                return;
            }
            setFormLoadingState("submitting");
            setFormMessage(null); // Reset any previous form messages
            const roundId = parseInt(values.roundId, 10);
            const categoryIds = values.categoryIds.map((id) => parseInt(id, 10));
            try {
                const resp = await submitApplicationSafe(roundId, categoryIds, accessToken);
                if (resp.status === 201) {
                    setFormMessage({ type: "success", message: "" });
                    setFormLoadingState("success");
                    const application = resp.data?.application;
                    if (application) {
                        onSuccess?.({ applicationId: application.ApplicationID, statusCode: application.Status as "O" | "CL" | "S", statusLabel: application.StatusLabel ?? "Submitted", roundId: values.roundId });
                    } else {
                        onSuccess?.({ applicationId: "", statusCode: "S", statusLabel: "Submitted", roundId: values.roundId });
                    }
                } else if (resp.status === 409) {
                    const duplicates = resp.data?.duplicates || [];
                    const names = duplicates.map((d: { name?: string }) => d.name || 'Unknown').join(', ');
                    setFormMessage({ type: "warning", message: `⚠️ You have already applied for the category: ${names} in this round.` });
                    setFormLoadingState("warning");
                } else if (resp.status === 401 || resp.status === 403) {
                    setFormMessage({ type: "error", message: "You are not authorized to apply to this round." });
                    setFormLoadingState("error");
                } else if (resp.status === 422) {
                    const firstErr = (() => {
                        const errs = resp.data?.errors;
                        if (errs && typeof errs === 'object') {
                            const firstKey = Object.keys(errs)[0];
                            if (firstKey) return Array.isArray(errs[firstKey]) ? errs[firstKey][0] : errs[firstKey];
                        }
                        return null;
                    })();
                    setFormMessage({ type: "error", message: firstErr || "Validation error. Please check your input." });
                    setFormLoadingState("error");
                } else if (resp.status >= 500) {
                    setFormMessage({ type: "error", message: "Server error. Please try again later." });
                    setFormLoadingState("error");
                } else {
                    setFormMessage({ type: "error", message: resp.data?.message || `Unexpected error (${resp.status}).` });
                    setFormLoadingState("error");
                }
            } catch (err: unknown) {
                setFormLoadingState("error");
                setFormMessage({ type: "error", message: (err as Error)?.message || "Please check your connection and try again." });
            }
        },
        [accessToken, onSuccess]
    );

    const handleClose = useCallback(() => {
        setFormMessage(null);
        setFormLoadingState("idle");
        form.reset({ roundId: defaultRoundId ?? "", categoryIds: [] });
        onOpenChange?.(false);
    }, [defaultRoundId, form, onOpenChange]);

    const trigger = useMemo(() => <SheetTrigger asChild>{children}</SheetTrigger>, [children]);

    const roundValidationState = form.formState.errors.roundId ? "invalid" : "valid";
    const categoriesValid = selectedCategoryIds.length > 0;
    const isFormValid = form.formState.isValid && categoriesValid;

    const renderFormState = () => {
        switch (formLoadingState) {
            case "success":
                return <SuccessState onClose={handleClose} />;
            case "warning":
                return <WarningState message={formMessage?.message || "You have already applied to this round."} />;
            case "error":
                return <ErrorState error={formMessage?.message || "Application submission failed. Please try again."} onRetry={() => onSubmit(form.getValues())} />;
            default:
                return (
                    <form className="mt-8 space-y-8" onSubmit={form.handleSubmit(onSubmit)} noValidate>
                        <div className="space-y-6">
                            <div className="space-y-4">
                                {!defaultRoundId && (
                                    <>
                                        <div className="flex items-center justify-between">
                                            <Label htmlFor="roundId" id="rounds-label" className="text-base font-semibold flex items-center gap-2">
                                                <Calendar className="w-4 h-4" />
                                                Available Rounds
                                            </Label>
                                            {roundsLoadingState === "loading" && <Loader2 className="w-4 h-4 animate-spin text-blue-500" aria-label="Loading rounds" />}
                                        </div>
                                        {roundsLoadingState === "loading" && <LoadingSkeleton />}
                                        {roundsLoadingState === "error" && <ErrorState error={roundError} onRetry={fetchRounds} />}
                                        {roundsLoadingState === "success" && rounds.length > 0 && (
                                            <>
                                                <Select value={form.watch("roundId")} onValueChange={(value) => form.setValue("roundId", value, { shouldValidate: true })}>
                                                    <SelectTrigger id="roundId" className={`h-12 transition-all duration-300 ${roundValidationState === "valid" ? "border-green-300 focus:border-green-500 focus:ring-green-200" : roundValidationState === "invalid" ? "border-red-300 focus:border-red-500 focus:ring-red-200" : "focus:border-blue-500 focus:ring-blue-200"}`} aria-invalid={!!form.formState.errors.roundId} aria-describedby="roundId-error" aria-labelledby="rounds-label">
                                                        <SelectValue placeholder="Choose a prequalification round" />
                                                    </SelectTrigger>
                                                    <SelectContent className="max-w-md">
                                                        {rounds.map((round) => (
                                                            <SelectItem key={round.id} value={round.id} disabled={round.status.toLowerCase() === "cl" || round.status.toLowerCase() === "closed"} className="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                                <div className="w-full">
                                                                    <div className="flex items-center justify-between mb-2">
                                                                        <span className="font-medium text-gray-900 dark:text-gray-100">{round.name}</span>
                                                                        <StatusBadge status={round.status} />
                                                                    </div>
                                                                    <div className="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                                                        {round.deadline && (
                                                                            <span className="flex items-center gap-1">
                                                                                <Clock className="w-3 h-3" />
                                                                                Deadline: {new Date(round.deadline).toLocaleDateString()}
                                                                            </span>
                                                                        )}
                                                                        {round.applicantCount !== undefined && (
                                                                            <span className="flex items-center gap-1">
                                                                                <Users className="w-3 h-3" />
                                                                                {round.applicantCount} applicants
                                                                            </span>
                                                                        )}
                                                                    </div>
                                                                </div>
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                {form.formState.errors.roundId && (
                                                    <div className="flex items-center gap-2 text-red-600 text-sm animate-in slide-in-from-left-2" role="alert">
                                                        <AlertCircle className="w-4 h-4" />
                                                        <span id="roundId-error">{form.formState.errors.roundId.message}</span>
                                                    </div>
                                                )}
                                            </>
                                        )}
                                        {roundsLoadingState === "success" && rounds.length === 0 && <EmptyState onRetry={fetchRounds} />}
                                    </>
                                )}
                                {defaultRoundId && roundsLoadingState === "success" && rounds[0] && (
                                    <div className="p-4 rounded-lg border bg-gray-50 dark:bg-gray-900/30 dark:border-gray-700">
                                        <div className="flex items-center justify-between mb-2">
                                            <div className="flex items-center gap-2">
                                                <Calendar className="w-4 h-4 text-blue-500" />
                                                <span className="font-medium text-gray-900 dark:text-gray-100">{rounds[0].name}</span>
                                            </div>
                                            <StatusBadge status={rounds[0].status} />
                                        </div>
                                        {rounds[0].hasApplied && (
                                            <div className="mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium" role="note">
                                                Already applied{rounds[0].applicationId ? ` • Ref ${rounds[0].applicationId}` : ''}
                                            </div>
                                        )}
                                        <div className="flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-400">
                                            {rounds[0].deadline && (
                                                <span className="flex items-center gap-1">
                                                    <Clock className="w-3 h-3" />
                                                    Deadline: {new Date(rounds[0].deadline).toLocaleDateString()}
                                                </span>
                                            )}
                                            {rounds[0].applicantCount !== undefined && (
                                                <span className="flex items-center gap-1">
                                                    <Users className="w-3 h-3" />
                                                    {rounds[0].applicantCount} applicants
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                            {selectedRoundId && (!rounds[0]?.hasApplied) && (
                                <div className="space-y-4 pt-6 transition-all duration-300 ease-in-out animate-in slide-in-from-bottom-4">
                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="categories" id="categories-label" className="text-base font-semibold flex items-center gap-2">
                                            <Users className="w-4 h-4" />
                                            Select Categories ({selectedCategoryIds.length} selected)
                                        </Label>
                                        {categoriesLoadingState === "loading" && <Loader2 className="w-4 h-4 animate-spin text-blue-500" aria-label="Loading categories" />}
                                    </div>
                                    {categoriesLoadingState === "loading" && <LoadingSkeleton />}
                                    {categoriesLoadingState === "error" && <ErrorState error={categoryError} onRetry={fetchCategories} />}
                                    {categoriesLoadingState === "success" && categories.length > 0 && (
                                        <CategorySelector categories={categories} selectedIds={selectedCategoryIds} onToggle={handleToggleCategory} error={form.formState.errors.categoryIds?.message} />
                                    )}
                                    {categoriesLoadingState === "success" && categories.length === 0 && (
                                        <div className="flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-gray-200 rounded-lg bg-gray-50/50 dark:border-gray-800 dark:bg-gray-900/10">
                                            <Info className="w-12 h-12 text-gray-500 mb-3" />
                                            <h3 className="text-lg font-semibold text-gray-900 mb-2 dark:text-gray-100">No Categories Found</h3>
                                            <p className="text-gray-600 mb-4 text-sm max-w-sm dark:text-gray-400">This round has no associated categories for selection.</p>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                        <div className="flex flex-col-reverse sm:flex-row gap-3 pt-6 border-t">
                            <Button type="button" variant="outline" onClick={handleClose} disabled={formLoadingState === "submitting"} className="flex-1 sm:flex-none transition-all duration-200 hover:scale-105 focus:scale-105">
                                Cancel
                            </Button>
                            {!rounds[0]?.hasApplied && <Button
                                type="submit"
                                disabled={
                                    formLoadingState === "submitting" ||
                                    roundsLoadingState === "loading" ||
                                    roundsLoadingState === "error" ||
                                    categoriesLoadingState === "loading" ||
                                    categoriesLoadingState === "error" ||
                                    !isFormValid
                                }
                                className="flex-1 sm:flex-none bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 disabled:hover:scale-100 focus:scale-105"
                            >
                                {formLoadingState === "submitting" ? (
                                    <>
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                        Submitting Application...
                                    </>
                                ) : (
                                    <>
                                        <Send className="mr-2 h-4 w-4" />
                                        Submit Application
                                    </>
                                )}
                            </Button>}
                        </div>
                    </form>
                );
        }
    };

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            {trigger}
            <SheetContent className="w-full sm:max-w-xl md:max-w-2xl overflow-y-auto">
                <SheetHeader className="border-b pb-6">
                    <SheetTitle className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Prequalification Application</SheetTitle>
                    <SheetDescription className="text-gray-600 dark:text-gray-400">Apply to participate in procurement opportunities</SheetDescription>
                </SheetHeader>
                {renderFormState()}
            </SheetContent>
        </Sheet>
    );
}

export function ApplicationFormTrigger({ children }: { children: React.ReactNode }) {
    return <>{children}</>;
}