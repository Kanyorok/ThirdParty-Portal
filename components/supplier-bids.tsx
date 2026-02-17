"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import Link from "next/link";
import {
  ArrowUpRight,
  FileText,
  Inbox,
  Loader2,
  RefreshCw,
  Search,
  X,
} from "lucide-react";

import { Button } from "@/components/common/button";
import { Input } from "@/components/common/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/common/select";
import { Badge } from "@/components/common/badge";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/common/sheet";

type BidStatus = "draft" | "submitted" | "unknown";

interface BidRecord {
  id?: number;
  bid_id?: number;
  tender_id?: number;
  tender_ref?: string;
  tender_no?: string;
  tender_title?: string;
  bid_amount?: number;
  currency?: string;
  validity_period?: number;
  delivery_period?: number;
  payment_terms?: string;
  status?: string;
  bid_status?: string;
  access_status?: string | null;
  submitted_at?: string | null;
  received_at?: string | null;
  documents_count?: number;
  submission_reference?: string;
  envelope_status?: string;
  is_complete?: number;
  remarks?: string | null;
}

function normalizeStatus(bid: BidRecord): BidStatus {
  const status = (bid.bid_status || bid.status || "").toLowerCase();
  if (status === "draft" || status === "submitted") return status;
  return "unknown";
}

function statusPill(status: BidStatus) {
  switch (status) {
    case "submitted":
      return "border-emerald-200 bg-emerald-50 text-emerald-700";
    case "draft":
      return "border-amber-200 bg-amber-50 text-amber-700";
    default:
      return "border-slate-200 bg-slate-100 text-slate-600";
  }
}

function statusLabel(status: BidStatus) {
  switch (status) {
    case "submitted":
      return "Submitted";
    case "draft":
      return "Draft";
    default:
      return "Unknown";
  }
}

function formatDate(value?: string | null) {
  if (!value) return "Not submitted";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Not submitted";
  return date.toLocaleString();
}

function formatAmount(amount?: number, currency?: string) {
  if (typeof amount !== "number" || Number.isNaN(amount)) return "Amount pending";
  return `${currency || "KES"} ${amount.toLocaleString()}`;
}

