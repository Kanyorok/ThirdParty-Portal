"use client"

import React, { useCallback, useEffect, useMemo, useState } from "react"
import {
    Building2, Search, Inbox, Maximize2, Sparkles,
    MapPin, ArrowUpRight, LayoutGrid, ChevronDown, X
} from "lucide-react"
import { usePagination } from "@/components/providers/pagination-provider"
import { Property, PaginatedResponse } from "@/types/property"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"
import { Sheet, SheetContent, SheetTitle, SheetTrigger } from "@/components/common/sheet"
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/common/accordion"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { useSession } from "next-auth/react"
import { toast } from "sonner"
import { cn } from "@/lib/utils"
import { resolveSessionAccessToken } from "@/lib/auth/server-token"
import {
    createLeaseInterest,
    getLeaseInterestById,
    getPaymentFrequencyCodeDetails,
    type CodeDetail,
} from "@/lib/api/lease-interests"

type PropertyStats = {
    totalUnits: number
    vacantUnits: number
    occupiedUnits: number
    totalBlocks: number
    totalFloors: number
    vacancyRate: number
}

function getPropertyStats(property: Property): PropertyStats {
    let totalUnits = 0
    let vacantUnits = 0
    let totalFloors = 0

    const blocks = property.blocks ?? []

    blocks.forEach((block) => {
        totalFloors += block.floors?.length ?? 0
        block.floors?.forEach((floor) => {
            floor.units?.forEach((unit) => {
                totalUnits += 1
                if (String(unit.availabilityLabel || "").toLowerCase() === "vacant") {
                    vacantUnits += 1
                }
            })
        })
    })

    const occupiedUnits = Math.max(totalUnits - vacantUnits, 0)

    return {
        totalUnits,
        vacantUnits,
        occupiedUnits,
        totalBlocks: blocks.length,
        totalFloors,
        vacancyRate: totalUnits > 0 ? Math.round((vacantUnits / totalUnits) * 100) : 0,
    }
}

function getPropertyLead(stats: PropertyStats) {
    if (stats.vacantUnits >= 5) return "High-availability spaces ready for immediate enquiries"
    if (stats.vacantUnits > 0) return "Move-in-ready units with active availability"
    return "Currently fully occupied, but still open for planning interest"
}

function getAvailabilityTone(stats: PropertyStats) {
    if (stats.vacantUnits > 0) {
        return "bg-emerald-500/95 text-white border-none"
    }

    return "bg-slate-900/90 text-white border-none"
}

function resolveImageUrl(input: unknown): string | null {
    if (!input || typeof input !== "object") return null

    const node = input as Record<string, unknown>
    const directKeys = [
        "imageUrl",
        "image_url",
        "image",
        "coverImage",
        "cover_image",
        "banner",
        "bannerUrl",
        "photo",
        "thumbnail",
        "thumbnailUrl",
        "url",
    ]

    for (const key of directKeys) {
        const value = node[key]
        if (typeof value === "string" && value.trim()) return value.trim()
    }

    const collectionKeys = ["media", "gallery", "images", "photos", "attachments"]
    for (const key of collectionKeys) {
        const value = node[key]
        if (!Array.isArray(value)) continue

        for (const item of value) {
            const candidate = resolveImageUrl(item)
            if (candidate) return candidate
        }
    }

    return null
}

function escapeHtml(value: string) {
    return value
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;")
}

