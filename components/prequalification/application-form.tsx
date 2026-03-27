"use client"

import { useEffect, useMemo, useState, useCallback, useRef } from "react"
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

import type { Round, RoundSection, SupplierCategory } from "@/types/prequalification-rounds-types"

type LoadingState = "idle" | "loading" | "success" | "error" | "submitting" | "warning";

type UploadStatus = 'pending' | 'uploading' | 'done' | 'error';
type UploadItem = { id: string; file?: File | null; sectionId?: number | null; fileType?: string; categoryId: string; status: UploadStatus; error?: string; serverId?: number | null };
type UnknownSection = Record<string, unknown>;
type UnknownCriteria = Record<string, unknown>;
const firstOf = <T = unknown>(o: unknown, keys: string[]): T | undefined => {
    if (!o || typeof o !== 'object') return undefined;
    const obj = o as Record<string, unknown>;
    for (const k of keys) {
        if (obj[k] !== undefined && obj[k] !== null) return obj[k] as T;
    }
    return undefined;
};

type SectionOption = { id: string; name: string; weight?: number };

const normalizeSections = (src: unknown): SectionOption[] => {
    const arr = (firstOf<unknown[]>(src, ['sections', 'Sections', 'availableSections', 'AvailableSections']) || []) as unknown[];
    const items: SectionOption[] = (Array.isArray(arr) ? arr : []).map((s) => {
        const id = firstOf<unknown>(s, ['sectionId', 'SectionID', 'SectionId', 'id', 'Id']);
        const nm = firstOf<string>(s, ['name', 'sectionName', 'SectionName', 'title', 'Title']) || (id != null ? `Section ${id}` : 'Section');
        const wt = firstOf<number>(s, ['weight', 'Weight', 'maxScore', 'MaxScore']);
        return { id: String(id ?? ''), name: nm, weight: typeof wt === 'number' ? wt : undefined };
    }).filter(x => x.id);
    const seen = new Set<string>();
    return items.filter(i => (seen.has(i.id) ? false : (seen.add(i.id), true)));
};

