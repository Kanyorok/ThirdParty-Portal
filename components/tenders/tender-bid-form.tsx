"use client";

import React, { useState, useRef, useEffect } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";
import { Input } from "@/components/common/input";
import { Textarea } from "@/components/common/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select";
import { Badge } from "@/components/common/badge";
import { Alert, AlertDescription } from "@/components/common/alert";
import { Separator } from "@/components/common/separator";
import { toast } from "sonner";
import {
  Upload,
  File,
  X,
  DollarSign,
  Shield,
  FileText,
  CheckCircle,
  AlertTriangle,
  Send,
  Save,
  Lock,
  RefreshCw,
  History,
} from "lucide-react";
import { cn } from "@/lib/utils";
import { resolveBidStatus } from "@/lib/bids/status";
import { parseJsonResponse } from "@/lib/parse-json-response";
import { Spinner } from "@/components/common/spinner";
import {
  TenderBidLineState,
  TenderTaxTreatment,
  TENDER_DEFAULT_VAT_RATE,
  RawTenderLineItem,
  RawBidLineItem,
  appendTenderBidItemsToFormData,
  calculateTenderLineTax,
  isTenderLineQuoted,
  parseBidLineItem,
  parseTenderLineItem,
  sumTenderLineTotals,
} from "@/lib/tender-bid";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/common/table";

// Simple module scoped counter for fallback IDs (avoids window any casts)
let docCounter = 0;

interface Tender {
  id: string;
  title: string;
  tenderNo: string;
  submissionDeadline: string;
  currency?: {
    code: string;
    symbol: string;
  };
  items?: RawTenderLineItem[];
}

function buildLineItemsFromTender(tender: Tender): TenderBidLineState[] {
  return (tender.items ?? []).map((raw) => {
    const item = parseTenderLineItem(raw);
    return {
      tenderItemId: item.id,
      itemName: item.manualItemDescription || item.itemName || `Item #${item.id}`,
      uom: item.uom,
      quantity: item.qtyToTender,
      unitPrice: "",
      taxTreatment: "vat_exclusive" as TenderTaxTreatment,
      discountPercentage: "0",
      withholdingTaxRate: "",
    };
  });
}

interface DocumentUpload {
  file: File;
  documentType: string;
  id: string;
}

interface TenderBidFormProps {
  tender: Tender;
  onFinalSubmitSuccess?: () => void; // callback to collapse modal & notify parent
  canSubmitBid?: boolean;
  submissionBlockedReason?: string;
  onResolveSubmissionBlock?: () => void;
}

// Raw shape is untyped: the ERP camelCases every response key, so this array's actual
// field names are read defensively via parseBidLineItem() rather than assumed here.
type BidSubmissionLineItem = RawBidLineItem;

interface BidSubmission {
  id?: number;
  bid_id?: number;
  tender_id?: number;
  bid_amount?: number;
  currency?: string;
  validity_period?: number;
  delivery_period?: number;
  payment_terms?: string;
  status?: string;
  bid_status?: string;
  submitted_at?: string;
  received_at?: string;
  documents_count?: number;
  submission_reference?: string;
  items?: BidSubmissionLineItem[];
}



type SubmittedBidInfo = {
  id: number | null;
  submittedAt: string | null;
  status: string | null;
};

