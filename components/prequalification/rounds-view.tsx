"use client"

import { useState } from "react"
import { useSearchParams } from "next/navigation"
import { RoundsTable } from "./rounds-table"
import { RoundsToolbar } from "./rounds-toolbar"
import { ApiCategory, RazzModule, Round } from "@/types/prequalification"
import { axiosInstance } from "@/lib/axios"

async function getRounds(query: Record<string, string | undefined>) {
    const page = Number(query.page || 1);
    const pageSize = Number(query.pageSize || 10);
    const sortBy = query.sortBy || "startDate"; // Default sortBy
    const sortOrder = query.sortOrder || "desc"; // Default sortOrder
    const statusValue = query.status === "all" ? "" : (query.status === "open" ? "O" : "CL");
    const q = query.q || "";

    try {
        // Fetch User ID
        const sessionRes = await axiosInstance.get('/auth/session');
        const userId = sessionRes.data.user.id || sessionRes.data.user.UserID;

        if (!userId) {
            throw new Error("Unable to identify current user");
        }

        // Fetch Rounds
        const authHeader = `Basic ${Buffer.from(`${process.env.NEXT_PUBLIC_API_USERNAME}:${process.env.NEXT_PUBLIC_API_PASSWORD}`).toString('base64')}`;
        const response = await axiosInstance.get(`/supplier/${userId}/prequalification/rounds`, {
            headers: {
                'Authorization': authHeader
            }
        });

        // Ensure we handle the wrapped response format from Laravel
        // The API returns { success: true, message: "...", data: [...] }
        const responseData = response.data;
        let rounds: RazzModule[] = [];

        if (Array.isArray(responseData)) {
            // Direct array
            rounds = responseData;
        } else if (responseData && Array.isArray(responseData.data)) {
            // Wrapped in data property
            rounds = responseData.data;
        } else if (responseData && typeof responseData === 'object') {
            // Might be keyed by ID or some other structure, but usually it's in data
            // If it's the specific "data" wrapper from Laravel Resources:
            rounds = responseData.data || [];
        }

        // Apply filters
        if (q) {
            const lowerQ = q.toLowerCase();
            rounds = rounds.filter((r: any) =>
                (r.title || "").toLowerCase().includes(lowerQ) ||
                (r.roundID || "").toString().toLowerCase().includes(lowerQ)
            );
        }

        if (statusValue) {
            rounds = rounds.filter((round: any) => {
                const roundStatus = typeof round.status === "object" ? round.status.value : round.status;
                return roundStatus === statusValue;
            });
        }

        // Apply sorting
        rounds.sort((a: any, b: any) => {
            let aValue, bValue;
            
            if (sortBy === "title") {
                aValue = a.title || "";
                bValue = b.title || "";
            } else if (sortBy === "startDate") {
                aValue = new Date(a.startDate || 0).getTime();
                bValue = new Date(b.startDate || 0).getTime();
            } else if (sortBy === "endDate") {
                aValue = new Date(a.endDate || 0).getTime();
                bValue = new Date(b.endDate || 0).getTime();
            } else {
                aValue = a.startDate || "";
                bValue = b.startDate || "";
            }

            if (aValue < bValue) return sortOrder === "asc" ? -1 : 1;
            if (aValue > bValue) return sortOrder === "asc" ? 1 : -1;
            return 0;
        });

        // Apply pagination
        const total = rounds.length;
        const totalPages = Math.ceil(total / pageSize);
        const startIndex = (page - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        const paginatedRounds = rounds.slice(startIndex, endIndex);

    // Return data in expected format
        return {
            data: paginatedRounds,
            page,
            pageSize,
            total,
            totalPages,
            sortBy,
            sortOrder,
            filters: { status, q }
        };
    } catch (error) {
        console.error(`Error fetching rounds from Laravel backend:`, error);
        return {
            data: [],
            page: 1,
            pageSize: 10,
            total: 0,
            totalPages: 1,
            sortBy: "startDate",
            sortOrder: "asc",
            filters: {}
        };
    }
}

export default async function RoundsView({
    initialQuery = {},
}: {
    initialQuery?: Record<string, string | undefined>;
}) {
    const apiData = await getRounds(initialQuery);

    // Build rounds with guaranteed unique, non-empty IDs (backend may return null IDs)
    const usedIds = new Set<string>();
    const mappedRounds: Round[] = apiData.data.map((r, idx) => {
        let baseId = (r.roundID ?? r.id ?? r.roundId ?? "").toString().trim();
        if (!baseId) {
            const basis = (r.title ?? r.name ?? "round")
                .toString()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/^-+|-+$/g, "") || "round";
            baseId = `round-${basis}-${idx + 1}`;
        }
        let uniqueId = baseId;
        let counter = 2;
        while (usedIds.has(uniqueId)) {
            uniqueId = `${baseId}-${counter++}`;
        }
        usedIds.add(uniqueId);

        const title = String(r.title ?? r.name ?? uniqueId);
        const startDate = r.startDate ?? "";
        const endDate = r.endDate ?? "";
        const maxVendors = Number(r.maxVendors ?? 0);

        const rawStatus: any = (r as any).status
        let status: any
        if (rawStatus && typeof rawStatus === 'object') {
            status = { value: rawStatus.value, label: rawStatus.label }
        } else {
            const v = (rawStatus ?? r.status)
            status = v === 'O' || v === 'CL' ? v : (v === 'Open' ? 'O' : 'CL')
        }
    // Map categories with their application status
        const rawCats = (r as unknown as { categories?: ApiCategory[] }).categories || [];
        const categories = rawCats.map((cat) => ({
            category_id: Number(cat.category_id ?? cat.categoryId ?? cat.SupplierCategoryID ?? cat.id),
            category_name: String(cat.category_name ?? cat.CategoryName ?? cat.name ?? ''),
            category_description: cat.description,
            has_applied: Boolean(cat.has_applied ?? cat.hasApplied ?? cat.application_id ?? cat.applicationId),
            application_id: (cat.application_id ?? cat.applicationId) ? String(cat.application_id ?? cat.applicationId) : undefined,
            application_date: cat.application_date ?? cat.applicationDate,
            status: (cat as any).status || ((cat.has_applied || cat.hasApplied) ? 'SUBMITTED' : 'NOT_APPLIED'),
            progress_percent: Number(cat.progress_percent ?? cat.progressPercent ?? 0),
            stage: cat.stage,
            stage_label: cat.stage_label ?? cat.stageLabel,
            updated_on: cat.updated_on ?? cat.updatedOn,
            decision_date: cat.decision_date ?? cat.decisionDate,
            rejection_reason: cat.rejection_reason ?? cat.rejectionReason,
        }));

    // Calculate summary from categories
        const appliedCategories = categories.filter((cat: any) => cat.has_applied);
        const approvedCategories = categories.filter((cat: any) => cat.status === 'APPROVED');
        const rejectedCategories = categories.filter((cat: any) => cat.status === 'REJECTED');
        const pendingCategories = categories.filter((cat: any) => 
            ['SUBMITTED', 'UNDER_REVIEW'].includes(cat.status)
        );

        return {
            id: uniqueId,
            title,
            status,
            startDate,
            endDate,
            maxVendors,
            supplierEligible: (r as { supplierEligible?: boolean }).supplierEligible,
            canApply: (r as { canApply?: boolean }).canApply,
            isClosed: (r as { isClosed?: boolean }).isClosed,
            isExpired: (r as { isExpired?: boolean }).isExpired,
            windowOpen: (r as { windowOpen?: boolean }).windowOpen,
            isFutureWindow: (r as { isFutureWindow?: boolean }).isFutureWindow,
            duplicateWithinRange: (r as { duplicateWithinRange?: boolean }).duplicateWithinRange,
            primaryWindowRoundId: (r as { primaryWindowRoundId?: number }).primaryWindowRoundId,
            primaryWindowRoundTitle: (r as { primaryWindowRoundTitle?: string }).primaryWindowRoundTitle,
            categories,
            hasApplied: appliedCategories.length > 0,
            applicationSummary: categories.length > 0 ? {
                total_categories: categories.length,
                applied_categories: appliedCategories.length,
                approved_categories: approvedCategories.length,
                rejected_categories: rejectedCategories.length,
                pending_categories: pendingCategories.length,
                overall_progress: categories.length > 0 ? 
                    Math.round(categories.reduce((sum: number, cat) => sum + (Number(cat.progress_percent) || 0), 0) / categories.length) : 0
            } : undefined,
        };
    });

    return (
        <section className="rounded-xl border">
            <div className="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="space-y-1">
                    <h2 className="text-lg font-medium">Rounds</h2>
                    <p className="text-sm text-muted-foreground">
                        {apiData.total} {apiData.total === 1 ? "round" : "rounds"} found
                    </p>
                </div>
                <RoundsToolbar
                    defaultQuery={{
                        q: initialQuery.q ?? "",
                        status: (initialQuery.status as "all" | "open" | "closed" | undefined) ?? "all",
                        sortBy: (initialQuery.sortBy as string | undefined) ?? apiData.sortBy,
                        sortOrder: (initialQuery.sortOrder as "asc" | "desc" | undefined) ?? apiData.sortOrder,
                        pageSize: Number(initialQuery.pageSize ?? apiData.pageSize),
                    }}
                />
            </div>
            <RoundsTable
                rounds={mappedRounds}
                total={apiData.total}
                page={apiData.page}
                pageSize={apiData.pageSize}
                totalPages={apiData.totalPages}
                sortBy={apiData.sortBy}
                sortOrder={apiData.sortOrder}
            />
        </section>
    );
}