const FormSchema = z.object({
    roundId: z.string().min(1, "Please select a round to continue"),
    categoryIds: z.array(z.string()).min(1, "Please select at least one category"),
    descriptions: z.record(z.string()).optional(),
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
    applicationId: z.union([z.string(), z.number()]).optional(),
    hasApplied: z.boolean().optional(),
    appliedCount: z.number().optional(),
    description: z.string().optional(),
    sections: z
        .array(
            z.object({
                id: z.union([z.string(), z.number()]).nullable().optional(),
                sectionId: z.number().nullable().optional(),
                name: z.string().nullable().optional(),
                weight: z.number().nullable().optional(),
                criteria: z
                    .array(
                        z.object({
                            id: z.union([z.string(), z.number()]).nullable().optional(),
                            criteriaId: z.number().nullable().optional(),
                            maxScore: z.number().nullable().optional(),
                            included: z.boolean().optional(),
                        })
                    )
                    .optional(),
            })
        )
        .optional(),
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
                return { label: "Open", className: "bg-emerald-50 text-emerald-700 border-emerald-200", icon: <CheckCircle2 className="w-3 h-3" /> };
            case "cl":
            case "closed":
                return { label: "Closed", className: "bg-amber-50 text-amber-800 border-amber-200", icon: <X className="w-3 h-3" /> };
            case "pending":
                return { label: "Pending", className: "bg-slate-50 text-slate-700 border-slate-200", icon: <Clock className="w-3 h-3" /> };
            default:
                return { label: s, className: "bg-slate-50 text-slate-700 border-slate-200", icon: <Info className="w-3 h-3" /> };
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
        className="w-full flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 animate-pulse"
        role="status"
        aria-live="polite"
        aria-label="Loading ... "
    >
        {[...Array(3)].map((_, index) => (
            <div key={index} className="flex items-center space-x-4 rounded-xl border border-slate-200 bg-white p-4">
                <div className="flex-1 space-y-2">
                    <div className="h-4 w-full rounded bg-slate-200" />
                    <div className="h-4 w-4/5 rounded bg-slate-100" />
                </div>
                <div className="h-8 w-16 rounded-full bg-slate-100" />
            </div>
        ))}
    </div>
);

const EmptyState = ({ onRetry }: { onRetry: () => void }) => (
    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-8 text-center transition-colors duration-300">
        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600">
            <Frown className="w-6 h-6" />
        </div>
        <h3 className="mb-2 text-xl font-bold tracking-tight text-slate-900">
            Nothing to see here
        </h3>
        <p className="mb-6 max-w-sm text-sm text-slate-600">
            No open prequalification rounds are available at the moment. Please check back later or try refreshing.
        </p>
        <Button
            variant="ghost"
            onClick={onRetry}
            className="text-slate-700 hover:text-blue-700"
        >
            <RefreshCw className="w-4 h-4 mr-2" />
            Try Again
        </Button>
    </div>
);

const ErrorState = ({ error, onRetry }: { error: string; onRetry: () => void }) => (
    <div
        className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-rose-200 bg-rose-50/60 p-8 text-center"
        role="alert"
    >
        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-700">
            <XCircle className="w-6 h-6" />
        </div>
        <h3 className="mb-2 text-xl font-bold tracking-tight text-rose-950">
            Something went wrong
        </h3>
        <p className="mb-6 max-w-sm text-sm text-rose-700">
            {error || "An unexpected error occurred. Please try again later."}
        </p>
        <Button
            variant="ghost"
            onClick={onRetry}
            className="text-rose-700 hover:text-rose-800"
        >
            <RefreshCw className="w-4 h-4 mr-2" />
            Try Again
        </Button>
    </div>
);

const WarningState = ({ message }: { message: string }) => (
    <div
        className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-amber-50/60 p-8 text-center"
        role="alert"
    >
        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-800">
            <InfoIcon className="w-6 h-6" />
        </div>
        <h3 className="mb-2 text-xl font-bold tracking-tight text-amber-950">
            Already Applied
        </h3>
        <p className="mb-6 max-w-sm text-sm text-amber-800">
            {message}
        </p>
    </div>
);

const SuccessState = ({ onClose }: { onClose: () => void }) => (
    <div
        className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/60 p-8 text-center"
        role="status"
    >
        <div className="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <CheckCheck className="w-8 h-8" />
        </div>
        <h3 className="mb-2 text-2xl font-bold tracking-tight text-emerald-950">
            Application submitted
        </h3>
        <p className="mb-6 max-w-sm text-sm text-emerald-800">
            Your prequalification application has been successfully submitted. We&apos;ll send you an email with the next steps.
        </p>
        <Button onClick={onClose} className="h-11 w-full rounded-xl px-8 sm:w-auto">
            Continue
        </Button>
    </div>
);

const CategorySelector = ({ categories, selectedIds, onToggle, error }: { categories: SupplierCategory[]; selectedIds: string[]; onToggle: (id: string) => void; error?: string }) => (
    <div className="space-y-3" role="group" aria-labelledby="categories-label">
        {categories.map((category) => {
            const cid = String(category.id);
            return (
                <div
                    key={cid}
                    onClick={() => onToggle(cid)}
                    className={cn(
                        "cursor-pointer rounded-xl border border-slate-200 bg-white p-4 transition-colors duration-200",
                        "hover:border-slate-300",
                        selectedIds.includes(cid)
                            ? "border-blue-300 bg-blue-50/60"
                            : ""
                    )}
                    role="checkbox"
                    aria-checked={selectedIds.includes(cid)}
                    tabIndex={0}
                    onKeyDown={(e) => {
                        if (e.key === "Enter" || e.key === " ") {
                            e.preventDefault();
                            onToggle(cid);
                        }
                    }}
                >
                    <div className="flex items-center justify-between">
                        <span className={cn(
                            "font-medium transition-colors duration-200",
                            selectedIds.includes(cid)
                                ? "text-blue-700"
                                : "text-slate-900"
                        )}>
                            {category.name}
                        </span>
                        <div className={cn(
                            "flex h-5 w-5 items-center justify-center rounded-full border transition-colors duration-200",
                            selectedIds.includes(cid)
                                ? "border-blue-600 bg-blue-600"
                                : "border-slate-300 bg-white"
                        )}>
                            {selectedIds.includes(cid) && <Check className="w-3 h-3 text-white" />}
                        </div>
                    </div>
                </div>
            );
        })}
        {error && (
            <div className="flex items-center gap-2 text-rose-700 text-sm animate-in slide-in-from-left-2" role="alert" aria-live="assertive">
                <AlertCircle className="w-4 h-4" />
                <span>{error}</span>
            </div>
        )}
    </div>
);

export default function ApplicationForm({ children, open = false, onOpenChange, defaultRoundId, onSuccess }: { children?: React.ReactNode; open?: boolean; onOpenChange?: (open: boolean) => void; defaultRoundId?: string; onSuccess?: (result: { applicationId: string; statusCode: "O" | "CL" | "S"; statusLabel: string; roundId: string }) => void }) {
    const { status } = useSession();
    const isAuthenticated = status === 'authenticated';
    const [rounds, setRounds] = useState<Round[]>([]);
    const [categories, setCategories] = useState<SupplierCategory[]>([]);
    const [roundsLoadingState, setRoundsLoadingState] = useState<LoadingState>("idle");
    const [categoriesLoadingState, setCategoriesLoadingState] = useState<LoadingState>("idle");
    const [formLoadingState, setFormLoadingState] = useState<LoadingState>("idle");
    const [roundError, setRoundError] = useState<string>("");
    const [categoryError, setCategoryError] = useState<string>("");
    const [formMessage, setFormMessage] = useState<{ type: "success" | "warning" | "error"; message: string } | null>(null);
    const [roundDetail, setRoundDetail] = useState<{ description?: string; sections?: RoundSection[] }>({});
    const [uploads, setUploads] = useState<UploadItem[]>([]);
    const counterRef = useRef(0);
    const [roundMetaById, setRoundMetaById] = useState<Record<string, { description?: string; sections?: RoundSection[] }>>({});

    const form = useForm<FormValues>({
        resolver: zodResolver(FormSchema),
        defaultValues: { roundId: defaultRoundId ?? "", categoryIds: [], descriptions: {} },
        mode: "onChange",
    });

    const selectedCategoryIds = form.watch("categoryIds");
    const selectedRoundId = form.watch("roundId");

    const fetchRounds = useCallback(async () => {
        if (!isAuthenticated) return;
        setRoundsLoadingState("loading");
        setRoundError("");
        try {
            const roundsRes: unknown = await getRounds({});
            const parsed = RoundsApiResponseSchema.safeParse(roundsRes);
            const raw = parsed.success ? parsed.data.data ?? [] : Array.isArray(roundsRes) ? roundsRes : [];
            const meta: Record<string, { description?: string; sections?: RoundSection[] }> = {};
            (raw as z.infer<typeof RoundApiItemSchema>[]).forEach((r) => {
                const rid = (r.roundID ?? r.id ?? "").toString();
                if (!rid) return;
                // Accept both `sections` and `Sections` from API resources
                const sectionsRaw = (r as unknown as Record<string, unknown>)['sections'] ?? (r as unknown as Record<string, unknown>)['Sections'];
                const normSections: RoundSection[] | undefined = Array.isArray(sectionsRaw)
                    ? (sectionsRaw as UnknownSection[]).map((s) => {
                        const sid = (s['sectionId'] ?? s['id'] ?? s['sectionID'] ?? s['SectionID'] ?? null) as number | string | null;
                        const sname = (s['name'] ?? s['sectionName'] ?? s['SectionName'] ?? s['title']) as string | undefined;
                        const sweight = (s['weight'] ?? s['sectionWeight'] ?? s['Weight'] ?? null) as number | null;
                        const critArr = Array.isArray(s['criteria']) ? (s['criteria'] as UnknownCriteria[]) : undefined;
                        const crit = critArr
                            ? critArr.map((c) => ({
                                id: (c['id'] ?? c['criteriaId'] ?? c['criteriaID'] ?? c['CriteriaID'] ?? null) as number | string | null,
                                criteriaId: (c['criteriaId'] ?? c['id'] ?? c['criteriaID'] ?? c['CriteriaID'] ?? null) as number | null,
                                maxScore: (c['maxScore'] ?? c['weight'] ?? c['Weight'] ?? c['score'] ?? null) as number | null,
                                included: (c['included'] ?? c['Included'] ?? true) as boolean,
                            }))
                            : undefined;
                        const nm = sname || (sid != null ? `Section ${sid}` : undefined);
                        return { id: sid, sectionId: typeof sid === 'number' ? sid : Number(sid) || null, name: nm, weight: sweight, criteria: crit } as RoundSection;
                    })
                    : undefined;
                meta[rid] = { description: r.description, sections: normSections };
            });
            const normalizedAll: Round[] = (raw as z.infer<typeof RoundApiItemSchema>[]).map((r) => {
                const statusCode = typeof r.status === "string" ? r.status : (r.status as { value?: string })?.value;
                const deadline = r.deadline || r.endDate || r.startDate;
                return {
                    id: (r.roundID ?? r.id ?? "").toString(),
                    title: r.title ?? r.name ?? "Untitled Round",
                    name: r.title ?? r.name ?? "Untitled Round",
                    status: (statusCode || "O") as string,
                    deadline,
                    applicantCount: r.applicantCount,
                    hasApplied: Boolean(r.hasApplied || r.applicationId),
                    applicationId: r.applicationId !== undefined && r.applicationId !== null ? String(r.applicationId) : undefined,
                };
            }).filter(r => r.id);
            const filtered = defaultRoundId
                ? normalizedAll.filter(r => r.id === defaultRoundId)
                : normalizedAll;
            setRounds(filtered);
            setRoundMetaById(meta);
            if (defaultRoundId && filtered.length === 0) {
                setRoundError("Selected round not found or is unavailable.");
                setRoundsLoadingState("error");
            } else {
                setRoundsLoadingState("success");
            }
            if (defaultRoundId) {
                const arr = raw as z.infer<typeof RoundApiItemSchema>[];
                const match = arr.find((x) => String(x.id ?? x.roundID) === String(defaultRoundId)) || arr[0];
                const rid = match ? String(xId(match)) : "";
                setRoundDetail(meta[rid] || { description: match?.description, sections: undefined });
            }
        } catch (error: unknown) {
            const errorMessage = (error as Error)?.message || "Failed to load prequalification rounds. Please try again.";
            setRoundError(errorMessage);
            setRoundsLoadingState("error");
            setRounds([]);
        }
    }, [isAuthenticated, defaultRoundId]);

    const fetchCategories = useCallback(async () => {
        if (!isAuthenticated || !selectedRoundId) {
            setCategories([]);
            setCategoriesLoadingState("idle");
            return;
        }
        setCategoriesLoadingState("loading");
        setCategoryError("");
        try {
            const categoriesRes: unknown = await getSupplierCategories();
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
    }, [isAuthenticated, selectedRoundId]);

    useEffect(() => {
        if (open && isAuthenticated) fetchRounds();
    }, [open, isAuthenticated, fetchRounds]);

    // When defaultRoundId is provided, auto-select it after rounds load
    useEffect(() => {
        if (defaultRoundId && roundsLoadingState === "success") {
            const match = rounds.find(r => String(r.id) === defaultRoundId);
            if (match) {
                form.setValue("roundId", String(match.id), { shouldValidate: true });
            }
        }
    }, [defaultRoundId, roundsLoadingState, rounds, form]);

    useEffect(() => {
        if (selectedRoundId) fetchCategories();
        else form.setValue("categoryIds", []);
    }, [selectedRoundId, fetchCategories, form]);

    useEffect(() => {
        if (selectedRoundId && roundMetaById[selectedRoundId]) {
            setRoundDetail(roundMetaById[selectedRoundId]);
        }
    }, [selectedRoundId, roundMetaById]);

    // helper for id extraction across variants
    function xId(r: z.infer<typeof RoundApiItemSchema>): string {
        const val = (r.roundID ?? r.id);
        return typeof val === 'number' ? String(val) : (val ?? '').toString();
    }

    // Fallback: if sections are missing from list response, fetch per-round detail
    const fetchRoundDetail = useCallback(async (rid: string) => {
        if (!isAuthenticated || !rid) return;
        try {
            // Use frontend proxy route for proper session auth and CORS
            let res = await fetch(`/api/prequalification/rounds/${encodeURIComponent(rid)}`, { cache: 'no-store', credentials: 'same-origin' });
            let jsonRaw: unknown = await res.json().catch(() => ({} as unknown));
            if (!res.ok || !jsonRaw) {
                return;
            }
            const json = jsonRaw as unknown;
            const unwrap = (j: unknown): Record<string, unknown> => {
                if (j && typeof j === 'object' && 'data' in (j as Record<string, unknown>)) {
                    const maybe = (j as Record<string, unknown>)['data'];
                    if (maybe && typeof maybe === 'object') return maybe as Record<string, unknown>;
                }
                return (j && typeof j === 'object') ? (j as Record<string, unknown>) : {};
            };
            const item = unwrap(json);
            const sectionsRaw = (item['sections'] as UnknownSection[] | undefined) || (item['Sections'] as UnknownSection[] | undefined) || [];
            const description = (item['description'] as string | undefined) || roundMetaById[rid]?.description;
            const normSections: RoundSection[] | undefined = Array.isArray(sectionsRaw)
                ? sectionsRaw.map((s) => {
                    const sid = (s['sectionId'] ?? s['id'] ?? s['sectionID'] ?? s['SectionID'] ?? null) as number | string | null;
                    const sname = (s['name'] ?? s['sectionName'] ?? s['SectionName'] ?? s['title']) as string | undefined;
                    const sweight = (s['weight'] ?? s['sectionWeight'] ?? s['Weight'] ?? null) as number | null;
                    const critArr = Array.isArray(s['criteria']) ? (s['criteria'] as UnknownCriteria[]) : undefined;
                    const crit = critArr
                        ? critArr.map((c) => ({
                            id: (c['id'] ?? c['criteriaId'] ?? c['criteriaID'] ?? c['CriteriaID'] ?? null) as number | string | null,
                            criteriaId: (c['criteriaId'] ?? c['id'] ?? c['criteriaID'] ?? c['CriteriaID'] ?? null) as number | null,
                            maxScore: (c['maxScore'] ?? c['weight'] ?? c['Weight'] ?? c['score'] ?? null) as number | null,
                            included: (c['included'] ?? c['Included'] ?? true) as boolean,
                        }))
                        : undefined;
                    const nm = sname || (sid != null ? `Section ${sid}` : undefined);
                    return { id: sid, sectionId: typeof sid === 'number' ? sid : Number(sid) || null, name: nm, weight: sweight, criteria: crit } as RoundSection;
                })
                : undefined;
            setRoundMetaById((prev) => ({ ...prev, [rid]: { description, sections: normSections } }));
            setRoundDetail({ description, sections: normSections });
        } catch {
            // no-op
        }
    }, [isAuthenticated, roundMetaById]);

    useEffect(() => {
        if (!selectedRoundId) return;
        const meta = roundMetaById[selectedRoundId];
        const missing = !meta || !meta.sections || meta.sections.length === 0;
        if (missing) void fetchRoundDetail(selectedRoundId);
    }, [selectedRoundId, roundMetaById, fetchRoundDetail]);

    useEffect(() => {
        if (defaultRoundId) form.setValue("roundId", defaultRoundId);
    }, [defaultRoundId, form]);

    // Fallback effective round id (must be declared before any usage below)
    const effectiveRoundId = useMemo(() => (
        (selectedRoundId && String(selectedRoundId)) || (defaultRoundId && String(defaultRoundId)) || ""
    ), [selectedRoundId, defaultRoundId]);

    const handleToggleCategory = useCallback(
        (categoryId: string) => {
            const current = form.getValues("categoryIds");
            const exists = current.includes(categoryId);
            const next = exists ? current.filter((id) => id !== categoryId) : [...current, categoryId];
            form.setValue("categoryIds", next, { shouldValidate: true });
        },
        [form]
    );

    // upload helper removed — immediate upload performed inline in file input handler

    const uploadFile = useCallback(async (u: UploadItem) => {
        const rid = effectiveRoundId;
        if (!rid || !u.file) return;
        try {
            const fd = new FormData();
            fd.append('file', u.file as Blob);
            const sectionId = u.sectionId ?? (roundMetaById[String(rid)]?.sections?.[0]?.sectionId ?? null);
            if (!sectionId) {
                throw new Error('Please select a document type (section) for each file.');
            }
            fd.append('section_id', String(sectionId));
            if (u.fileType) fd.append('file_type', u.fileType);
            fd.append('description', (form.getValues('descriptions') as Record<string, string> | undefined)?.[u.categoryId] || '');
            const url = `/api/procurement/prequalification/applications/${encodeURIComponent(String(rid))}/categories/${encodeURIComponent(String(u.categoryId))}/documents`;
            const res = await fetch(url, { method: 'POST', body: fd, cache: 'no-store' });
            if (!res.ok) throw new Error(await res.text());
            setUploads((list) => list.map((x) => x.id === u.id ? { ...x, status: 'done' } : x));
        } catch (e: unknown) {
            const message = e instanceof Error ? e.message : String(e);
            setUploads((list) => list.map((x) => x.id === u.id ? { ...x, status: 'error', error: message } : x));
        }
    }, [effectiveRoundId, form, roundMetaById]);

    const onSubmit = useCallback(
        async (values: FormValues) => {
            if (!isAuthenticated) {
                toast.error("Authentication failed. Please log in again.");
                return;
            }
            setFormLoadingState("submitting");
            setFormMessage(null); // Reset any previous form messages
            const ridStr = values.roundId || effectiveRoundId;
            const roundId = parseInt(ridStr, 10);
            if (!roundId || Number.isNaN(roundId)) {
                setFormLoadingState("error");
                setFormMessage({ type: "error", message: "Please select a round." });
                return;
            }
            const categoryIds = values.categoryIds.map((id) => parseInt(id, 10));
            try {
                const resp = await submitApplicationSafe(roundId, categoryIds);
                // After submit, upload any staged files for the selected categories
                if (resp.status === 201) {
                    const staged = uploads.filter(u => (u.status === 'pending' || u.status === 'error') && categoryIds.includes(parseInt(u.categoryId, 10)));
                    for (const u of staged) {
                        setUploads((list) => list.map((x) => x.id === u.id ? { ...x, status: 'uploading' } : x));
                        await uploadFile(u);
                    }
                }
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
                } else if (resp.status === 401 || resp.status === 403 || resp.status === 410) {
                    const msg = resp.data?.message || (resp.status === 410 ? 'This round has expired.' : 'You are not authorized to apply to this round.');
                    setFormMessage({ type: "error", message: msg });
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
                    const upstreamMessage =
                        (typeof resp.data?.message === "string" && resp.data.message.trim()) ||
                        (typeof resp.data?.error === "string" && resp.data.error.trim()) ||
                        "";
                    setFormMessage({
                        type: "error",
                        message: upstreamMessage || "Server error. Please try again later.",
                    });
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
        [isAuthenticated, onSuccess, uploads, uploadFile, effectiveRoundId]
    );

    const handleClose = useCallback(() => {
        setFormMessage(null);
        setFormLoadingState("idle");
        form.reset({ roundId: defaultRoundId ?? "", categoryIds: [] });
        onOpenChange?.(false);
    }, [defaultRoundId, form, onOpenChange]);

    const trigger = useMemo(() => <SheetTrigger asChild>{children}</SheetTrigger>, [children]);

    const roundValidationState = form.formState.errors.roundId ? "invalid" : "valid";
    const currentRound = useMemo(() => rounds.find(r => String(r.id) === selectedRoundId) || (defaultRoundId ? rounds[0] : undefined), [rounds, selectedRoundId, defaultRoundId]);
    const descriptionsMap = form.watch("descriptions") as Record<string, string> | undefined;
    // round/category validity handled via submitEnabled below

    // Effective round id and submit gate

    // Compute section options from detail or meta cache, and auto-refetch if empty
    const sectionOptions = useMemo<SectionOption[]>(() => {
        const fromDetail = normalizeSections(roundDetail);
        if (fromDetail.length) return fromDetail;
        const rid = effectiveRoundId ? String(effectiveRoundId) : '';
        const meta = rid ? (roundMetaById?.[rid] ?? {}) : {};
        return normalizeSections(meta);
    }, [roundDetail, roundMetaById, effectiveRoundId]);

    useEffect(() => {
        if (!effectiveRoundId) return;
        if ((sectionOptions?.length ?? 0) > 0) return;
        // Silent re-fetch to populate sections if missing
        void fetchRoundDetail(String(effectiveRoundId));
    }, [effectiveRoundId, sectionOptions?.length, fetchRoundDetail]);

    const submitEnabled = useMemo(() => {
        const hasRound = Boolean(effectiveRoundId || form.getValues("roundId"));
        const hasCategories = Array.isArray(selectedCategoryIds) && selectedCategoryIds.length > 0;
        return Boolean(
            hasRound &&
            hasCategories &&
            formLoadingState !== "submitting" &&
            !currentRound?.hasApplied
        );
    }, [effectiveRoundId, selectedCategoryIds, formLoadingState, currentRound, form]);

    const renderFormState = () => {
        switch (formLoadingState) {
            case "success":
                return (
                    <div className="px-6 py-6 sm:px-8">
                        <SuccessState onClose={handleClose} />
                    </div>
                );
            case "warning":
                return (
                    <div className="px-6 py-6 sm:px-8">
                        <WarningState message={formMessage?.message || "You have already applied to this round."} />
                    </div>
                );
            case "error":
                return (
                    <div className="px-6 py-6 sm:px-8">
                        <ErrorState error={formMessage?.message || "Application submission failed. Please try again."} onRetry={() => onSubmit(form.getValues())} />
                    </div>
                );
            default:
                return (
                    <form className="flex h-full flex-col" onSubmit={form.handleSubmit(onSubmit)} noValidate>
                        <div className="flex-1 space-y-6 overflow-y-auto px-6 py-6 sm:px-8">
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
                                                    <SelectTrigger
                                                        id="roundId"
                                                        className={cn(
                                                            "h-11 w-full rounded-xl border-slate-200 bg-white focus-visible:ring-blue-100",
                                                            roundValidationState === "valid" && "border-emerald-300 focus-visible:ring-emerald-100",
                                                            roundValidationState === "invalid" && "border-rose-300 focus-visible:ring-rose-100"
                                                        )}
                                                        aria-invalid={!!form.formState.errors.roundId}
                                                        aria-describedby="roundId-error"
                                                        aria-labelledby="rounds-label"
                                                    >
                                                        <SelectValue placeholder="Choose a prequalification round" />
                                                    </SelectTrigger>
                                                    <SelectContent className="max-w-md rounded-xl border-slate-200 shadow-none">
                                                        {rounds.map((round) => (
                                                            <SelectItem
                                                                key={String(round.id)}
                                                                value={String(round.id)}
                                                                disabled={round.status?.toLowerCase() === "cl" || round.status?.toLowerCase() === "closed"}
                                                                className="rounded-lg p-3 transition-colors hover:bg-slate-50 focus:bg-slate-50"
                                                            >
                                                                <div className="w-full">
                                                                    <div className="flex items-center justify-between mb-2">
                                                                        <span className="font-medium text-slate-900">{round.name ?? round.title}</span>
                                                                        <StatusBadge status={round.status ?? ""} />
                                                                    </div>
                                                                    <div className="flex items-center gap-4 text-xs text-slate-600">
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
                                                    <div className="flex items-center gap-2 text-rose-700 text-sm animate-in slide-in-from-left-2" role="alert">
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
                                    <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                        <div className="flex items-center justify-between mb-2">
                                            <div className="flex items-center gap-2">
                                                <Calendar className="w-4 h-4 text-blue-500" />
                                                <span className="font-medium text-slate-900">{rounds[0].name ?? rounds[0].title}</span>
                                            </div>
                                            <StatusBadge status={rounds[0].status ?? ""} />
                                        </div>
                                        {roundDetail.description && (
                                            <div className="mb-2 whitespace-pre-wrap text-sm text-slate-600">
                                                {roundDetail.description}
                                            </div>
                                        )}
                                        {Array.isArray(roundDetail.sections) && roundDetail.sections.length > 0 && (
                                            <div className="mt-3 space-y-2">
                                                <h4 className="text-sm font-semibold">Sections & Criteria</h4>
                                                <ul className="text-sm list-disc pl-5 space-y-1">
                                                    {roundDetail.sections.map((s, si) => (
                                                        <li key={String(s.id ?? s.sectionId ?? si)}>
                                                            <span className="font-medium">{s.name}</span>
                                                            {Array.isArray(s.criteria) && s.criteria.length > 0 && (
                                                                <ul className="list-disc pl-5 mt-1">
                                                                    {s.criteria.map((c, ci) => (
                                                                        <li key={String(c.id ?? c.criteriaId ?? ci)}>Max: {c.maxScore ?? '-'} {c.included === false ? '(excluded)' : ''}</li>
                                                                    ))}
                                                                </ul>
                                                            )}
                                                        </li>
                                                    ))}
                                                </ul>
                                            </div>
                                        )}
                                        {rounds[0].hasApplied && (
                                            <div className="mt-2 text-xs font-medium text-emerald-700" role="note">
                                                Already applied{rounds[0].applicationId ? ` • Ref ${rounds[0].applicationId}` : ''}
                                            </div>
                                        )}
                                        <div className="flex flex-wrap gap-4 text-xs text-slate-600">
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
                            {selectedRoundId && (
                                <div className="space-y-4 pt-6 transition-all duration-300 ease-in-out animate-in slide-in-from-bottom-4">
                                    {currentRound && (
                                        <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                            <div className="flex items-center justify-between mb-2">
                                                <div className="flex items-center gap-2">
                                                    <Calendar className="w-4 h-4 text-blue-500" />
                                                    <span className="font-medium text-slate-900">{currentRound.name ?? currentRound.title}</span>
                                                </div>
                                                <StatusBadge status={currentRound.status ?? ""} />
                                            </div>
                                            {roundDetail.description && (
                                                <div className="mb-2 whitespace-pre-wrap text-sm text-slate-600">
                                                    {roundDetail.description}
                                                </div>
                                            )}
                                        </div>
                                    )}
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
                                        currentRound?.hasApplied ? (
                                            <WarningState message="You have already applied to this round. Categories are locked." />
                                        ) : (
                                            <div className="space-y-6">
                                                <CategorySelector categories={categories} selectedIds={selectedCategoryIds} onToggle={handleToggleCategory} error={form.formState.errors.categoryIds?.message} />
                                                {selectedCategoryIds.length > 0 && (
                                                    <div className="space-y-4">
                                                        <h4 className="text-sm font-semibold">Supporting Documents</h4>
                                                        {selectedCategoryIds.map((cid) => (
                                                            <div key={cid} className="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
                                                                <div className="flex items-center justify-between">
                                                                    <div className="flex flex-col gap-1">
                                                                        <div className="text-sm font-medium text-slate-900">{categories.find(c => c.id === cid)?.name || 'Category'}</div>
                                                                        <div className="text-xs text-slate-600">Optional notes and supporting documents</div>
                                                                    </div>
                                                                </div>
                                                                <textarea
                                                                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-100"
                                                                    placeholder="Add a short note (optional)"
                                                                    value={(descriptionsMap?.[cid] ?? '')}
                                                                    onChange={(e) => {
                                                                        const next = { ...(descriptionsMap || {}), [cid]: e.target.value };
                                                                        form.setValue('descriptions', next, { shouldDirty: true, shouldValidate: false });
                                                                    }}
                                                                />
                                                                <div className="grid gap-2">
                                                                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                                                                        <label className="text-xs text-slate-600">Upload file</label>
                                                                        <input className="sm:col-span-2 h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200" type="file" id={`fileinput-${cid}`} onChange={async (e) => {
                                                                            const inputEl = e.currentTarget as HTMLInputElement | null;
                                                                            const f = inputEl?.files?.[0]
                                                                            if (!f) return
                                                                            const sectionId = undefined; // will be set in per-file editor below
                                                                            const fileType = undefined;
                                                                            // Stage local upload entry (do not upload now)
                                                                            const uuid = typeof window !== 'undefined' && typeof window.crypto?.randomUUID === 'function'
                                                                                ? window.crypto.randomUUID()
                                                                                : `u-${Date.now()}-${(counterRef.current += 1)}`;
                                                                            const item: UploadItem = { id: uuid, file: f, categoryId: cid, sectionId: sectionId ?? null, fileType: fileType || undefined, status: 'pending' };
                                                                            setUploads((u) => [...u, item]);
                                                                            if (inputEl) inputEl.value = '';
                                                                        }} />
                                                                    </div>
                                                                    {uploads.filter(u => u.categoryId === cid).length > 0 && (
                                                                        <div className="space-y-2 text-xs">
                                                                            {uploads.filter(u => u.categoryId === cid).map((u) => (
                                                                                <div key={u.id} className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50/40 p-3">
                                                                                    <div className="flex items-center justify-between">
                                                                                        <span className="truncate font-medium">{u.file ? u.file.name : 'staged'}</span>
                                                                                        <span className={u.status === 'done' ? 'text-emerald-700' : u.status === 'error' ? 'text-rose-700' : u.status === 'uploading' ? 'text-blue-700' : 'text-slate-500'}>
                                                                                            {u.status}
                                                                                        </span>
                                                                                    </div>
                                                                                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                                                                                        <label className="text-[11px] text-slate-600">Document type</label>
                                                                                        <select
                                                                                            className="sm:col-span-2 h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-100"
                                                                                            value={u.sectionId ?? ''}
                                                                                            onChange={(ev) => {
                                                                                                const val = ev.target.value ? Number(ev.target.value) : null;
                                                                                                setUploads((list) => list.map((x) => x.id === u.id ? { ...x, sectionId: val } : x));
                                                                                            }}
                                                                                        >
                                                                                            <option value="">Select section</option>
                                                                                            {sectionOptions.length === 0 ? (
                                                                                                <option value="" disabled>Loading sections…</option>
                                                                                            ) : (
                                                                                                sectionOptions.map((s, si) => (
                                                                                                    <option key={s.id || String(si)} value={s.id}>{s.name}{typeof s.weight === 'number' ? ` (${s.weight}%)` : ''}</option>
                                                                                                ))
                                                                                            )}
                                                                                        </select>
                                                                                        <label className="text-[11px] text-slate-600">File type</label>
                                                                                        <input
                                                                                            className="sm:col-span-2 h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-100"
                                                                                            value={u.fileType || ''}
                                                                                            onChange={(ev) => setUploads((list) => list.map((x) => x.id === u.id ? { ...x, fileType: ev.target.value || undefined } : x))}
                                                                                            placeholder="e.g., Company Profile, License"
                                                                                        />
                                                                                    </div>
                                                                                </div>
                                                                            ))}
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        )
                                    )}
                                    {categoriesLoadingState === "success" && categories.length === 0 && (
                                        <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 py-8 text-center">
                                            <Info className="mb-3 h-12 w-12 text-slate-500" />
                                            <h3 className="mb-2 text-lg font-semibold text-slate-900">No Categories Found</h3>
                                            <p className="mb-4 max-w-sm text-sm text-slate-600">This round has no associated categories for selection.</p>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                        <div className="sticky bottom-0 flex flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-6 py-4 backdrop-blur sm:flex-row sm:px-8">
                            <Button type="button" variant="outline" onClick={handleClose} disabled={formLoadingState === "submitting"} className="h-11 flex-1 rounded-xl border-slate-200 bg-white hover:bg-slate-50 sm:flex-none">
                                Cancel
                            </Button>
                            {!currentRound?.hasApplied && <Button
                                type="submit"
                                disabled={!submitEnabled}
                                className="h-11 flex-1 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 focus-visible:ring-indigo-100 sm:flex-none"
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
            <SheetContent className="w-full sm:max-w-2xl lg:max-w-3xl overflow-hidden border-l border-slate-200 bg-white p-0 shadow-none">
                <SheetHeader className="sticky top-0 z-10 border-b border-slate-200 bg-white/95 px-6 py-5 pr-12 backdrop-blur sm:px-8">
                    <SheetTitle className="text-2xl font-bold tracking-tight text-slate-900">Prequalification application</SheetTitle>
                    <SheetDescription className="text-slate-600">Apply to participate in procurement opportunities.</SheetDescription>
                    <div className="mt-3 flex flex-wrap gap-2">
                        <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            {selectedRoundId ? "Round selected" : "Select a round"}
                        </span>
                        <span className="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                            {selectedCategoryIds.length} categories selected
                        </span>
                    </div>
                </SheetHeader>
                {renderFormState()}
            </SheetContent>
        </Sheet>
    );
}

export function ApplicationFormTrigger({ children }: { children: React.ReactNode }) {
    return <>{children}</>;
}