export default function TenderBidForm({
  tender,
  onFinalSubmitSuccess,
  canSubmitBid = true,
  submissionBlockedReason,
  onResolveSubmissionBlock,
}: TenderBidFormProps) {
  const [bidData, setBidData] = useState({
    currency: tender.currency?.code || "KES",
    validityPeriod: "90",
    deliveryPeriod: "30",
    paymentTerms: "",
  });
  const [lineItems, setLineItems] = useState<TenderBidLineState[]>(() => buildLineItemsFromTender(tender));

  const [documents, setDocuments] = useState<DocumentUpload[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitType, setSubmitType] = useState<'draft' | 'final' | null>(null);
  const [isDragging, setIsDragging] = useState(false);
  const [existingBidId, setExistingBidId] = useState<number | null>(null);
  const [isEditingDraft, setIsEditingDraft] = useState(false);
  const [submittedBidInfo, setSubmittedBidInfo] = useState<SubmittedBidInfo | null>(null);
  const [isLoadingExisting, setIsLoadingExisting] = useState(true);
  const [bidHistory, setBidHistory] = useState<BidSubmission[]>([]);
  const [isLoadingHistory, setIsLoadingHistory] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const documentTypes = [
    { value: "technical", label: "Technical Proposal" },
    { value: "financial", label: "Financial Proposal" },
    { value: "compliance", label: "Compliance Documents" },
    { value: "bond", label: "Bid Bond" },
    { value: "other", label: "Other" },
  ];

  const currencies = [
    { code: "KES", symbol: "KSh", name: "Kenyan Shilling" },
    { code: "USD", symbol: "$", name: "US Dollar" },
    { code: "EUR", symbol: "€", name: "Euro" },
    { code: "GBP", symbol: "£", name: "British Pound" },
  ];

  const normalizeBidStatus = (bid: BidSubmission) =>
    resolveBidStatus(bid.status || bid.bid_status, {
      hasSubmittedTimestamp: Boolean(bid.submitted_at || bid.received_at),
    });

  const getBidTimestamp = (bid: BidSubmission) =>
    bid.submitted_at || bid.received_at || "";

  const hydrateBidData = (bid: BidSubmission) => {
    setBidData({
      currency: bid.currency || tender.currency?.code || "KES",
      validityPeriod: bid.validity_period?.toString() || "90",
      deliveryPeriod: bid.delivery_period?.toString() || "30",
      paymentTerms: bid.payment_terms || "",
    });

    const existingByTenderItemId = new Map(
      (bid.items ?? []).map((raw) => {
        const parsed = parseBidLineItem(raw);
        return [parsed.tenderItemId, parsed];
      })
    );
    setLineItems(
      buildLineItemsFromTender(tender).map((line) => {
        const existing = existingByTenderItemId.get(line.tenderItemId);
        if (!existing) return line;

        return {
          ...line,
          unitPrice: existing.quotedPrice != null ? String(existing.quotedPrice) : "",
          taxTreatment: existing.taxType === "Exempt"
            ? "no_vat"
            : existing.isTaxInclusive
              ? "vat_inclusive"
              : "vat_exclusive",
          discountPercentage: existing.discountPercentage != null ? String(existing.discountPercentage) : "0",
          withholdingTaxRate: existing.withholdingTaxRate != null ? String(existing.withholdingTaxRate) : "",
        };
      })
    );
  };

  const fetchBidContext = async () => {
    if (!tender.id) return;
    setIsLoadingExisting(true);
    setIsLoadingHistory(true);

    try {
      const noParam = tender.tenderNo ? `&tenderNo=${encodeURIComponent(tender.tenderNo)}` : "";
      const response = await fetch(`/api/tender-bids?all=true&tenderId=${tender.id}${noParam}`);
      const data = await parseJsonResponse<{ data?: BidSubmission[] }>(response);
      const list: BidSubmission[] = Array.isArray(data?.data) ? data.data : [];
      const sorted = [...list].sort((a, b) => {
        const left = getBidTimestamp(a) ? new Date(getBidTimestamp(a)).getTime() : 0;
        const right = getBidTimestamp(b) ? new Date(getBidTimestamp(b)).getTime() : 0;
        return right - left;
      });

      setBidHistory(sorted);

      const submitted = sorted.find((item) => normalizeBidStatus(item) === "submitted");
      const draft = sorted.find((item) => normalizeBidStatus(item) === "draft");
      const existing = submitted ?? draft ?? null;

      if (!existing) {
        setExistingBidId(null);
        setIsEditingDraft(false);
        setSubmittedBidInfo(null);
        return;
      }

      const existingStatus = normalizeBidStatus(existing);

      hydrateBidData(existing);
      setExistingBidId(existing.id || existing.bid_id || null);
      setIsEditingDraft(existingStatus === "draft");
      setSubmittedBidInfo(
        existingStatus === "submitted"
          ? {
            id: existing.id || existing.bid_id || null,
            submittedAt: getBidTimestamp(existing) || null,
            status: String(existing.status || existing.bid_status || "").trim() || null,
          }
          : null
      );
    } catch {
      // Silent fail for existing bid check
      setBidHistory([]);
      setSubmittedBidInfo(null);
    } finally {
      setIsLoadingExisting(false);
      setIsLoadingHistory(false);
    }
  };

  // Check for existing bid on component load
  useEffect(() => {
    if (tender.id) {
      fetchBidContext();
    } else {
      setIsLoadingExisting(false);
      setIsLoadingHistory(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tender.id, tender.currency?.code]);

  const processFiles = (files: FileList | null) => {
    if (!files) return;

    Array.from(files).forEach(file => {
      // Check file size (max 10MB)
      if (file.size > 10 * 1024 * 1024) {
        toast.error(`File ${file.name} is too large. Maximum size is 10MB.`);
        return;
      }

      // Check file type
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png'
      ];

      if (!allowedTypes.includes(file.type)) {
        toast.error(`File ${file.name} has an unsupported format.`);
        return;
      }

      // Generate deterministic-ish incremental fallback id if crypto not available
      const fallbackId = `doc_${++docCounter}`;
      const newDocument: DocumentUpload = {
        file,
        documentType: "other", // Default type
        id: (typeof crypto !== 'undefined' && 'randomUUID' in crypto) ? (crypto as unknown as { randomUUID: () => string }).randomUUID() : fallbackId,
      };

      setDocuments(prev => [...prev, newDocument]);
      toast.success(`File ${file.name} added successfully.`);
    });
  };

  const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = event.target.files;
    processFiles(files);

    // Reset file input
    if (fileInputRef.current) {
      fileInputRef.current.value = "";
    }
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (!isDragging) setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);

    const files = e.dataTransfer.files;
    processFiles(files);
  };

  const handleUploadClick = () => {
    fileInputRef.current?.click();
  };

  const removeDocument = (id: string) => {
    setDocuments(prev => prev.filter(doc => doc.id !== id));
  };

  const updateDocumentType = (id: string, type: string) => {
    setDocuments(prev =>
      prev.map(doc =>
        doc.id === id ? { ...doc, documentType: type } : doc
      )
    );
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const formatBidDate = (value?: string) => {
    if (!value) return "Recent";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "Recent";
    return date.toLocaleString();
  };

  const getBidStatusTone = (status: string) => {
    switch (status) {
      case "submitted":
        return "border-emerald-200 bg-emerald-50 text-emerald-700";
      case "draft":
        return "border-amber-200 bg-amber-50 text-amber-700";
      case "declined":
        return "border-rose-200 bg-rose-50 text-rose-700";
      default:
        return "border-slate-200 bg-slate-100 text-slate-600";
    }
  };

  const formatBidAmount = (value?: number, currency?: string) => {
    if (typeof value !== "number" || Number.isNaN(value)) return "Amount pending";
    return `${currency || bidData.currency || "KES"} ${value.toLocaleString()}`;
  };

  const formatServerStatus = (value?: string | null) => {
    const normalized = String(value || "").trim();
    if (!normalized) return "Submitted";
    return normalized
      .replace(/[_-]+/g, " ")
      .split(" ")
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase())
      .join(" ");
  };

  const buildExistingBidMessage = (params: {
    id?: number | null;
    submittedAt?: string | null;
    status?: string | null;
    fallback?: string;
  }) => {
    const parts: string[] = [];
    if (params.id != null) parts.push(`Bid ID #${params.id}`);
    if (params.submittedAt) {
      const date = new Date(params.submittedAt);
      if (!Number.isNaN(date.getTime())) parts.push(`Submitted ${date.toLocaleString()}`);
    }
    if (params.status) parts.push(`Status ${formatServerStatus(params.status)}`);
    const suffix = parts.length ? ` (${parts.join(" • ")})` : "";
    return `${params.fallback || "A final bid has already been submitted for this tender."}${suffix}`;
  };

  const validateBid = () => {

    if (!tender.id) {
      toast.error("Tender ID is missing");
      return false;
    }

    if (lineItems.length === 0) {
      toast.error("This tender has no line items to bid on.");
      return false;
    }

    const unquotedItems = lineItems.filter((line) => !isTenderLineQuoted(line));
    if (unquotedItems.length > 0) {
      toast.error(`Please enter a unit price for every tendered item (${unquotedItems.length} remaining).`);
      return false;
    }

    if (!bidData.currency || bidData.currency.trim() === '') {
      toast.error("Please select a currency");
      return false;
    }

    if (!bidData.validityPeriod || bidData.validityPeriod.trim() === '' || parseInt(bidData.validityPeriod) <= 0) {
      toast.error("Please enter a valid validity period");
      return false;
    }

    if (!bidData.deliveryPeriod || bidData.deliveryPeriod.trim() === '' || parseInt(bidData.deliveryPeriod) <= 0) {
      toast.error("Please enter a valid delivery period");
      return false;
    }

    return true;
  };

  const handleSubmit = async (type: 'draft' | 'final') => {
    if (!canSubmitBid) {
      toast.error(submissionBlockedReason || "You need to accept the tender invitation before submitting a bid.");
      return;
    }

    // Prevent resubmission if a final bid already exists
    if (existingBidId && !isEditingDraft) {
      toast.error(
        buildExistingBidMessage({
          id: submittedBidInfo?.id ?? existingBidId,
          submittedAt: submittedBidInfo?.submittedAt,
          status: submittedBidInfo?.status,
          fallback: "You have already submitted a final bid for this tender. No further changes are allowed.",
        })
      );
      return;
    }

    // Basic validation for both draft and final
    if (!tender.id) {
      toast.error("Tender ID is missing");
      return;
    }

    // For draft: allow submission with basic data, but warn about missing documents
    if (type === 'draft') {
      if (documents.length === 0) {
        toast.warning("⚠️ Saving draft without documents. You can add documents later.");
      }
    }

    // For final: require full validation
    if (type === 'final' && !validateBid()) {
      return;
    }

    setIsSubmitting(true);
    setSubmitType(type);
    const submitToastId = toast.loading(
      type === "draft" ? "Saving draft bid..." : "Submitting bid..."
    );

    try {
      const formData = new FormData();

      // Build form data

      formData.append('tenderId', String(tender.id));
      formData.append('tenderNo', String(tender.tenderNo || ""));
      formData.append('currency', String(bidData.currency));
      formData.append('validityPeriod', String(bidData.validityPeriod));
      formData.append('deliveryPeriod', String(bidData.deliveryPeriod));
      formData.append('paymentTerms', String(bidData.paymentTerms));
      formData.append('status', type === 'draft' ? 'draft' : 'submitted');
      appendTenderBidItemsToFormData(formData, lineItems);

      // Add documents
      documents.forEach((doc) => {
        formData.append('documents', doc.file);
        formData.append('documentTypes', doc.documentType);
      });

      const response = await fetch('/api/tender-bids', {
        method: 'POST',
        body: formData,
      });

      const payload: any = await parseJsonResponse(response);

      if (!response.ok) {
        const existingBidPayload = payload.data && typeof payload.data === "object" ? payload.data : null;
        const existingBidId = Number(existingBidPayload?.existing_bid_id);
        const hasExistingBid = Number.isFinite(existingBidId) && existingBidId > 0;
        const existingSubmittedAt = typeof existingBidPayload?.submitted_at === "string"
          ? existingBidPayload.submitted_at
          : null;
        const existingStatus = typeof existingBidPayload?.status === "string"
          ? existingBidPayload.status
          : null;

        if (hasExistingBid) {
          setExistingBidId(existingBidId);
          setIsEditingDraft(false);
          setSubmittedBidInfo({
            id: existingBidId,
            submittedAt: existingSubmittedAt,
            status: existingStatus,
          });
          await fetchBidContext();
          throw new Error(
            buildExistingBidMessage({
              id: existingBidId,
              submittedAt: existingSubmittedAt,
              status: existingStatus,
              fallback: payload.message || "A final bid has already been submitted for this tender.",
            })
          );
        }

        if (response.status === 422 && payload.errors) {
          // Handle validation errors from ERP
          const errorMessages: string[] = [];
          const fieldLabelMap: Record<string, string> = {
            tender_id: "Tender",
            bid_amount: "Bid Amount",
            bid_documents: "Documents",
            currency: "Currency",
            validity_period: "Validity Period",
            delivery_period: "Delivery Period",
            status: "Status",
            items: "Tendered Items",
          };

          Object.entries(payload.errors as Record<string, string[] | string>).forEach(([field, value]) => {
            const label = fieldLabelMap[field] || field.replace(/_/g, " ");
            const text = Array.isArray(value) ? value[0] : String(value);
            if (text) errorMessages.push(`${label}: ${text}`);
          });

          const hasBidDocValidation = Object.keys(payload.errors as Record<string, unknown>).some(
            (key) => key === "bid_documents" || key.startsWith("bid_documents.")
          );
          if (hasBidDocValidation && documents.length === 0 && type === "final") {
            errorMessages.unshift("Backend rule: final submission currently requires at least one document.");
          }

          const errorMessage = errorMessages.length > 0
            ? errorMessages.join(', ')
            : payload.message || 'Validation failed';

          throw new Error(errorMessage);
        } else if (response.status === 409) {
          throw new Error(payload.message || "A final bid has already been submitted for this tender.");
        } else if (response.status === 403) {
          // Handle business logic errors from ERP (like expired deadlines)
          const messageText = (payload.message || payload.error || "").toString().toLowerCase();
          if (payload.invitation_status || messageText.includes("invitation")) {
            throw new Error(payload.message || "You must accept the tender invitation before submitting a bid.");
          }

          if (payload.tender_status === "cl" || payload.submission_deadline) {
            const deadline = payload.submission_deadline
              ? ` (Deadline was: ${new Date(payload.submission_deadline).toLocaleString()})`
              : "";
            throw new Error(`${payload.message || 'Submission not allowed'}${deadline}`);
          } else {
            throw new Error(payload.message || 'This action is not allowed');
          }
        } else if (response.status >= 500 || response.status === 502) {
          // ERP server error surfaced by proxy
          throw new Error('ERP is currently unavailable. Please try again in a moment.');
        } else {
          throw new Error(payload.message || payload.error || 'Failed to submit bid');
        }
      }

      // Show appropriate success message based on whether fallback was used
      const effectiveStatus = String(payload.effective_status || payload.data?.status || payload.data?.bid_status || "").toLowerCase();
      const fallbackToDraft = Boolean(payload.fallback_to_draft) || (type === "final" && effectiveStatus === "draft");
      const message = payload.fallback ?
        (type === 'draft'
          ? "Bid saved as draft successfully! (Mock mode - ERP not connected)"
          : "Bid submitted successfully! (Mock mode - ERP not connected)"
        ) :
        fallbackToDraft
          ? "Final submission currently requires documents. Your bid has been saved as draft."
          : (type === 'draft'
            ? "Bid saved as draft successfully."
            : "Bid submitted successfully. Your documents are encrypted and stored securely."
          );

      if (fallbackToDraft) {
        toast.dismiss(submitToastId);
        toast.warning(message);
      } else {
        toast.dismiss(submitToastId);
        toast.success(message);
      }

      // Reset form if final submission
      if (type === 'final' && !fallbackToDraft) {
        setBidData({
          currency: tender.currency?.code || "KES",
          validityPeriod: "90",
          deliveryPeriod: "30",
          paymentTerms: "",
        });
        setLineItems(buildLineItemsFromTender(tender));
        setDocuments([]);
        setExistingBidId(payload.data?.bid_id || payload.data?.Id || null);
        setIsEditingDraft(false); // Final submission, no longer editing draft

        // Trigger external success handler (e.g., close modal / collapse dialog)
        if (onFinalSubmitSuccess) {
          // Slight delay to let toast render before closing
          setTimeout(() => {
            onFinalSubmitSuccess();
          }, 400);
        }
      } else {
        // For draft saves, update the existing bid ID if we got one back
        setExistingBidId(payload.data?.bid_id || payload.data?.Id || existingBidId);
        setIsEditingDraft(true); // Still in draft mode
      }

      await fetchBidContext();

    } catch (err) {
      toast.dismiss(submitToastId);
      toast.error(
        err instanceof Error
          ? err.message
          : "Failed to submit bid"
      );
    } finally {
      toast.dismiss(submitToastId);
      setIsSubmitting(false);
      setSubmitType(null);
    }
  };

  const cardTone = "gap-0 rounded-xl border border-slate-200/80 bg-white py-0 shadow-none";
  const finalBidLocked = !isLoadingExisting && existingBidId !== null && !isEditingDraft;
  const validityNumber = Number(bidData.validityPeriod);
  const deliveryNumber = Number(bidData.deliveryPeriod);
  const quotedLineCount = lineItems.filter(isTenderLineQuoted).length;
  const hasAllItemsQuoted = lineItems.length > 0 && quotedLineCount === lineItems.length;
  const hasCurrency = Boolean(bidData.currency?.trim());
  const hasValidity = Number.isFinite(validityNumber) && validityNumber > 0;
  const hasDelivery = Number.isFinite(deliveryNumber) && deliveryNumber > 0;
  const hasDocuments = documents.length > 0;
  const readinessScore = [hasAllItemsQuoted, hasCurrency, hasValidity, hasDelivery].filter(Boolean).length;
  const readinessPercent = Math.round((readinessScore / 4) * 100);
  const lineTotals = sumTenderLineTotals(
    lineItems.map((line) =>
      calculateTenderLineTax(
        Number(line.unitPrice) || 0,
        line.quantity,
        line.taxTreatment,
        Number(line.withholdingTaxRate) || 0,
        Number(line.discountPercentage) || 0
      )
    )
  );
  const finalDisabledReason = finalBidLocked
    ? "Final bid already submitted. This tender is locked for further submission."
    : !canSubmitBid
      ? (submissionBlockedReason || "You cannot submit a bid right now.")
      : !hasAllItemsQuoted
        ? `Quote a unit price for every tendered item (${quotedLineCount}/${lineItems.length} done).`
        : !hasCurrency
          ? "Select a currency to continue."
          : !hasValidity
            ? "Enter a valid bid validity period."
            : !hasDelivery
              ? "Enter a valid delivery period."
              : null;

  return (
    <div className="space-y-3">
      {isLoadingExisting && (
        <Alert className="border-slate-200 bg-slate-50 py-2 text-slate-700">
          <AlertTriangle className="h-4 w-4" />
          <AlertDescription className="text-xs">
            Checking existing bids...
          </AlertDescription>
        </Alert>
      )}

      {!isLoadingExisting && isEditingDraft && (
        <Alert className="border-amber-200 bg-amber-50 py-2 text-amber-800">
          <FileText className="h-4 w-4" />
          <AlertDescription className="text-xs">
            Draft loaded. Update details and submit when ready.
          </AlertDescription>
        </Alert>
      )}

      {finalBidLocked && (
        <Alert className="border-emerald-200 bg-emerald-50 py-2 text-emerald-800">
          <CheckCircle className="h-4 w-4" />
          <AlertDescription className="text-xs">
            <div className="space-y-1">
              <p>Final bid already submitted. Editing is locked.</p>
              {submittedBidInfo && (
                <p className="text-[11px] text-emerald-700">
                  {buildExistingBidMessage({
                    id: submittedBidInfo.id,
                    submittedAt: submittedBidInfo.submittedAt,
                    status: submittedBidInfo.status,
                    fallback: "Submission recorded",
                  })}
                </p>
              )}
            </div>
          </AlertDescription>
        </Alert>
      )}

      {!canSubmitBid && !finalBidLocked && (
        <Alert className="border-amber-200 bg-amber-50 py-2 text-amber-800">
          <AlertTriangle className="h-4 w-4" />
          <AlertDescription className="flex flex-wrap items-center justify-between gap-2 text-xs">
            <span>{submissionBlockedReason || "Accept the tender invitation before submitting a bid."}</span>
            {onResolveSubmissionBlock && (
              <Button
                type="button"
                size="sm"
                variant="outline"
                onClick={onResolveSubmissionBlock}
                className="h-7 border-amber-300 bg-white px-2 text-[11px] font-semibold text-amber-800 hover:bg-amber-100"
              >
                Go to response
              </Button>
            )}
          </AlertDescription>
        </Alert>
      )}

      <Card className={cardTone}>
        <CardHeader className="border-b border-slate-100 px-4 py-3">
          <CardTitle className="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
            <span className="flex items-center gap-2">
              <CheckCircle className="h-4 w-4 text-indigo-600" />
              Submission readiness
            </span>
            <Badge className="rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">
              {readinessPercent}% ready
            </Badge>
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-2 px-4 py-3">
          <div className="grid gap-2 sm:grid-cols-2">
            {[
              { done: hasAllItemsQuoted, label: `All items quoted (${quotedLineCount}/${lineItems.length})` },
              { done: hasCurrency, label: "Currency selected" },
              { done: hasValidity, label: "Validity period set" },
              { done: hasDelivery, label: "Delivery period set" },
            ].map((item) => (
              <div
                key={item.label}
                className={cn(
                  "flex items-center gap-2 rounded-lg border px-2.5 py-2 text-xs",
                  item.done
                    ? "border-emerald-200 bg-emerald-50 text-emerald-700"
                    : "border-slate-200 bg-slate-50 text-slate-600"
                )}
              >
                <span className={cn("h-2 w-2 rounded-full", item.done ? "bg-emerald-500" : "bg-slate-400")} />
                <span className="font-medium">{item.label}</span>
              </div>
            ))}
          </div>
          {!hasDocuments && (
            <p className="text-[11px] text-amber-700">
              Add at least one document for higher final-submission success.
            </p>
          )}
          {finalDisabledReason && (
            <p className="text-[11px] text-slate-600">
              {finalDisabledReason}
            </p>
          )}
        </CardContent>
      </Card>

      <Card className={cardTone}>
        <CardHeader className="border-b border-slate-100 px-4 py-3">
          <CardTitle className="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
            <span className="flex items-center gap-2">
              <History className="h-4 w-4 text-indigo-600" />
              Recent bids
            </span>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              onClick={() => fetchBidContext()}
              disabled={isSubmitting || isLoadingHistory}
              className="h-7 px-2 text-slate-500 hover:bg-slate-100"
            >
              <RefreshCw className={cn("h-3.5 w-3.5", isLoadingHistory && "animate-spin")} />
            </Button>
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-2 px-4 py-3">
          {isLoadingHistory ? (
            <p className="text-xs text-slate-500">Loading bid history...</p>
          ) : bidHistory.length === 0 ? (
            <p className="text-xs text-slate-500">No bids yet for this tender.</p>
          ) : (
            bidHistory.slice(0, 3).map((bid) => {
              const status = normalizeBidStatus(bid);
              const timestamp = getBidTimestamp(bid);
              return (
                <div key={bid.id || bid.bid_id || `${status}-${timestamp}`} className="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/70 px-3 py-2">
                  <div className="min-w-0">
                    <p className="truncate text-xs font-medium text-slate-800">
                      {formatBidAmount(bid.bid_amount, bid.currency)}
                    </p>
                    <p className="text-[11px] text-slate-500">
                      {formatBidDate(timestamp)}
                    </p>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge className={cn("rounded-full border px-2 py-0.5 text-[10px] font-semibold capitalize", getBidStatusTone(status))}>
                      {status || "unknown"}
                    </Badge>
                    <Badge className="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-medium text-slate-600">
                      {bid.documents_count || 0} docs
                    </Badge>
                  </div>
                </div>
              );
            })
          )}
        </CardContent>
      </Card>

      <Alert className="border-indigo-200 bg-indigo-50/50 py-2 text-indigo-800">
        <Shield className="h-4 w-4" />
        <AlertDescription className="text-xs">
          Documents remain encrypted and accessible only through approved opening flow.
        </AlertDescription>
      </Alert>

      <Card className={cardTone}>
        <CardHeader className="border-b border-slate-100 px-4 py-3">
          <CardTitle className="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
            <span className="flex items-center gap-2">
              <DollarSign className="h-4 w-4 text-indigo-600" />
              Tendered items
            </span>
            <Badge className="rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">
              {quotedLineCount}/{lineItems.length} quoted
            </Badge>
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-3 px-4 py-3">
          {lineItems.length === 0 ? (
            <p className="text-xs text-slate-500">This tender has no line items to bid on.</p>
          ) : (
            <div className="overflow-x-auto rounded-lg border border-slate-200">
              <Table>
                <TableHeader>
                  <TableRow className="bg-slate-50">
                    <TableHead className="text-xs">Item</TableHead>
                    <TableHead className="text-xs">Qty</TableHead>
                    <TableHead className="text-xs">Unit price</TableHead>
                    <TableHead className="text-xs">Tax</TableHead>
                    <TableHead className="text-xs">Discount %</TableHead>
                    <TableHead className="text-xs">Net</TableHead>
                    <TableHead className="text-xs">Gross</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {lineItems.map((line, index) => {
                    const totals = calculateTenderLineTax(
                      Number(line.unitPrice) || 0,
                      line.quantity,
                      line.taxTreatment,
                      Number(line.withholdingTaxRate) || 0,
                      Number(line.discountPercentage) || 0
                    );
                    const updateLine = (patch: Partial<TenderBidLineState>) => {
                      setLineItems((prev) => prev.map((l, i) => (i === index ? { ...l, ...patch } : l)));
                    };

                    return (
                      <TableRow key={line.tenderItemId}>
                        <TableCell className="text-xs font-medium text-slate-800">{line.itemName}</TableCell>
                        <TableCell className="text-xs text-slate-600">
                          {line.quantity}{line.uom ? ` ${line.uom}` : ""}
                        </TableCell>
                        <TableCell>
                          <Input
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            value={line.unitPrice}
                            onChange={(e) => updateLine({ unitPrice: e.target.value })}
                            className="h-8 w-28 bg-white text-xs"
                          />
                        </TableCell>
                        <TableCell>
                          <Select
                            value={line.taxTreatment}
                            onValueChange={(value) => updateLine({ taxTreatment: value as TenderTaxTreatment })}
                          >
                            <SelectTrigger className="h-8 w-32 bg-white text-xs">
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="vat_exclusive">VAT excl. ({TENDER_DEFAULT_VAT_RATE}%)</SelectItem>
                              <SelectItem value="vat_inclusive">VAT incl. ({TENDER_DEFAULT_VAT_RATE}%)</SelectItem>
                              <SelectItem value="no_vat">Exempt</SelectItem>
                            </SelectContent>
                          </Select>
                        </TableCell>
                        <TableCell>
                          <Input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            value={line.discountPercentage}
                            onChange={(e) => updateLine({ discountPercentage: e.target.value })}
                            className="h-8 w-20 bg-white text-xs"
                          />
                        </TableCell>
                        <TableCell className="text-xs font-semibold text-slate-800">
                          {totals.netAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </TableCell>
                        <TableCell className="text-xs font-semibold text-slate-800">
                          {totals.grossAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </div>
          )}

          {lineItems.length > 0 && (
            <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
              <div className="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
                <div className="text-[10px] uppercase text-slate-500">Net total</div>
                <div className="text-sm font-semibold text-slate-800">{lineTotals.netAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
              </div>
              <div className="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
                <div className="text-[10px] uppercase text-slate-500">Tax</div>
                <div className="text-sm font-semibold text-slate-800">{lineTotals.taxAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
              </div>
              <div className="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
                <div className="text-[10px] uppercase text-slate-500">Gross total</div>
                <div className="text-sm font-semibold text-slate-800">{lineTotals.grossAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
              </div>
              <div className="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2">
                <div className="text-[10px] uppercase text-slate-500">Withholding tax</div>
                <div className="text-sm font-semibold text-slate-800">{lineTotals.withholdingTaxAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      <Card className={cardTone}>
        <CardHeader className="border-b border-slate-100 px-4 py-3">
          <CardTitle className="flex items-center gap-2 text-sm font-semibold text-slate-800">
            <DollarSign className="h-4 w-4 text-indigo-600" />
            Bid details
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-3 px-4 py-3">
          <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div className="space-y-1.5">
              <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">Currency</label>
              <Select
                value={bidData.currency}
                onValueChange={(value) => setBidData(prev => ({ ...prev, currency: value }))}
              >
                <SelectTrigger className="h-9 bg-white text-xs">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {currencies.map(currency => (
                    <SelectItem key={currency.code} value={currency.code}>
                      {currency.code}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-1.5">
              <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">Validity (days)</label>
              <Input
                type="number"
                placeholder="90"
                value={bidData.validityPeriod}
                onChange={(e) => setBidData(prev => ({ ...prev, validityPeriod: e.target.value }))}
                min="1"
                className="h-9 bg-white text-sm"
              />
            </div>

            <div className="space-y-1.5">
              <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">Delivery (days)</label>
              <Input
                type="number"
                placeholder="30"
                value={bidData.deliveryPeriod}
                onChange={(e) => setBidData(prev => ({ ...prev, deliveryPeriod: e.target.value }))}
                min="1"
                className="h-9 bg-white text-sm"
              />
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment terms</label>
            <Textarea
              placeholder="Preferred payment terms"
              value={bidData.paymentTerms}
              onChange={(e) => setBidData(prev => ({ ...prev, paymentTerms: e.target.value }))}
              className="min-h-[68px] bg-white text-sm"
            />
          </div>
        </CardContent>
      </Card>

      <Card className={cardTone}>
        <CardHeader className="border-b border-slate-100 px-4 py-3">
          <CardTitle className="flex items-center gap-2 text-sm font-semibold text-slate-800">
            <FileText className="h-4 w-4 text-indigo-600" />
            Supporting documents
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-3 px-4 py-3">
          <div
            className={cn(
              "cursor-pointer rounded-lg border border-dashed p-5 text-center transition-colors",
              isDragging
                ? "border-indigo-500 bg-indigo-50/60"
                : "border-slate-300/80 bg-slate-50/70 hover:border-indigo-300 hover:bg-indigo-50/40"
            )}
            onDragOver={handleDragOver}
            onDragLeave={handleDragLeave}
            onDrop={handleDrop}
            onClick={handleUploadClick}
          >
            <Upload className={cn("mx-auto mb-2 h-8 w-8 transition-colors", isDragging ? "text-indigo-500" : "text-slate-400")} />
            <p className="text-sm font-medium text-slate-700">
              {isDragging ? "Drop files here" : "Upload files"}
            </p>
            <p className="mt-1 text-xs text-slate-500">
              PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG up to 10MB each
            </p>
            <input
              ref={fileInputRef}
              type="file"
              multiple
              className="hidden"
              onChange={handleFileUpload}
              accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
            />
          </div>

          <Alert className="border-slate-200 bg-slate-50 py-2 text-slate-700">
            <Lock className="h-4 w-4" />
            <AlertDescription className="text-xs">
              Supporting documents are optional, but strongly recommended for final submission.
            </AlertDescription>
          </Alert>

          {documents.length > 0 && (
            <div className="space-y-2">
              <Separator />
              <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Uploaded ({documents.length})
              </h4>
              <div className="space-y-2">
                {documents.map((doc) => (
                  <div key={doc.id} className="flex flex-col gap-2 rounded-lg border border-slate-200 bg-white p-2.5 sm:flex-row sm:items-center">
                    <File className="h-5 w-5 text-indigo-600" />
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-xs font-medium text-slate-800">{doc.file.name}</p>
                      <p className="text-[11px] text-slate-500">
                        {formatFileSize(doc.file.size)} • {doc.file.type || "File"}
                      </p>
                    </div>
                    <Select
                      value={doc.documentType}
                      onValueChange={(value) => updateDocumentType(doc.id, value)}
                    >
                      <SelectTrigger className="h-8 w-full bg-white text-xs sm:w-[150px]">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        {documentTypes.map(type => (
                          <SelectItem key={type.value} value={type.value}>
                            {type.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      onClick={() => removeDocument(doc.id)}
                      disabled={isSubmitting}
                      className="h-8 w-8 p-0 text-slate-500 hover:bg-slate-100"
                    >
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      <Card className={cardTone}>
        <CardContent className="space-y-3 px-4 py-3">
          <div className="rounded-lg border border-indigo-200 bg-indigo-50/60 px-3 py-2 text-[11px] text-indigo-800">
            Save draft to continue later, or submit once your bid is complete.
          </div>
          <div className="flex flex-col gap-2 sm:flex-row">
            <Button
              onClick={() => handleSubmit('draft')}
              variant="outline"
              disabled={isSubmitting || isLoadingExisting || finalBidLocked || !canSubmitBid}
              className="h-9 flex-1 border-slate-200 text-slate-700 hover:bg-slate-50"
            >
              {isSubmitting && submitType === 'draft' ? (
                <div className="flex items-center text-xs">
                  <Spinner className="mr-2 h-3.5 w-3.5" />
                  {isEditingDraft ? 'Updating draft' : 'Saving draft'}
                </div>
              ) : (
                <>
                  <Save className="mr-2 h-4 w-4" />
                  {isEditingDraft ? 'Update Draft' : 'Save Draft'}
                </>
              )}
            </Button>

            <Button
              onClick={() => handleSubmit('final')}
              disabled={isSubmitting || isLoadingExisting || finalBidLocked || !canSubmitBid || !hasAllItemsQuoted || !hasCurrency || !hasValidity || !hasDelivery}
              className="h-9 flex-1 bg-indigo-600 hover:bg-indigo-700"
            >
              {isSubmitting && submitType === 'final' ? (
                <div className="flex items-center text-xs">
                  <Spinner className="mr-2 h-3.5 w-3.5" />
                  Submitting bid
                </div>
              ) : (
                <>
                  <Send className="mr-2 h-4 w-4" />
                  Submit bid
                </>
              )}
            </Button>
          </div>

          <Alert className="border-amber-200 bg-amber-50 py-2 text-amber-800">
            <AlertTriangle className="h-4 w-4" />
            <AlertDescription className="text-xs">
              Final submissions are locked after successful submit.
            </AlertDescription>
          </Alert>
        </CardContent>
      </Card>
    </div>
  );
}