export default function SupplierBids() {
  const [search, setSearch] = useState("");
  const [status, setStatus] = useState("all");
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [bids, setBids] = useState<BidRecord[]>([]);
  const [selectedBid, setSelectedBid] = useState<BidRecord | null>(null);

  const fetchBids = useCallback(async () => {
    try {
      setIsLoading(true);
      setError(null);

      const response = await fetch("/api/tender-bids", { headers: { Accept: "application/json" } });
      const data = await response.json().catch(() => null);
      if (!response.ok) {
        throw new Error(data?.message || "Failed to load bids");
      }

      const list = Array.isArray(data?.data) ? (data.data as BidRecord[]) : [];
      const sorted = [...list].sort((a, b) => {
        const leftDate = new Date(a.submitted_at || a.received_at || 0).getTime();
        const rightDate = new Date(b.submitted_at || b.received_at || 0).getTime();
        return rightDate - leftDate;
      });
      setBids(sorted);
    } catch (err) {
      setBids([]);
      setError(err instanceof Error ? err.message : "Unable to load bids");
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchBids();
  }, [fetchBids]);

  const filteredBids = useMemo(() => {
    const q = search.trim().toLowerCase();
    return bids.filter((bid) => {
      const normalized = normalizeStatus(bid);
      const matchesStatus = status === "all" || normalized === status;
      if (!matchesStatus) return false;

      if (!q) return true;
      const haystack = [
        bid.tender_ref,
        bid.tender_no,
        bid.tender_title,
        bid.submission_reference,
        bid.currency,
        bid.envelope_status,
      ]
        .filter(Boolean)
        .join(" ")
        .toLowerCase();

      return haystack.includes(q);
    });
  }, [bids, search, status]);

  const summary = useMemo(() => {
    return bids.reduce(
      (acc, bid) => {
        const normalized = normalizeStatus(bid);
        acc.total += 1;
        if (normalized === "draft") acc.draft += 1;
        if (normalized === "submitted") acc.submitted += 1;
        return acc;
      },
      { total: 0, draft: 0, submitted: 0 }
    );
  }, [bids]);

  return (
    <section className="w-full space-y-6">
      <header className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div className="flex gap-3">
          <div className="w-1 rounded-full bg-indigo-600" />
          <div className="space-y-1">
            <h1 className="text-2xl font-semibold tracking-tight text-slate-900">My Bids</h1>
            <p className="text-sm text-slate-600">Track drafts and submitted bids across all tenders.</p>
          </div>
        </div>

        <div className="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
          <div className="relative w-full sm:w-80">
            <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <Input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by tender, ref, or bid reference"
              className="h-10 rounded-md bg-white pl-10 pr-9 text-sm"
            />
            {search && (
              <button
                onClick={() => setSearch("")}
                className="absolute right-2.5 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded hover:bg-slate-100"
              >
                <X className="h-4 w-4 text-slate-400" />
              </button>
            )}
          </div>

          <Select value={status} onValueChange={setStatus}>
            <SelectTrigger className="h-10 w-full rounded-md bg-white text-sm sm:w-36">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All</SelectItem>
              <SelectItem value="draft">Draft</SelectItem>
              <SelectItem value="submitted">Submitted</SelectItem>
            </SelectContent>
          </Select>

          <Button
            variant="outline"
            onClick={fetchBids}
            disabled={isLoading}
            className="h-10 border-slate-200 px-3 text-slate-700 hover:bg-slate-50"
          >
            <RefreshCw className={`h-4 w-4 ${isLoading ? "animate-spin" : ""}`} />
          </Button>
        </div>
      </header>

      <div className="grid gap-3 sm:grid-cols-3">
        <div className="rounded-xl border border-slate-200/70 bg-white px-4 py-3">
          <p className="text-xs font-medium text-slate-500">Total bids</p>
          <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.total}</p>
        </div>
        <div className="rounded-xl border border-slate-200/70 bg-white px-4 py-3">
          <p className="text-xs font-medium text-slate-500">Draft</p>
          <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.draft}</p>
        </div>
        <div className="rounded-xl border border-slate-200/70 bg-white px-4 py-3">
          <p className="text-xs font-medium text-slate-500">Submitted</p>
          <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.submitted}</p>
        </div>
      </div>

      {error && (
        <div className="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {error}
        </div>
      )}

      <div className="grid gap-3">
        {filteredBids.map((bid) => {
          const normalized = normalizeStatus(bid);
          const key = String(bid.id || bid.bid_id || `${bid.tender_id}-${bid.submission_reference}`);
          return (
            <div
              key={key}
              role="button"
              tabIndex={0}
              className="group rounded-xl border border-slate-200/80 bg-white px-4 py-3 transition-colors hover:border-indigo-200 hover:bg-indigo-50/30"
              onClick={() => setSelectedBid(bid)}
              onKeyDown={(event) => {
                if (event.key === "Enter" || event.key === " ") {
                  event.preventDefault();
                  setSelectedBid(bid);
                }
              }}
            >
              <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div className="min-w-0">
                  <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span className="font-mono">{bid.tender_no || bid.tender_ref || "TENDER"}</span>
                    <span className="h-1 w-1 rounded-full bg-slate-300" />
                    <span>{formatDate(bid.submitted_at || bid.received_at)}</span>
                  </div>
                  <p className="mt-1 truncate text-base font-semibold text-slate-900">
                    {bid.tender_title || "Untitled tender"}
                  </p>
                  <p className="mt-1 text-sm text-slate-600">
                    {formatAmount(bid.bid_amount, bid.currency)}
                  </p>
                </div>

                <div className="flex items-center gap-2">
                  <Badge className={`rounded-full border px-2.5 py-1 text-[11px] font-semibold ${statusPill(normalized)}`}>
                    {statusLabel(normalized)}
                  </Badge>
                  <Badge className="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                    {bid.documents_count || 0} docs
                  </Badge>
                  <Button
                    variant="outline"
                    size="sm"
                    className="h-8 border-indigo-200 px-3 text-xs text-indigo-700 hover:bg-indigo-50"
                    onClick={(e) => {
                      e.stopPropagation();
                      setSelectedBid(bid);
                    }}
                  >
                    View
                    <ArrowUpRight className="ml-1 h-3.5 w-3.5" />
                  </Button>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {!isLoading && filteredBids.length === 0 && (
        <div className="rounded-xl border border-slate-200/70 bg-white py-14 text-center">
          <Inbox className="mx-auto mb-2 h-8 w-8 text-slate-300" />
          <p className="text-sm font-semibold text-slate-900">No bids found</p>
          <p className="text-xs text-slate-500">Create a bid from the tenders page to see it here.</p>
        </div>
      )}

      {isLoading && (
        <div className="flex justify-center py-12">
          <Loader2 className="h-6 w-6 animate-spin text-indigo-600" />
        </div>
      )}

      <Sheet open={!!selectedBid} onOpenChange={(open) => !open && setSelectedBid(null)}>
        <SheetContent className="w-full border-l border-slate-200 bg-white p-0 sm:max-w-xl">
          {selectedBid && (
            <div className="flex h-full flex-col">
              <SheetHeader className="border-b border-slate-200/70 px-5 py-4 text-left">
                <SheetTitle className="text-lg font-semibold text-slate-900">
                  {selectedBid.tender_title || "Bid details"}
                </SheetTitle>
                <SheetDescription className="text-xs text-slate-500">
                  {selectedBid.submission_reference || "Submission reference unavailable"}
                </SheetDescription>
              </SheetHeader>

              <div className="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                <div className="grid gap-2 sm:grid-cols-2">
                  <div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Tender</p>
                    <p className="mt-1 text-sm font-semibold text-slate-900">
                      {selectedBid.tender_no || selectedBid.tender_ref || "-"}
                    </p>
                  </div>
                  <div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Status</p>
                    <p className="mt-1 text-sm font-semibold text-slate-900">
                      {statusLabel(normalizeStatus(selectedBid))}
                    </p>
                  </div>
                  <div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Bid amount</p>
                    <p className="mt-1 text-sm font-semibold text-slate-900">
                      {formatAmount(selectedBid.bid_amount, selectedBid.currency)}
                    </p>
                  </div>
                  <div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Documents</p>
                    <p className="mt-1 text-sm font-semibold text-slate-900">{selectedBid.documents_count || 0}</p>
                  </div>
                  <div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Validity period</p>
                    <p className="mt-1 text-sm font-semibold text-slate-900">
                      {selectedBid.validity_period ? `${selectedBid.validity_period} days` : "-"}
                    </p>
                  </div>
                  <div className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Delivery period</p>
                    <p className="mt-1 text-sm font-semibold text-slate-900">
                      {selectedBid.delivery_period ? `${selectedBid.delivery_period} days` : "-"}
                    </p>
                  </div>
                </div>

                {selectedBid.payment_terms && (
                  <div className="rounded-lg border border-slate-200 bg-white px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Payment terms</p>
                    <p className="mt-1 text-sm text-slate-700">{selectedBid.payment_terms}</p>
                  </div>
                )}

                {selectedBid.remarks && (
                  <div className="rounded-lg border border-slate-200 bg-white px-3 py-2">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Remarks</p>
                    <p className="mt-1 text-sm text-slate-700">{selectedBid.remarks}</p>
                  </div>
                )}
              </div>

              <div className="border-t border-slate-200/70 px-5 py-3">
                <Button asChild className="h-9 w-full bg-indigo-600 hover:bg-indigo-700">
                  <Link href="/dashboard/supplier/tenders">
                    Open tenders
                    <FileText className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
              </div>
            </div>
          )}
        </SheetContent>
      </Sheet>
    </section>
  );
}