function openPrintablePropertySummary({
    property,
    locationName,
    stats,
    imageUrl,
}: {
    property: Property
    locationName: string
    stats: PropertyStats
    imageUrl?: string | null
}) {
    if (typeof window === "undefined") return

    const printWindow = window.open("", "_blank", "noopener,noreferrer,width=1080,height=920")
    if (!printWindow) {
        toast.error("Unable to open printable summary. Please allow pop-ups and try again.")
        return
    }

    const structureMarkup = (property.blocks || [])
        .map((block) => {
            const floors = block.floors || []
            const floorMarkup = floors
                .map((floor) => {
                    const units = floor.units || []
                    const unitMarkup = units
                        .map((unit) => {
                            const status = String(unit.availabilityLabel || "Unknown")
                            return `
                                <tr>
                                    <td>${escapeHtml(unit.unitCode)}</td>
                                    <td>${escapeHtml(floor.floorLabel)}</td>
                                    <td>${escapeHtml(String(unit.unitSize ?? "-"))} sq ft</td>
                                    <td>${escapeHtml(status)}</td>
                                </tr>
                            `
                        })
                        .join("")

                    return `
                        <section class="floor-card">
                            <h4>${escapeHtml(floor.floorLabel)}</h4>
                            <p>${units.length} unit${units.length === 1 ? "" : "s"}</p>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Unit</th>
                                        <th>Floor</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${unitMarkup}
                                </tbody>
                            </table>
                        </section>
                    `
                })
                .join("")

            return `
                <section class="block-section">
                    <div class="section-head">
                        <div>
                            <h3>${escapeHtml(block.blockName)}</h3>
                            <p>${floors.length} floor${floors.length === 1 ? "" : "s"}</p>
                        </div>
                    </div>
                    <div class="floor-grid">
                        ${floorMarkup}
                    </div>
                </section>
            `
        })
        .join("")

    const doc = `
        <!doctype html>
        <html>
            <head>
                <meta charset="utf-8" />
                <title>${escapeHtml(property.propertyName)} Summary</title>
                <style>
                    :root {
                        color-scheme: light;
                        --ink: #0f172a;
                        --muted: #475569;
                        --line: #dbe5f0;
                        --soft: #f8fbff;
                        --brand: #2563eb;
                        --brand-soft: #dbeafe;
                        --success: #047857;
                    }
                    * { box-sizing: border-box; }
                    body {
                        margin: 0;
                        font-family: "Segoe UI", Arial, sans-serif;
                        color: var(--ink);
                        background: white;
                    }
                    .page {
                        padding: 32px;
                    }
                    .hero {
                        border: 1px solid var(--line);
                        border-radius: 24px;
                        overflow: hidden;
                        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 50%, #f8fafc 100%);
                        margin-bottom: 24px;
                    }
                    .hero-grid {
                        display: grid;
                        grid-template-columns: 1.1fr 0.9fr;
                        gap: 24px;
                        padding: 24px;
                        align-items: stretch;
                    }
                    .eyebrow {
                        display: inline-block;
                        border: 1px solid #bfdbfe;
                        background: white;
                        color: var(--brand);
                        border-radius: 999px;
                        padding: 6px 10px;
                        font-size: 11px;
                        font-weight: 700;
                        letter-spacing: .16em;
                        text-transform: uppercase;
                    }
                    h1 { font-size: 30px; line-height: 1.15; margin: 14px 0 10px; }
                    p { margin: 0; }
                    .lead { color: var(--muted); line-height: 1.7; max-width: 54ch; }
                    .meta { margin-top: 18px; color: var(--muted); font-size: 14px; }
                    .media {
                        min-height: 240px;
                        border: 1px solid rgba(219,229,240,.9);
                        border-radius: 20px;
                        overflow: hidden;
                        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
                    }
                    .media img { width: 100%; height: 100%; object-fit: cover; display: block; }
                    .media-fallback {
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: var(--brand);
                        font-size: 15px;
                        font-weight: 600;
                        background: radial-gradient(circle at top left, rgba(37,99,235,.12), transparent 35%), linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
                    }
                    .stats {
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 12px;
                        margin-bottom: 24px;
                    }
                    .stat {
                        border: 1px solid var(--line);
                        border-radius: 18px;
                        padding: 16px;
                        background: var(--soft);
                    }
                    .stat label {
                        display: block;
                        color: var(--muted);
                        font-size: 11px;
                        font-weight: 700;
                        letter-spacing: .14em;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                    }
                    .stat strong { font-size: 26px; }
                    .stat span { display: block; margin-top: 8px; color: var(--muted); font-size: 12px; line-height: 1.5; }
                    .section {
                        border: 1px solid var(--line);
                        border-radius: 24px;
                        padding: 20px;
                        margin-bottom: 20px;
                    }
                    .section-title {
                        font-size: 12px;
                        text-transform: uppercase;
                        letter-spacing: .16em;
                        color: var(--muted);
                        font-weight: 700;
                        margin-bottom: 14px;
                    }
                    .section p { color: var(--muted); line-height: 1.7; }
                    .block-section + .block-section { margin-top: 18px; }
                    .section-head {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 14px;
                    }
                    .section-head h3 { margin: 0 0 6px; font-size: 20px; }
                    .section-head p { font-size: 13px; color: var(--muted); }
                    .floor-grid { display: grid; gap: 14px; }
                    .floor-card {
                        border: 1px solid var(--line);
                        border-radius: 18px;
                        padding: 14px;
                        background: #fff;
                    }
                    .floor-card h4 { margin: 0 0 4px; font-size: 15px; }
                    .floor-card p { font-size: 12px; color: var(--muted); margin-bottom: 12px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { text-align: left; padding: 10px 8px; border-top: 1px solid var(--line); font-size: 12px; }
                    th { color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
                    .footer {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-top: 24px;
                        color: var(--muted);
                        font-size: 12px;
                    }
                    @media print {
                        body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                        .page { padding: 16px; }
                    }
                </style>
            </head>
            <body>
                <main class="page">
                    <section class="hero">
                        <div class="hero-grid">
                            <div>
                                <span class="eyebrow">Property Summary</span>
                                <h1>${escapeHtml(property.propertyName)}</h1>
                                <p class="lead">${escapeHtml(property.propertyDescription || "Commercial property summary generated from the current registry listing.")}</p>
                                <p class="meta">${escapeHtml(locationName)} • ${escapeHtml(property.propertyCode)}</p>
                            </div>
                            <div class="media">
                                ${imageUrl ? `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(property.propertyName)}" />` : `<div class="media-fallback">Property image unavailable in current feed</div>`}
                            </div>
                        </div>
                    </section>

                    <section class="stats">
                        <div class="stat"><label>Vacant now</label><strong>${stats.vacantUnits}</strong><span>Units currently open for interest</span></div>
                        <div class="stat"><label>Total units</label><strong>${stats.totalUnits}</strong><span>Inventory across the full property</span></div>
                        <div class="stat"><label>Blocks</label><strong>${stats.totalBlocks}</strong><span>Distinct structural sections</span></div>
                        <div class="stat"><label>Vacancy rate</label><strong>${stats.vacancyRate}%</strong><span>Share of current available stock</span></div>
                    </section>

                    <section class="section">
                        <div class="section-title">Overview</div>
                        <p>${escapeHtml(getPropertyLead(stats))}</p>
                    </section>

                    <section class="section">
                        <div class="section-title">Property Structure</div>
                        ${structureMarkup}
                    </section>

                    <div class="footer">
                        <span>Generated from ThirdParty Portal on ${escapeHtml(new Date().toLocaleString())}</span>
                        <span>Printable property summary</span>
                    </div>
                </main>
            </body>
        </html>
    `

    printWindow.document.open()
    printWindow.document.write(doc)
    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
}

function PropertyVisual({
    title,
    imageUrl,
    className,
    children,
}: {
    title: string
    imageUrl?: string | null
    className?: string
    children?: React.ReactNode
}) {
    const [hasError, setHasError] = useState(false)
    const showImage = Boolean(imageUrl && !hasError)

    return (
        <div className={cn("relative overflow-hidden", className)}>
            {showImage ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img
                    src={String(imageUrl)}
                    alt={title}
                    className="h-full w-full object-cover"
                    onError={() => setHasError(true)}
                />
            ) : (
                <>
                    <div className="absolute inset-0 bg-[linear-gradient(135deg,_#eff6ff_0%,_#f8fafc_45%,_#ffffff_100%)]" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_35%)]" />
                    <div className="absolute inset-0 flex items-center justify-center">
                        <Building2 className="h-20 w-20 text-slate-200 transition-all duration-500 group-hover/card:scale-110 group-hover/card:text-blue-200" strokeWidth={1} />
                    </div>
                </>
            )}
            {children}
        </div>
    )
}

export function RentablePropertiesList({
    initialData,
    searchQuery,
    setSearchQuery,
    localities = []
}: {
    initialData?: PaginatedResponse<Property>;
    searchQuery: string;
    setSearchQuery: (q: string) => void;
    localities?: any[];
}) {
    const { isPending } = usePagination()
    const [statusFilter, setStatusFilter] = useState<string | null>(null)

    const properties = useMemo(() => initialData?.data ?? [], [initialData])

    const resolveLocation = useCallback((locId: number | undefined) => {
        if (!locId) return "Nairobi, KE"
        const found = localities.find(l => l.id === locId)
        return found ? found.name : "Nairobi, KE"
    }, [localities])

    const filteredProperties = useMemo(() => {
        const query = searchQuery.trim().toLowerCase()
        return properties.filter(p => {
            const locName = resolveLocation(p.locationId).toLowerCase()
            const matchesSearch =
                p.propertyName.toLowerCase().includes(query) ||
                p.propertyCode.toLowerCase().includes(query) ||
                locName.includes(query)

            if (!statusFilter) return matchesSearch

            return matchesSearch && p.blocks?.some(b =>
                b.floors?.some(f =>
                    f.units?.some(u => u.availabilityLabel === statusFilter)
                )
            )
        })
    }, [properties, searchQuery, statusFilter, resolveLocation])

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-6">
                <div className="space-y-2.5">
                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                        <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                        <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Property Registry</span>
                    </div>
                    <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
                        Available Properties for Rent
                    </h1>
                    <p className="text-sm text-slate-600">Browse to rent/lease properties</p>
                </div>

                <div className="flex flex-col lg:flex-row gap-4">
                    <div className="relative flex-1 group">
                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" strokeWidth={2} />
                        <input
                            type="text"
                            placeholder="Search properties by name, code, or location..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full pl-11 pr-10 h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 outline-none transition-all text-sm placeholder:text-slate-400"
                        />
                        {searchQuery && (
                            <button
                                onClick={() => setSearchQuery("")}
                                type="button"
                                className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors"
                            >
                                <X className="h-4 w-4 text-slate-400" strokeWidth={2} />
                            </button>
                        )}
                    </div>

                    <div className="flex items-center gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200 lg:min-w-[280px]">
                        {["All", "Vacant", "Occupied"].map((label) => (
                            <button
                                key={label}
                                onClick={() => setStatusFilter(label === "All" ? null : label)}
                                className={cn(
                                    "flex-1 px-4 py-2 rounded-lg text-xs font-medium transition-all whitespace-nowrap",
                                    (statusFilter === label || (label === "All" && !statusFilter))
                                        ? "bg-white text-slate-900"
                                        : "text-slate-600 hover:text-slate-900 hover:bg-white/50"
                                )}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                </div>
            </header>

            {isPending && properties.length === 0 ? (
                <div className="h-[50vh] flex flex-col items-center justify-center gap-4">
                    <div className="relative">
                        <div className="h-12 w-12 border-4 border-blue-100 border-t-blue-500 rounded-full animate-spin" />
                    </div>
                    <p className="text-sm font-medium text-slate-600">Loading properties...</p>
                </div>
            ) : filteredProperties.length === 0 ? (
                <EmptyState isSearch={!!searchQuery || !!statusFilter} onClear={() => { setSearchQuery(""); setStatusFilter(null) }} />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
                    {filteredProperties.map((property) => (
                        <PropertyDetailsSheet
                            key={property.id}
                            property={property}
                            locationName={resolveLocation(property.locationId)}
                        >
                            <div className="cursor-pointer">
                                <PropertyCard
                                    property={property}
                                    locationName={resolveLocation(property.locationId)}
                                />
                            </div>
                        </PropertyDetailsSheet>
                    ))}
                </div>
            )}
        </div>
    )
}

type UnitChoice = {
    blockId: number
    blockName: string
    floorId: number
    floorLabel: string
    unitId: number
    unitCode: string
    availabilityLabel: string
}

function PropertyCard({ property, locationName }: { property: Property, locationName: string }) {
    const stats = useMemo(() => {
        let totalUnits = 0, vacantUnits = 0;
        property.blocks?.forEach(b => b.floors?.forEach(f => f.units?.forEach(u => {
            totalUnits++;
            if (u.availabilityLabel === "Vacant") vacantUnits++;
        })));
        return { totalUnits, vacantUnits };
    }, [property]);

    return (
        <div className="group/card relative bg-white rounded-2xl border border-slate-200 overflow-hidden transition-all duration-300 hover:border-blue-300">
            <div className="relative aspect-[16/9] bg-gradient-to-br from-blue-50 to-slate-50 overflow-hidden">
                <div className="absolute inset-0 flex items-center justify-center">
                    <Building2 className="h-20 w-20 text-slate-200 group-hover/card:scale-110 group-hover/card:text-blue-200 transition-all duration-500" strokeWidth={1} />
                </div>

                <div className="absolute inset-0 bg-gradient-to-t from-black/5 to-transparent opacity-0 group-hover/card:opacity-100 transition-opacity duration-300" />

                <div className="absolute top-4 left-4 flex gap-2">
                    <Badge className="bg-white/95 backdrop-blur-sm text-slate-900 border border-slate-200 text-[10px] font-semibold px-3 py-1 rounded-lg">
                        {property.propertyCode}
                    </Badge>
                    {stats.vacantUnits > 0 && (
                        <Badge className="bg-emerald-500/95 backdrop-blur-sm text-white border-none text-[10px] font-semibold px-3 py-1 rounded-lg">
                            {stats.vacantUnits} Units Available
                        </Badge>
                    )}
                </div>
            </div>

            <div className="p-5 space-y-4">
                <div className="space-y-2">
                    <h3 className="text-lg font-semibold text-slate-900 group-hover/card:text-blue-600 transition-colors line-clamp-1">
                        {property.propertyName}
                    </h3>
                    <div className="flex items-center gap-1.5 text-slate-600">
                        <MapPin className="h-3.5 w-3.5 text-blue-500" strokeWidth={2} />
                        <span className="text-xs font-medium">{locationName}</span>
                    </div>
                </div>

                <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div className="flex items-center gap-6">
                        <div>
                            <p className="text-[10px] font-medium text-slate-500 uppercase tracking-wide mb-0.5">Units</p>
                            <p className="text-base font-semibold text-slate-900">{stats.totalUnits}</p>
                        </div>
                        <div className="h-8 w-px bg-slate-200" />
                        <div>
                            <p className="text-[10px] font-medium text-slate-500 uppercase tracking-wide mb-0.5">Type</p>
                            <p className="text-base font-semibold text-slate-900">Commercial</p>
                        </div>
                    </div>
                    <div className="h-9 w-9 rounded-xl bg-blue-50 flex items-center justify-center group-hover/card:bg-blue-500 group-hover/card:text-white transition-all">
                        <ArrowUpRight className="h-4 w-4" strokeWidth={2} />
                    </div>
                </div>
            </div>
        </div>
    )
}

function PropertyDetailsSheet({ property, locationName, children }: { property: Property, locationName: string, children: React.ReactNode }) {
    const { data: session } = useSession()
    const accessToken = resolveSessionAccessToken(session as any) || null

    const totalVacant = property.blocks?.reduce((acc, b) =>
        acc + b.floors.reduce((fAcc, f) =>
            fAcc + f.units.filter(u => u.availabilityLabel === "Vacant").length, 0
        ), 0
    );

    const unitChoices = useMemo<UnitChoice[]>(
        () =>
            (property.blocks || []).flatMap((block) =>
                (block.floors || []).flatMap((floor) =>
                    (floor.units || []).map((unit) => ({
                        blockId: block.id,
                        blockName: block.blockName,
                        floorId: floor.id,
                        floorLabel: floor.floorLabel,
                        unitId: unit.id,
                        unitCode: unit.unitCode,
                        availabilityLabel: unit.availabilityLabel,
                    }))
                )
            ),
        [property.blocks]
    )

    const selectableUnits = useMemo(() => {
        const vacant = unitChoices.filter((unit) =>
            String(unit.availabilityLabel || "").toLowerCase().includes("vacant")
        )
        return vacant.length > 0 ? vacant : unitChoices
    }, [unitChoices])

    const [isInterestDialogOpen, setIsInterestDialogOpen] = useState(false)
    const [selectedUnitId, setSelectedUnitId] = useState("")
    const [startDate, setStartDate] = useState("")
    const [endDate, setEndDate] = useState("")
    const [additionalInformation, setAdditionalInformation] = useState("")
    const [paymentFrequencyOptions, setPaymentFrequencyOptions] = useState<CodeDetail[]>([])
    const [paymentFrequencyId, setPaymentFrequencyId] = useState("")
    const [isLoadingPaymentFrequency, setIsLoadingPaymentFrequency] = useState(false)
    const [isSubmittingInterest, setIsSubmittingInterest] = useState(false)

    const selectedUnit = useMemo(
        () => selectableUnits.find((unit) => String(unit.unitId) === selectedUnitId) || null,
        [selectableUnits, selectedUnitId]
    )

    const resetInterestForm = useCallback(() => {
        setSelectedUnitId(selectableUnits[0] ? String(selectableUnits[0].unitId) : "")
        setStartDate("")
        setEndDate("")
        setAdditionalInformation("")
        setPaymentFrequencyId((current) =>
            current || (paymentFrequencyOptions[0] ? String(paymentFrequencyOptions[0].id) : "")
        )
    }, [paymentFrequencyOptions, selectableUnits])

    useEffect(() => {
        if (!isInterestDialogOpen) return
        if (!selectedUnitId && selectableUnits[0]) {
            setSelectedUnitId(String(selectableUnits[0].unitId))
        }
    }, [isInterestDialogOpen, selectableUnits, selectedUnitId])

    useEffect(() => {
        if (!isInterestDialogOpen) return
        if (!accessToken) return
        if (paymentFrequencyOptions.length > 0) return

        let active = true
        setIsLoadingPaymentFrequency(true)

        getPaymentFrequencyCodeDetails(accessToken)
            .then((options) => {
                if (!active) return
                setPaymentFrequencyOptions(options)
                if (!paymentFrequencyId && options[0]) {
                    setPaymentFrequencyId(String(options[0].id))
                }
            })
            .catch((error) => {
                if (!active) return
                toast.error(error instanceof Error ? error.message : "Failed to load payment frequency options")
            })
            .finally(() => {
                if (!active) return
                setIsLoadingPaymentFrequency(false)
            })

        return () => {
            active = false
        }
    }, [accessToken, isInterestDialogOpen, paymentFrequencyId, paymentFrequencyOptions.length])

    const openInterestDialog = () => {
        if (!accessToken) {
            toast.error("Session expired. Sign in again to continue.")
            return
        }
        setIsInterestDialogOpen(true)
    }

    const submitInterest = async (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault()

        if (!accessToken) {
            toast.error("Session expired. Sign in again to continue.")
            return
        }
        if (!selectedUnit) {
            toast.error("Select a unit to continue.")
            return
        }
        if (!startDate || !endDate) {
            toast.error("Start and end dates are required.")
            return
        }
        if (new Date(startDate).getTime() > new Date(endDate).getTime()) {
            toast.error("Interested end date must be after start date.")
            return
        }
        if (!paymentFrequencyId) {
            toast.error("Select payment frequency.")
            return
        }

        setIsSubmittingInterest(true)
        try {
            const payload = {
                property_id: property.id,
                block_id: selectedUnit.blockId,
                floor_id: selectedUnit.floorId,
                unit_id: selectedUnit.unitId,
                interested_start_date: startDate,
                interested_end_date: endDate,
                payment_frequency: Number(paymentFrequencyId),
                additional_information: additionalInformation.trim() || undefined,
            }

            const created = await createLeaseInterest(payload, accessToken)
            const createdId = Number((created as any)?.data?.id ?? (created as any)?.data?.Id ?? NaN)
            let verificationFailed = false

            try {
                if (Number.isFinite(createdId) && createdId > 0) {
                    await getLeaseInterestById(createdId, accessToken)
                }
            } catch {
                verificationFailed = true
            }

            if (verificationFailed) {
                toast.success("Lease interest submitted. Verification endpoint did not respond.")
            } else {
                toast.success((created as any)?.message || "Property interest created successfully")
            }
            setIsInterestDialogOpen(false)
            resetInterestForm()
        } catch (error) {
            toast.error(error instanceof Error ? error.message : "Failed to submit lease interest.")
        } finally {
            setIsSubmittingInterest(false)
        }
    }

    return (
        <Sheet>
            <SheetTrigger asChild>{children}</SheetTrigger>
            <SheetContent className="w-full sm:max-w-[540px] md:max-w-2xl bg-white p-0 border-l border-slate-200 overflow-hidden">
                <div className="h-full flex flex-col">
                    <header className="p-8 pb-10 bg-gradient-to-br from-blue-50/40 to-white border-b border-slate-200 relative shrink-0">
                        <div className="relative z-10 space-y-4">
                            <Badge className="bg-blue-500 text-white border-none px-3 py-1.5 rounded-lg text-[10px] font-semibold tracking-wide uppercase">
                                {property.propertyCode}
                            </Badge>
                            <SheetTitle className="text-3xl font-semibold text-slate-900 tracking-tight leading-tight pr-12">
                                {property.propertyName}
                            </SheetTitle>
                            <div className="flex items-center gap-2 text-sm font-medium text-slate-600">
                                <MapPin className="h-4 w-4 text-blue-500" strokeWidth={2} />
                                {locationName}
                            </div>
                        </div>
                        <div className="absolute -right-10 -bottom-10 text-blue-50 select-none pointer-events-none">
                            <Building2 className="h-64 w-64" strokeWidth={0.5} />
                        </div>
                    </header>

                    <div className="flex-1 overflow-y-auto px-8 py-8 space-y-8 pb-32">
                        <div className="space-y-6">
                            <div className="space-y-2">
                                <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overview</h4>
                                <p className="text-base leading-relaxed text-slate-700">
                                    {property.propertyDescription || "Premium commercial property located in a prime business district with excellent accessibility and modern amenities."}
                                </p>
                            </div>

                            <div className="flex items-center gap-6 p-6 bg-blue-50 rounded-xl border border-blue-200">
                                <div className="flex items-baseline gap-2">
                                    <div className="text-4xl font-semibold text-slate-900">{totalVacant}</div>
                                    <div className="text-sm text-slate-600">of {property.blocks?.reduce((acc, b) =>
                                        acc + b.floors.reduce((fAcc, f) => fAcc + f.units.length, 0), 0
                                    )}</div>
                                </div>
                                <div className="h-12 w-px bg-blue-200" />
                                <div className="text-xs font-medium text-slate-600 leading-relaxed">
                                    Units currently<br />available to lease
                                </div>
                            </div>
                        </div>

                        <div className="space-y-6">
                            <h4 className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Property Structure</h4>

                            {property.blocks?.map((block) => (
                                <section key={block.id} className="space-y-4">
                                    <div className="flex items-center gap-3 pb-2 border-b border-slate-200">
                                        <div className="h-9 w-9 rounded-xl bg-blue-500 text-white flex items-center justify-center">
                                            <LayoutGrid className="h-4 w-4" strokeWidth={2} />
                                        </div>
                                        <h4 className="text-lg font-semibold text-slate-900">{block.blockName}</h4>
                                    </div>

                                    <Accordion type="multiple" className="space-y-2">
                                        {block.floors?.map((floor) => (
                                            <AccordionItem key={floor.id} value={`floor-${floor.id}`} className="border-none">
                                                <AccordionTrigger className="hover:no-underline py-0 [&[data-state=open]>div]:border-blue-300 [&[data-state=open]>div]:bg-blue-50/30">
                                                    <div className="flex items-center justify-between w-full p-4 rounded-xl bg-white border border-slate-200 text-left transition-all">
                                                        <div className="flex items-center gap-4">
                                                            <div className="h-10 w-10 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center">
                                                                <span className="text-base font-semibold text-blue-600">
                                                                    {floor.floorLabel.replace(/\D/g, '')?.padStart(2, '0') || "01"}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <p className="text-sm font-semibold text-slate-900">{floor.floorLabel}</p>
                                                                <p className="text-xs text-slate-600 font-medium mt-0.5">{floor.units.length} Units</p>
                                                            </div>
                                                        </div>
                                                        <ChevronDown className="h-4 w-4 text-slate-400 transition-transform duration-200" strokeWidth={2} />
                                                    </div>
                                                </AccordionTrigger>
                                                <AccordionContent className="pt-2 px-1">
                                                    <div className="space-y-2">
                                                        {floor.units?.map((unit) => (
                                                            <div
                                                                key={unit.id}
                                                                className="flex items-center justify-between p-4 rounded-xl bg-slate-50/50 border border-slate-200 hover:border-blue-300 hover:bg-blue-50/20 transition-colors"
                                                            >
                                                                <div className="space-y-1">
                                                                    <p className="text-sm font-semibold text-slate-900">{unit.unitCode}</p>
                                                                    <div className="flex items-center gap-2 text-xs text-slate-600 font-medium">
                                                                        <Maximize2 className="h-3.5 w-3.5 text-blue-500" strokeWidth={2} />
                                                                        {unit.unitSize} sq ft
                                                                    </div>
                                                                </div>
                                                                <Badge className={cn(
                                                                    "text-[10px] font-semibold uppercase px-2.5 py-1 rounded-lg border",
                                                                    unit.availabilityLabel === "Vacant"
                                                                        ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                                                                        : "bg-slate-100 text-slate-600 border-slate-200"
                                                                )}>
                                                                    {unit.availabilityLabel}
                                                                </Badge>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </AccordionContent>
                                            </AccordionItem>
                                        ))}
                                    </Accordion>
                                </section>
                            ))}
                        </div>
                    </div>

                    <footer className="p-6 border-t border-slate-200 bg-white flex gap-3 shrink-0 absolute bottom-0 w-full z-50">
                        <Button
                            variant="outline"
                            className="flex-1 h-11 rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-none focus-visible:ring-4 focus-visible:ring-blue-50 focus-visible:border-blue-300"
                        >
                            Download Details
                        </Button>
                        <Button
                            disabled={totalVacant === 0}
                            onClick={openInterestDialog}
                            className={cn(
                                "flex-[2] h-11 rounded-xl text-xs font-medium transition-colors shadow-none focus-visible:ring-4 focus-visible:ring-blue-50",
                                totalVacant === 0
                                    ? "bg-slate-200 text-slate-500 cursor-not-allowed hover:bg-slate-200"
                                    : "bg-blue-500 hover:bg-blue-600 text-white",
                            )}
                        >
                            {totalVacant === 0 ? "No Units Available" : `Show Interest (${totalVacant} Available)`}
                        </Button>
                    </footer>
                </div>
            </SheetContent>

            <Dialog
                open={isInterestDialogOpen}
                onOpenChange={(nextOpen) => {
                    setIsInterestDialogOpen(nextOpen)
                    if (!nextOpen) resetInterestForm()
                }}
            >
                <DialogContent className="sm:max-w-[640px] border border-slate-200 bg-white shadow-none">
                    <DialogHeader>
                        <DialogTitle className="text-lg font-semibold tracking-tight text-slate-900">
                            Show Interest In This Property
                        </DialogTitle>
                    </DialogHeader>

                    <form className="space-y-4" onSubmit={submitInterest}>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-slate-700">Unit</label>
                                <select
                                    value={selectedUnitId}
                                    onChange={(e) => setSelectedUnitId(e.target.value)}
                                    className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300"
                                    required
                                >
                                    <option value="" disabled>
                                        Select unit
                                    </option>
                                    {selectableUnits.map((unit) => (
                                        <option key={unit.unitId} value={unit.unitId}>
                                            {unit.blockName} · {unit.floorLabel} · {unit.unitCode}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-slate-700">Payment Frequency</label>
                                <select
                                    value={paymentFrequencyId}
                                    onChange={(e) => setPaymentFrequencyId(e.target.value)}
                                    className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300 disabled:bg-slate-100"
                                    disabled={isLoadingPaymentFrequency}
                                    required
                                >
                                    <option value="" disabled>
                                        {isLoadingPaymentFrequency ? "Loading frequencies..." : "Select payment frequency"}
                                    </option>
                                    {paymentFrequencyOptions.map((option) => (
                                        <option key={option.id} value={option.id}>
                                            {option.label || option.name || option.code || `Option ${option.id}`}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-slate-700">Interested Start Date</label>
                                <input
                                    type="date"
                                    value={startDate}
                                    onChange={(e) => setStartDate(e.target.value)}
                                    className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300"
                                    required
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-slate-700">Interested End Date</label>
                                <input
                                    type="date"
                                    value={endDate}
                                    onChange={(e) => setEndDate(e.target.value)}
                                    className="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300"
                                    required
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-semibold text-slate-700">Additional Information</label>
                            <textarea
                                value={additionalInformation}
                                onChange={(e) => setAdditionalInformation(e.target.value)}
                                placeholder="I want this unit for 12 months"
                                className="min-h-[88px] w-full rounded-lg border border-slate-200 bg-white p-3 text-sm outline-none focus:border-blue-300"
                            />
                        </div>

                        <div className="flex items-center justify-end gap-2 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                className="h-10 rounded-lg border-slate-200 bg-white text-xs font-semibold"
                                onClick={() => setIsInterestDialogOpen(false)}
                                disabled={isSubmittingInterest}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                className="h-10 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700"
                                disabled={isSubmittingInterest || !accessToken}
                            >
                                {isSubmittingInterest ? "Submitting..." : "Submit Interest"}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </Sheet>
    )
}

function EmptyState({ isSearch, onClear }: { isSearch: boolean, onClear: () => void }) {
    return (
        <div className="h-[50vh] flex flex-col items-center justify-center text-center px-6">
            <div className="relative mb-6">
                <div className="h-20 w-20 rounded-2xl bg-white border border-slate-200 flex items-center justify-center">
                    <Inbox className="h-9 w-9 text-slate-300" strokeWidth={1.5} />
                </div>
            </div>
            <h3 className="text-lg font-semibold text-slate-900 mb-2">
                {isSearch ? "No Properties Found" : "No Properties Available"}
            </h3>
            <p className="text-sm text-slate-600 max-w-sm mb-6 leading-relaxed">
                {isSearch
                    ? "Try adjusting your search criteria or filters to find what you're looking for."
                    : "Properties will appear here once they're added to your portfolio."
                }
            </p>
            {isSearch && (
                <Button
                    onClick={onClear}
                    variant="outline"
                    className="rounded-xl px-6 h-10 text-xs font-medium border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors shadow-none focus-visible:ring-4 focus-visible:ring-blue-50 focus-visible:border-blue-300"
                >
                    Clear Filters
                </Button>
            )}
        </div>
    )
}
