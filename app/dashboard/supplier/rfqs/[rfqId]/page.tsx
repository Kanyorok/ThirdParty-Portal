"use client";

import React, { useCallback, useEffect, useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { Card } from "@/components/common/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableFooter } from "@/components/common/table";
import { Button } from "@/components/common/button";
import { Input } from "@/components/common/input";
import { Textarea } from "@/components/common/textarea";
import { Label } from "@/components/common/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/common/accordion";
import { Badge } from "@/components/common/badge";
import { Loader2, FileText, Send, Save, MessageSquarePlus, Printer } from "lucide-react";
import { format } from "date-fns";
import type { RfqHeader, RfqLineItem, RfqDocumentAttachment, SupplierLineResponseInput } from "@/types/rfq";

type CurrencyOption = { id?: string; name: string; code: string; symbol: string; isDefault?: boolean };
type SupplierProfile = { tradingName?: string; businessType?: string; registrationNumber?: string; taxPin?: string; email?: string; phone?: string; country?: string; physicalAddress?: string };
type ResponseItem = { rfqLineId: string; quotedPrice?: number | null; totalPayable?: number | null; comments?: string | null };
type SupplierResponse = { currency?: string; durationDays?: number; leadTimeDays?: number; items?: ResponseItem[]; submittedAt?: string };

interface RfqDetails {
    header: RfqHeader;
    lines: RfqLineItem[];
    branding?: { name?: string; motto?: string; logo?: string; address?: string; email?: string; phone?: string };
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return !!value && typeof value === "object" && !Array.isArray(value);
}

function toStringSafe(value: unknown, fallback = ""): string {
    if (value == null) return fallback;
    if (typeof value === "string") return value;
    if (typeof value === "number") return String(value);
    return fallback;
}

function toNumberSafe(value: unknown, fallback = 0): number {
    const num = typeof value === "number" ? value : parseFloat(String(value));
    return Number.isFinite(num) ? num : fallback;
}

function normalizeAttachments(raw: unknown): RfqDocumentAttachment[] {
    if (!Array.isArray(raw)) return [];
    return raw.map((item): RfqDocumentAttachment => {
        const r = isRecord(item) ? item : {};
        return {
            id: toStringSafe(r.id || r.attachmentId || r.file_id || crypto.randomUUID()),
            fileName: toStringSafe(r.fileName || r.name || r.filename || r.title || "Document"),
            url: toStringSafe(r.url || r.fileUrl || r.downloadUrl || r.path || "#"),
        };
    });
}

function normalizeHeader(raw: Record<string, unknown>): RfqHeader {
    return {
        id: toStringSafe(raw.id ?? raw.rfqId ?? raw.rfq_id ?? ""),
        // Title: prefer 'comments' from supplier invitations
        title: toStringSafe(raw.comments ?? raw.title ?? raw.RFQTitle ?? raw.name ?? raw.referenceName ?? "Untitled RFQ"),
        // Reference: prefer 'number'
        referenceNumber: toStringSafe(raw.number ?? raw.referenceNumber ?? raw.reference ?? raw.RFQRef ?? raw.ref_no ?? raw.ref ?? "-"),
        buyerName: toStringSafe(raw.buyerName ?? raw.buyer ?? ""),
        // Closing date: prefer 'submissionDeadline'
        closingDate: toStringSafe(raw.submissionDeadline ?? raw.closingDate ?? raw.closeDate ?? raw.closing_date ?? raw.deadline ?? raw.endDate ?? ""),
        description: toStringSafe(raw.description ?? raw.details ?? ""),
        attachments: normalizeAttachments((raw as any)?.attachments),
    };
}

function normalizeLine(raw: Record<string, unknown>, index: number): RfqLineItem {
    const itemCode = toStringSafe(raw.ItemCode ?? raw.itemCode ?? raw.item_code ?? raw.code ?? raw.code_ ?? raw.ItemID ?? raw.itemId ?? raw.Item ?? "");
    const itemName = toStringSafe(raw.ItemName ?? raw.itemName ?? raw.item_name ?? raw.name ?? raw.itemDescription ?? raw.description ?? "");
    const descriptionVal = itemCode && itemName ? `${itemCode} - ${itemName}` : (itemName || itemCode || toStringSafe(raw.description ?? raw.itemDescription ?? raw.item ?? raw.name ?? ""));

    return {
        id: toStringSafe(raw.id ?? raw.rfqLineId ?? raw.lineId ?? raw.RFQLineID ?? `${index + 1}`),
        lineNumber: toNumberSafe(raw.lineNumber ?? raw.line_no ?? index + 1),
        description: descriptionVal,
        quantity: toNumberSafe(raw.quantity ?? raw.qty ?? 0),
        unitOfMeasure: toStringSafe(raw.unitOfMeasure ?? raw.unit ?? raw.uomName ?? raw.UOM ?? raw.uom ?? ""),
        specification: toStringSafe(raw.specification ?? raw.notes ?? raw.spec ?? ""),
    };
}

function normalizeDetails(data: unknown): (RfqDetails & { currencies?: CurrencyOption[]; response?: SupplierResponse; isSubmitted?: boolean }) | null {
    // Try common shapes: { data: { rfq, lines } }, { rfq, lines }, direct
    const pickContainer = (obj: Record<string, unknown>): Record<string, unknown> => {
        if (isRecord(obj.data)) return obj.data as Record<string, unknown>;
        return obj;
    };
    if (!isRecord(data)) return null;
    const container = pickContainer(data);
    const rfqRaw = (container.rfq || container.header || container.invitation || container) as Record<string, unknown>;
    const linesRaw = (container.lines || container.items || []) as unknown[];
    const header = normalizeHeader(isRecord(rfqRaw) ? rfqRaw : {});
    const lines = Array.isArray(linesRaw) ? linesRaw.map((l, i) => normalizeLine(isRecord(l) ? l : {}, i)) : [];
    // currencies may be provided by backend
    const currenciesRaw = (container.currencies || (rfqRaw as any)?.currencies) as unknown;
    let currencies: CurrencyOption[] | undefined;
    if (Array.isArray(currenciesRaw)) {
        currencies = (currenciesRaw as any[]).map((c: any) => ({
            id: String(c.id ?? c.Id ?? ""),
            name: String(c.name ?? c.Name ?? c.symbol ?? c.Symbol ?? ""),
            code: String(c.code ?? c.Code ?? ""),
            symbol: String(c.symbol ?? c.Symbol ?? c.code ?? c.Code ?? ""),
            isDefault: Boolean(c.isDefault) || String(c.symbol ?? c.Symbol ?? "").toLowerCase() === "ksh",
        })).sort((a: CurrencyOption, b: CurrencyOption) => (Number(!!b.isDefault) - Number(!!a.isDefault)) || a.symbol.localeCompare(b.symbol));
    }
    // response (if supplier has submitted already)
    const responseRaw = (container.response || (container as any).supplierResponse || (rfqRaw as any)?.response) as any;
    let response: SupplierResponse | undefined;
    let isSubmitted: boolean | undefined;
    if (responseRaw && typeof responseRaw === "object") {
        const itemsRaw = Array.isArray(responseRaw.items) ? responseRaw.items : [];
        const items: ResponseItem[] = itemsRaw.map((it: any) => ({
            rfqLineId: String(it.rfqLineId ?? it.lineId ?? it.RFQLineID ?? it.LineId ?? ""),
            // prefer the DB column names used by your backend
            quotedPrice: (it.QuotedPrice ?? it.quotedPrice ?? it.unitPrice) ?? null,
            totalPayable: (it.TotalPayable ?? it.totalPayable ?? it.totalPrice) ?? null,
            // leadTimeDays: (it.leadTimeDays ?? it.leadTime ?? null) ?? null, // Removed per item lead time
            comments: toStringSafe(it.Comments ?? it.comments ?? it.remark ?? null) || null,
        }));
        response = {
            currency: String(responseRaw.currency ?? responseRaw.currencyCode ?? ""),
            // accept DurationDays (DB) or durationDays
            durationDays: Number.isFinite(Number(responseRaw.DurationDays ?? responseRaw.durationDays)) ? Number(responseRaw.DurationDays ?? responseRaw.durationDays) : undefined,
            leadTimeDays: Number.isFinite(Number(responseRaw.LeadTimeDays ?? responseRaw.leadTimeDays)) ? Number(responseRaw.LeadTimeDays ?? responseRaw.leadTimeDays) : undefined,
            items,
            submittedAt: String(responseRaw.submittedAt ?? responseRaw.createdAt ?? responseRaw.created_on ?? ""),
        };
        isSubmitted = true;
    } else if (typeof (container as any).status === "string") {
        isSubmitted = ((container as any).status || "").toUpperCase() === "SUBMITTED";
    }
    const branding = (container.branding || (rfqRaw as any)?.branding) as any;
    return { header, lines, currencies, response, isSubmitted, branding };
}

export default function RfqDetailPage() {
    const params = useParams<{ rfqId: string }>();
    const router = useRouter();
    const rfqId = String(params?.rfqId || "");

    const [loading, setLoading] = useState<boolean>(true);
    const [saving, setSaving] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);
    const [details, setDetails] = useState<RfqDetails | null>(null);
    const [currencies, setCurrencies] = useState<CurrencyOption[]>([]);
    const [currency, setCurrency] = useState<string>("");
    const [durationDays, setDurationDays] = useState<string>("14");
    const [leadTimeDays, setLeadTimeDays] = useState<string>("");
    const [lineResponses, setLineResponses] = useState<Record<string, SupplierLineResponseInput & { comments?: string | null }>>({});
    const [isSubmitted, setIsSubmitted] = useState<boolean>(false);
    const [profile, setProfile] = useState<SupplierProfile | null>(null);

    const [clarifications, setClarifications] = useState<readonly { id: string; question: string; response?: string; rfqLineId?: string | null; createdOn?: string }[]>([]);
    const [clarQuestion, setClarQuestion] = useState<string>("");
    const [clarLineId, setClarLineId] = useState<string>("");
    const [postingClar, setPostingClar] = useState<boolean>(false);

    // const totalLines = useMemo(() => details?.lines?.length || 0, [details]);
    const grandTotal = useMemo(() => {
        return details?.lines.reduce((sum, l) => {
            const r = lineResponses[l.id];
            const t = r?.totalPrice != null ? r.totalPrice : (r?.unitPrice != null ? r.unitPrice * l.quantity : 0);
            return sum + (Number.isFinite(t) ? t : 0);
        }, 0) || 0;
    }, [details, lineResponses]);

    const loadCurrencies = useCallback(async (): Promise<void> => {
        try {
            const res = await fetch("/api/currencies", { headers: { Accept: "application/json" } });
            const data = await res.json();
            const list = Array.isArray(data?.data) ? data.data : [];
            const options: CurrencyOption[] = list.map((c: any) => ({
                id: String(c.id || ""),
                name: String(c.name || c.symbol || c.code || ""),
                code: String(c.code || ""),
                symbol: String(c.symbol || c.code || ""),
                isDefault: Boolean(c.isDefault) || String(c.symbol || "").toLowerCase() === "ksh",
            })).filter((c: CurrencyOption) => c.code);
            setCurrencies(options);
            const preferred = options.find((o) => o.isDefault) || options[0];
            if (!currency && preferred) setCurrency(preferred.code);
        } catch {
            // ignore
        }
    }, [currency]);

    const loadProfile = useCallback(async (): Promise<void> => {
        try {
            const res = await fetch("/api/third-party-profile", { headers: { Accept: "application/json" }, cache: "no-store" });
            const data = await res.json();
            const p = (data?.userProfile || data?.data || data) as any;
            if (p && typeof p === "object") {
                setProfile({
                    tradingName: p.tradingName || p.thirdParty?.thirdPartyName || "",
                    businessType: p.businessType || "",
                    registrationNumber: p.registrationNumber || p.thirdParty?.registrationNumber || "",
                    taxPin: p.taxPin || p.thirdParty?.taxPin || "",
                    email: p.email || "",
                    phone: p.phone || "",
                    country: p.country || p.thirdParty?.country || "",
                    physicalAddress: p.physicalAddress || p.thirdParty?.physicalAddress || "",
                });
            }
        } catch {
            setProfile(null);
        }
    }, []);

    const loadDetails = useCallback(async (): Promise<void> => {
        setLoading(true);
        setError(null);
        try {
            const res = await fetch(`/api/procurement/rfq-suppliers/${encodeURIComponent(rfqId)}`, { headers: { Accept: "application/json" }, cache: "no-store" });
            const contentType = res.headers.get("content-type") || "";
            const data = contentType.includes("application/json") ? await res.json() : await res.text();
            if (!res.ok) throw new Error((data && data.message) || "Failed to load RFQ");
            const normalized = normalizeDetails(data);
            if (!normalized) throw new Error("Invalid RFQ payload");
            setDetails(normalized);
            // if backend returned currencies, use them; else fall back to API
            if (normalized.currencies && normalized.currencies.length > 0) {
                const filtered = normalized.currencies.filter((c) => !!c.code);
                setCurrencies(filtered);
                const preferred = filtered.find((c) => c.isDefault) || filtered[0];
                if (preferred) setCurrency(preferred.code);
            }
            // if response present (already submitted), seed fields and lock editing
            if (normalized.response) {
                const responseMap: Record<string, SupplierLineResponseInput & { comments?: string | null }> = {};
                const respItems = (normalized.response as SupplierResponse).items || [];
                normalized.lines.forEach((l, idx) => {
                    // try to find matching item by rfqLineId
                    let it = respItems.find((x: any) => x && String(x.rfqLineId ?? x.lineId ?? x.RFQLineID ?? "") === String(l.id));
                    // fallback: use item at same index if available
                    if (!it) it = respItems[idx];

                    const itAny = it as any;
                    const quoted = (itAny?.quotedPrice ?? itAny?.QuotedPrice ?? itAny?.unitPrice) ?? undefined;
                    const total = (itAny?.totalPayable ?? itAny?.TotalPayable ?? itAny?.totalPrice) ?? (quoted != null ? Number((quoted * l.quantity).toFixed(2)) : undefined);
                    const comments = (itAny?.comments ?? itAny?.Comments ?? "") || "";

                    responseMap[l.id] = {
                        lineItemId: l.id,
                        unitPrice: quoted ?? undefined,
                        totalPrice: total ?? undefined,
                        // prefer per-item lead time, else use response.durationDays
                        // leadTimeDays: perItemLead ?? (normalized.response?.durationDays ?? undefined), // Removed
                        comments: comments ?? "",
                    } as any;
                });
                setLineResponses(responseMap);
                if (normalized.response.currency) setCurrency(normalized.response.currency);
                if (normalized.response.durationDays) setDurationDays(String(normalized.response.durationDays));
                if (normalized.response.leadTimeDays) setLeadTimeDays(String(normalized.response.leadTimeDays));
                setIsSubmitted(Boolean(normalized.isSubmitted));
            } else {
                // seed lineResponses (no previous submission)
                const seed: Record<string, SupplierLineResponseInput & { comments?: string | null }> = {};
                normalized.lines.forEach((l) => {
                    seed[l.id] = { lineItemId: l.id, unitPrice: undefined, totalPrice: undefined, comments: "" } as any;
                });
                setLineResponses(seed);
            }
        } catch (e: unknown) {
            const msg = e instanceof Error ? e.message : "Unable to load RFQ";
            setError(msg);
        } finally {
            setLoading(false);
        }
    }, [rfqId]);

    const loadClarifications = useCallback(async (): Promise<void> => {
        try {
            const res = await fetch(`/api/procurement/rfq-clarifications/${encodeURIComponent(rfqId)}`, { headers: { Accept: "application/json" }, cache: "no-store" });
            const contentType = res.headers.get("content-type") || "";
            const data = contentType.includes("application/json") ? await res.json() : await res.text();
            if (!res.ok) throw new Error((data && data.message) || "Failed to load clarifications");
            const list = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
            const normalized = list.map((row: any) => ({
                id: toStringSafe(row.id || row.clarificationId || crypto.randomUUID()),
                question: toStringSafe(row.question || row.subject || row.body || row.message || ""),
                response: toStringSafe(row.response || row.answer || row.adminResponse || ""),
                rfqLineId: toStringSafe(row.rfqLineId || row.lineId || ""),
                createdOn: toStringSafe(row.createdOn || row.created_at || row.createdAt || ""),
            }));
            setClarifications(normalized);
        } catch {
            setClarifications([]);
        }
    }, [rfqId]);

    useEffect(() => {
        if (!rfqId) return;
        loadDetails();
        loadClarifications();
        loadCurrencies();
        loadProfile();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [rfqId]);

    const onChangeUnitPrice = (lineId: string, value: string) => {
        setLineResponses((prev) => {
            const current = { ...(prev[lineId] || { lineItemId: lineId }) } as SupplierLineResponseInput & { comments?: string | null };
            const unitPrice = value ? parseFloat(value) : undefined;
            current.unitPrice = Number.isFinite(unitPrice as number) ? unitPrice : undefined;
            const line = details?.lines.find((l) => l.id === lineId);
            if (line && current.unitPrice != null) {
                current.totalPrice = Number((current.unitPrice * line.quantity).toFixed(2));
            }
            return { ...prev, [lineId]: current };
        });
    };

    // Total price is auto-calculated from unit price * quantity; no direct editing handler

    // Removed onChangeLeadTime as it is now header level

    const onChangeComments = (lineId: string, value: string) => {
        setLineResponses((prev) => {
            const current = { ...(prev[lineId] || { lineItemId: lineId }) } as SupplierLineResponseInput & { leadTimeDays?: number | null; comments?: string | null };
            current.comments = value ?? "";
            return { ...prev, [lineId]: current };
        });
    };

    const buildResponsePayload = useCallback((isDraft: boolean) => {
        const items = Object.values(lineResponses)
            .filter((r) => r && r.lineItemId)
            .map((r) => ({
                rfqLineId: r.lineItemId,
                quotedPrice: r.unitPrice ?? null,
                totalPayable: r.totalPrice ?? null,
                // leadTimeDays: r.leadTimeDays ?? null, // Removed
                comments: r.comments ?? null,
            }));
        const duration = Number.parseInt(durationDays || "0", 10);
        const leadTime = Number.parseInt(leadTimeDays || "0", 10);
        return {
            rfqId,
            currency: currency || undefined,
            durationDays: duration,
            leadTimeDays: leadTime,
            isDraft: isDraft || undefined,
            items,
        } as Record<string, unknown>;
    }, [rfqId, currency, durationDays, leadTimeDays, lineResponses]);

    const submitResponse = async (isDraft: boolean) => {
        try {
            setSaving(true);
            const duration = Number.parseInt(durationDays || "0", 10);
            if (!Number.isFinite(duration) || duration < 1) {
                setError("Offer validity (days) is required and must be at least 1");
                setSaving(false);
                return;
            }
            const leadTime = Number.parseInt(leadTimeDays || "0", 10);
            if (!Number.isFinite(leadTime) || leadTime < 1) {
                setError("Lead time (days) is required and must be at least 1");
                setSaving(false);
                return;
            }
            const payload = buildResponsePayload(isDraft);
            const res = await fetch("/api/procurement/rfq-responses", {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify(payload),
            });
            const contentType = res.headers.get("content-type") || "";
            const data = contentType.includes("application/json") ? await res.json() : await res.text();
            if (!res.ok) throw new Error((data && data.message) || "Failed to submit response");
            // After submit, navigate back to list
            router.push("/dashboard/rfqs");
        } catch (e: unknown) {
            const msg = e instanceof Error ? e.message : "Failed to submit";
            setError(msg);
        } finally {
            setSaving(false);
        }
    };

    const raiseClarification = async () => {
        if (!clarQuestion.trim()) return;
        try {
            setPostingClar(true);
            const body: Record<string, unknown> = { rfqId, question: clarQuestion.trim() };
            if (clarLineId) body.rfqLineId = clarLineId;
            const res = await fetch("/api/procurement/rfq-clarifications", {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify(body),
            });
            const contentType = res.headers.get("content-type") || "";
            const data = contentType.includes("application/json") ? await res.json() : await res.text();
            if (!res.ok) throw new Error((data && data.message) || "Failed to raise clarification");
            setClarQuestion("");
            setClarLineId("");
            await loadClarifications();
        } catch {
            // noop; error can be shown via setError if needed
        } finally {
            setPostingClar(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center gap-2 text-muted-foreground"><Loader2 className="h-4 w-4 animate-spin" /> Loading RFQ...</div>
        );
    }

    if (error) {
        return (
            <div className="text-destructive text-sm">{error}</div>
        );
    }

    if (!details) return null;

    const { header, lines } = details;

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-semibold leading-tight">{header.title}</h1>
                    <div className="text-sm text-muted-foreground mt-1">Ref: {header.referenceNumber}</div>
                    {header.closingDate && (
                        <div className="text-sm text-muted-foreground">Closing: {format(new Date(header.closingDate), "PPpp")}</div>
                    )}
                </div>
                <div className="flex items-center gap-2">
                    {isSubmitted ? (
                        <Badge variant="secondary">Submitted</Badge>
                    ) : (
                        <>
                            <Button variant="secondary" disabled={saving} onClick={() => submitResponse(true)}>
                                <Save className="h-4 w-4 mr-1" /> Save draft
                            </Button>
                            <Button disabled={saving} onClick={() => submitResponse(false)}>
                                <Send className="h-4 w-4 mr-1" /> Submit response
                            </Button>
                        </>
                    )}
                    <Button variant="outline" onClick={() => window.print()}>
                        <Printer className="h-4 w-4 mr-1" /> Print quotation
                    </Button>
                </div>
            </div>

            {header.description && (
                <Card className="p-4">
                    <div className="text-sm whitespace-pre-wrap leading-relaxed">{header.description}</div>
                </Card>
            )}

            {header.attachments && header.attachments.length > 0 && (
                <Card className="p-4">
                    <div className="font-medium mb-2">Attachments</div>
                    <ul className="space-y-1">
                        {header.attachments.map((doc) => (
                            <li key={doc.id} className="flex items-center gap-2 text-sm">
                                <FileText className="h-4 w-4 text-muted-foreground" />
                                <a href={doc.url} target="_blank" rel="noreferrer" className="text-primary hover:underline">{doc.fileName}</a>
                            </li>
                        ))}
                    </ul>
                </Card>
            )}

            <Card className="p-0 overflow-hidden print:hidden">
                <div className="p-4 border-b flex items-center gap-4">
                    <div className="w-48">
                        <Label htmlFor="currency">Currency</Label>
                        <Select value={currency} onValueChange={setCurrency} disabled={isSubmitted}>
                            <SelectTrigger id="currency" className="mt-1">
                                <SelectValue placeholder="Select currency" />
                            </SelectTrigger>
                            <SelectContent>
                                {currencies.map((c) => (
                                    <SelectItem key={c.code} value={c.code}>{c.symbol}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="w-48">
                        <Label htmlFor="durationDays">Offer validity (days)</Label>
                        <Input id="durationDays" inputMode="numeric" pattern="[0-9]*" className="mt-1" value={durationDays} onChange={(e) => setDurationDays(e.target.value.replace(/[^0-9]/g, ""))} disabled={isSubmitted} />
                    </div>
                    <div className="w-48">
                        <Label htmlFor="leadTimeDays">Lead time (days)</Label>
                        <Input id="leadTimeDays" inputMode="numeric" pattern="[0-9]*" className="mt-1" value={leadTimeDays} onChange={(e) => setLeadTimeDays(e.target.value.replace(/[^0-9]/g, ""))} disabled={isSubmitted} />
                    </div>
                    <div className="ml-auto text-sm text-muted-foreground">Grand total: <span className="font-medium">{currency || ""} {grandTotal.toLocaleString()}</span></div>
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Description</TableHead>
                            <TableHead>Qty</TableHead>
                            <TableHead>UOM</TableHead>
                            <TableHead>Unit price</TableHead>
                            <TableHead>Total price</TableHead>
                            {/* <TableHead>Lead time (days)</TableHead> */ /* Removed */}
                            <TableHead>Comments</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {lines.map((l) => {
                            const r = lineResponses[l.id] || { lineItemId: l.id };
                            return (
                                <TableRow key={l.id}>
                                    <TableCell className="max-w-[360px]">
                                        <div className="font-medium text-foreground">{l.description}</div>
                                    </TableCell>
                                    <TableCell>{l.quantity}</TableCell>
                                    <TableCell>{l.unitOfMeasure}</TableCell>
                                    <TableCell className="w-40">
                                        <Input inputMode="decimal" value={r.unitPrice != null ? String(r.unitPrice) : ""} onChange={(e) => onChangeUnitPrice(l.id, e.target.value.replace(/[^0-9.]/g, ""))} disabled={isSubmitted} />
                                    </TableCell>
                                    <TableCell className="w-40">
                                        <Input inputMode="decimal" value={r.totalPrice != null ? String(r.totalPrice) : ""} readOnly />
                                    </TableCell>
                                    {/* <TableCell className="w-40">
                                        <Input inputMode="numeric" pattern="[0-9]*" value={r.leadTimeDays != null ? String(r.leadTimeDays) : ""} onChange={(e) => onChangeLeadTime(l.id, e.target.value.replace(/[^0-9]/g, ""))} disabled={isSubmitted} />
                                    </TableCell> */ /* Removed */}
                                    <TableCell className="min-w-[240px]">
                                        <Textarea rows={2} value={r.comments || ""} onChange={(e) => onChangeComments(l.id, e.target.value)} disabled={isSubmitted} />
                                    </TableCell>
                                </TableRow>
                            );
                        })}
                    </TableBody>
                    <TableFooter>
                        <TableRow>
                            <TableCell colSpan={5} />
                            <TableCell className="font-medium">Grand total</TableCell>
                            <TableCell colSpan={2} className="font-semibold">{currency || ""} {grandTotal.toLocaleString()}</TableCell>
                        </TableRow>
                    </TableFooter>
                </Table>
            </Card>

            <Accordion type="single" collapsible className="print:hidden">
                <AccordionItem value="clarifications">
                    <AccordionTrigger>
                        <div className="flex items-center gap-2">
                            <MessageSquarePlus className="h-4 w-4" /> Clarifications
                            <Badge variant="secondary">{clarifications.length}</Badge>
                        </div>
                    </AccordionTrigger>
                    <AccordionContent>
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <Card className="p-4">
                                <div className="font-medium mb-3">Existing clarifications</div>
                                <div className="divide-y">
                                    {clarifications.length === 0 && (
                                        <div className="text-sm text-muted-foreground">No clarifications yet.</div>
                                    )}
                                    {clarifications.map((c) => (
                                        <div key={c.id} className="py-3">
                                            <div className="text-sm"><span className="font-medium">Q:</span> {c.question}</div>
                                            {c.response && (
                                                <div className="text-sm mt-1"><span className="font-medium">A:</span> {c.response}</div>
                                            )}
                                            <div className="text-[11px] text-muted-foreground mt-1">{c.createdOn ? format(new Date(c.createdOn), "PPpp") : ""}</div>
                                        </div>
                                    ))}
                                </div>
                            </Card>
                            <Card className="p-4">
                                <div className="font-medium mb-3">Raise a clarification</div>
                                <div className="grid grid-cols-1 gap-3">
                                    <div>
                                        <Label htmlFor="clar-line">Related line (optional)</Label>
                                        <Select value={clarLineId || "__general__"} onValueChange={(v) => setClarLineId(v === "__general__" ? "" : v)}>
                                            <SelectTrigger id="clar-line" className="mt-1">
                                                <SelectValue placeholder="General clarification" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="__general__">General clarification</SelectItem>
                                                {lines.map((l) => (
                                                    <SelectItem key={l.id} value={l.id}>Line {l.lineNumber || ""}: {l.description.substring(0, 40)}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div>
                                        <Label htmlFor="clar-q">Question</Label>
                                        <Textarea id="clar-q" rows={4} className="mt-1" value={clarQuestion} onChange={(e) => setClarQuestion(e.target.value)} />
                                    </div>
                                    <div className="flex justify-end gap-2">
                                        <Button variant="secondary" type="button" onClick={() => { setClarQuestion(""); setClarLineId(""); }}>
                                            Clear
                                        </Button>
                                        <Button type="button" onClick={raiseClarification} disabled={postingClar || !clarQuestion.trim()}>
                                            {postingClar && <Loader2 className="h-4 w-4 mr-1 animate-spin" />} Raise clarification
                                        </Button>
                                    </div>
                                </div>
                            </Card>
                        </div>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
            {/* Print-friendly quotation view */}
            <div className="hidden print:block">
                <div className="mb-8 flex justify-between items-start">
                    <div>
                        {details.branding?.logo && (
                            // eslint-disable-next-line @next/next/no-img-element
                            <img src={details.branding.logo} alt="Logo" className="h-16 mb-2 object-contain" />
                        )}
                        <div className="text-2xl font-bold text-primary">{details.branding?.name || "Organization Name"}</div>
                        <div className="text-sm text-muted-foreground italic">{details.branding?.motto || ""}</div>
                        <div className="text-sm mt-2">{details.branding?.address || ""}</div>
                        <div className="text-sm">{details.branding?.email || ""} | {details.branding?.phone || ""}</div>
                    </div>
                    <div className="text-right">
                        <div className="text-3xl font-bold text-gray-800 mb-1">QUOTATION</div>
                        <div className="text-sm text-muted-foreground">Date: {format(new Date(), "PP")}</div>
                        <div className="text-sm text-muted-foreground">Ref: {header.referenceNumber}</div>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-8 mb-8 border-t border-b py-4">
                    <div>
                        <div className="font-medium">Supplier</div>
                        <div>{profile?.tradingName || ""}</div>
                        <div className="text-sm text-muted-foreground">{profile?.email || ""}{profile?.phone ? ` · ${profile.phone}` : ""}</div>
                        <div className="text-sm text-muted-foreground">{profile?.physicalAddress || ""}{profile?.country ? `, ${profile.country}` : ""}</div>
                        <div className="text-sm">Reg No: {profile?.registrationNumber || ""}</div>
                        <div className="text-sm">Tax PIN: {profile?.taxPin || ""}</div>
                    </div>
                    <div>
                        <div className="font-bold uppercase text-xs text-muted-foreground mb-1">Quotation For</div>
                        <div className="font-semibold text-lg">{header.title}</div>
                        <div className="text-sm mt-1">Ref: {header.referenceNumber}</div>
                        {header.closingDate && <div className="text-sm">Closing: {format(new Date(header.closingDate), "PPpp")}</div>}
                    </div>
                </div>

                <div className="mb-6">
                    <div className="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div className="text-xs text-muted-foreground uppercase font-semibold">Currency</div>
                            <div className="font-medium">{currency}</div>
                        </div>
                        <div>
                            <div className="text-xs text-muted-foreground uppercase font-semibold">Offer Validity</div>
                            <div className="font-medium">{durationDays} Days</div>
                        </div>
                        <div>
                            <div className="text-xs text-muted-foreground uppercase font-semibold">Lead Time</div>
                            <div className="font-medium">{leadTimeDays} Days</div>
                        </div>
                    </div>
                </div>
                <table className="w-full text-sm border-collapse" style={{ borderSpacing: 0 }}>
                    <thead>
                        <tr>
                            <th className="border p-2 text-left">#</th>
                            <th className="border p-2 text-left">Item description</th>
                            <th className="border p-2 text-right">Qty</th>
                            <th className="border p-2 text-left">UOM</th>
                            <th className="border p-2 text-right">Unit price</th>
                            <th className="border p-2 text-right">Total price</th>
                            {/* <th className="border p-2 text-right">Lead time</th> */}
                            <th className="border p-2 text-left">Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        {details.lines.map((l, idx) => {
                            const r = lineResponses[l.id] || { lineItemId: l.id };
                            const total = r.totalPrice != null ? r.totalPrice : (r.unitPrice != null ? r.unitPrice * l.quantity : 0);
                            return (
                                <tr key={l.id}>
                                    <td className="border p-2">{idx + 1}</td>
                                    <td className="border p-2">{l.description}</td>
                                    <td className="border p-2 text-right">{l.quantity}</td>
                                    <td className="border p-2">{l.unitOfMeasure}</td>
                                    <td className="border p-2 text-right">{r.unitPrice != null ? r.unitPrice.toLocaleString() : ""}</td>
                                    <td className="border p-2 text-right">{total.toLocaleString()}</td>
                                    {/* <td className="border p-2 text-right">{r.leadTimeDays ?? ""}</td> */}
                                    <td className="border p-2">{r.comments || ""}</td>
                                </tr>
                            );
                        })}
                        <tr className="bg-gray-50">
                            <td className="border p-2 text-right font-bold" colSpan={5}>Grand Total</td>
                            <td className="border p-2 text-right font-bold">{currency} {grandTotal.toLocaleString()}</td>
                            <td className="border p-2" colSpan={1}></td>
                        </tr>
                    </tbody>
                </table>

                <div className="mt-12 pt-8 border-t">
                    <div className="flex justify-between items-end">
                        <div className="w-64 border-t border-gray-400 pt-2 text-center text-sm">
                            Authorized Signature
                        </div>
                        <div className="text-xs text-muted-foreground">
                            Generated by BR_ERP Supplier Portal
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

