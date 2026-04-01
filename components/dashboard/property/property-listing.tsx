"use client"

import React, { useCallback, useEffect, useMemo, useState } from "react"
import {
    Building2, Search, Inbox, Maximize2, Sparkles,
    MapPin, ArrowUpRight, LayoutGrid, ChevronDown, X,
    Blocks, DoorOpen, ShieldCheck, Clock3
} from "lucide-react"
import { usePagination } from "@/components/providers/pagination-provider"
import { Property, PaginatedResponse, PropertyLocality } from "@/types/property"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"
import { Spinner } from "@/components/common/spinner"
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
    localities?: PropertyLocality[];
}) {
    const { isPending } = usePagination()
    const [statusFilter, setStatusFilter] = useState<string | null>(null)

    const properties = useMemo(() => initialData?.data ?? [], [initialData])

    const resolveLocation = useCallback((property: Property) => {
        const directName = property.locationName || property.localityName
        if (typeof directName === "string" && directName.trim()) return directName.trim()

        if (!property.locationId) return "Location pending confirmation"
        const found = localities.find((locality) => locality.id === property.locationId)
        return found ? found.name : `Locality #${property.locationId}`
    }, [localities])

    const filteredProperties = useMemo(() => {
        const query = searchQuery.trim().toLowerCase()
        return properties.filter(p => {
            const locName = resolveLocation(p).toLowerCase()
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

    const propertyViewModels = useMemo(() => {
        return filteredProperties
            .map((property) => ({
                property,
                locationName: resolveLocation(property),
                stats: getPropertyStats(property),
                imageUrl: resolveImageUrl(property),
            }))
            .sort((left, right) => {
                if (right.stats.vacantUnits !== left.stats.vacantUnits) {
                    return right.stats.vacantUnits - left.stats.vacantUnits
                }

                return right.stats.totalUnits - left.stats.totalUnits
            })
    }, [filteredProperties, resolveLocation])

    const portfolioStats = useMemo(() => {
        return propertyViewModels.reduce(
            (acc, item) => {
                acc.properties += 1
                acc.units += item.stats.totalUnits
                acc.vacant += item.stats.vacantUnits
                acc.blocks += item.stats.totalBlocks
                return acc
            },
            { properties: 0, units: 0, vacant: 0, blocks: 0 }
        )
    }, [propertyViewModels])

    const activeFilterCount = (searchQuery.trim() ? 1 : 0) + (statusFilter ? 1 : 0)
    const occupancyRate =
        portfolioStats.units > 0
            ? Math.round(((portfolioStats.units - portfolioStats.vacant) / portfolioStats.units) * 100)
            : 0

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-5">
                <div className="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.16),_transparent_40%),linear-gradient(135deg,_#ffffff_0%,_#f8fbff_48%,_#eef6ff_100%)] p-6 shadow-[0_18px_50px_-30px_rgba(15,23,42,0.28)] md:p-8">
                    <div className="absolute right-0 top-0 h-44 w-44 rounded-full bg-blue-100/50 blur-3xl" />
                    <div className="absolute -bottom-16 right-8 hidden h-40 w-40 rounded-full border border-blue-100 bg-white/60 lg:block" />

                    <div className="relative grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] xl:items-end">
                        <div className="space-y-4">
                            <div className="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white/80 px-3 py-1.5 shadow-sm backdrop-blur-sm">
                                <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                                <span className="text-[10px] font-semibold uppercase tracking-[0.22em] text-blue-700">Property Registry</span>
                            </div>

                            <div className="space-y-3">
                                <h1 className="max-w-3xl text-3xl font-semibold tracking-tight text-slate-950 md:text-4xl">
                                    Lease-ready commercial spaces with clearer availability and faster intent capture
                                </h1>
                                <p className="max-w-2xl text-sm leading-6 text-slate-600 md:text-[15px]">
                                    Browse vetted properties, compare live availability, and submit interest from a single flow designed to shorten decision time.
                                </p>
                            </div>

                            <div className="flex flex-wrap gap-2.5">
                                <div className="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700">
                                    <ShieldCheck className="h-3.5 w-3.5" />
                                    Verified inventory
                                </div>
                                <div className="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-[11px] font-semibold text-blue-700">
                                    <Clock3 className="h-3.5 w-3.5" />
                                    Faster enquiry path
                                </div>
                                <div className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700">
                                    <DoorOpen className="h-3.5 w-3.5 text-amber-600" />
                                    {portfolioStats.vacant.toLocaleString()} spaces currently open
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Live listings</p>
                                <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{portfolioStats.properties}</p>
                                <p className="mt-1 text-xs text-slate-600">Properties surfaced for leasing decisions</p>
                            </div>
                            <div className="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Vacancy rate</p>
                                <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{portfolioStats.units > 0 ? `${Math.max(100 - occupancyRate, 0)}%` : "0%"}</p>
                                <p className="mt-1 text-xs text-slate-600">Availability across {portfolioStats.units.toLocaleString()} units</p>
                            </div>
                            <div className="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Ready now</p>
                                <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{portfolioStats.vacant}</p>
                                <p className="mt-1 text-xs text-slate-600">Vacant spaces that can take interest today</p>
                            </div>
                            <div className="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Property mix</p>
                                <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{portfolioStats.blocks}</p>
                                <p className="mt-1 text-xs text-slate-600">Blocks distributed across listed assets</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="rounded-[1.75rem] border border-slate-200 bg-white/90 p-3 shadow-[0_16px_40px_-32px_rgba(15,23,42,0.35)] backdrop-blur-sm md:p-4">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center">
                        <div className="relative flex-1 group">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" strokeWidth={2} />
                            <input
                                type="text"
                                placeholder="Search by property name, code, or location"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-10 text-sm outline-none transition-all placeholder:text-slate-400 focus:border-blue-300 focus:bg-white focus:ring-4 focus:ring-blue-50"
                            />
                            {searchQuery && (
                                <button
                                    onClick={() => setSearchQuery("")}
                                    type="button"
                                    className="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg transition-colors hover:bg-slate-100"
                                >
                                    <X className="h-4 w-4 text-slate-400" strokeWidth={2} />
                                </button>
                            )}
                        </div>

                        <div className="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-1 lg:min-w-[300px]">
                            {["All", "Vacant", "Occupied"].map((label) => (
                                <button
                                    key={label}
                                    onClick={() => setStatusFilter(label === "All" ? null : label)}
                                    className={cn(
                                        "flex-1 rounded-xl px-4 py-2.5 text-xs font-semibold transition-all whitespace-nowrap",
                                        (statusFilter === label || (label === "All" && !statusFilter))
                                            ? "bg-white text-slate-900 shadow-sm"
                                            : "text-slate-600 hover:bg-white/60 hover:text-slate-900"
                                    )}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="mt-3 flex flex-col gap-3 border-t border-slate-100 px-1 pt-3 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex flex-wrap items-center gap-2 text-sm text-slate-600">
                            <span className="font-semibold text-slate-900">{propertyViewModels.length}</span>
                            <span>{propertyViewModels.length === 1 ? "property" : "properties"} matched</span>
                            <span className="hidden text-slate-300 sm:inline">•</span>
                            <span>{portfolioStats.vacant} vacant units ready for interest</span>
                            {activeFilterCount > 0 ? (
                                <Badge className="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-blue-700">
                                    {activeFilterCount} active {activeFilterCount === 1 ? "filter" : "filters"}
                                </Badge>
                            ) : null}
                        </div>

                        {activeFilterCount > 0 ? (
                            <Button
                                onClick={() => {
                                    setSearchQuery("")
                                    setStatusFilter(null)
                                }}
                                variant="outline"
                                className="h-10 rounded-xl border-slate-200 px-4 text-xs font-semibold text-slate-700"
                            >
                                Clear filters
                            </Button>
                        ) : (
                            <div className="flex items-center gap-2 text-xs font-medium text-slate-500">
                                <Blocks className="h-3.5 w-3.5 text-blue-500" />
                                Listings are ranked by available inventory first
                            </div>
                        )}
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
                    {propertyViewModels.map(({ property, locationName, stats, imageUrl }) => (
                        <PropertyDetailsSheet
                            key={property.id}
                            property={property}
                            locationName={locationName}
                            stats={stats}
                            imageUrl={imageUrl}
                        >
                            <div className="cursor-pointer">
                                <PropertyCard
                                    property={property}
                                    locationName={locationName}
                                    stats={stats}
                                    imageUrl={imageUrl}
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

function PropertyCard({ property, locationName, stats, imageUrl }: { property: Property, locationName: string, stats: PropertyStats, imageUrl?: string | null }) {
    return (
        <div className="group/card relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-[0_24px_60px_-32px_rgba(37,99,235,0.35)]">
            <PropertyVisual title={property.propertyName} imageUrl={imageUrl} className="aspect-[16/10]">
                <div className="absolute inset-0 bg-gradient-to-t from-slate-950/20 via-transparent to-transparent opacity-70" />

                <div className="absolute left-4 top-4 flex flex-wrap gap-2">
                    <Badge className="rounded-full border border-slate-200 bg-white/95 px-3 py-1 text-[10px] font-semibold text-slate-900 backdrop-blur-sm">
                        {property.propertyCode}
                    </Badge>
                    <Badge className={cn("rounded-full px-3 py-1 text-[10px] font-semibold backdrop-blur-sm", getAvailabilityTone(stats))}>
                        {stats.vacantUnits > 0 ? `${stats.vacantUnits} Ready Now` : "Waitlist Opportunity"}
                    </Badge>
                </div>

                <div className="absolute inset-x-4 bottom-4 rounded-2xl border border-white/70 bg-white/88 p-3 shadow-sm backdrop-blur-sm">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Availability</p>
                            <p className="mt-1 text-sm font-semibold text-slate-950">{getPropertyLead(stats)}</p>
                        </div>
                        <div className="text-right">
                            <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Vacancy</p>
                            <p className="mt-1 text-lg font-semibold tracking-tight text-slate-950">{stats.vacancyRate}%</p>
                        </div>
                    </div>
                </div>
            </PropertyVisual>

            <div className="space-y-5 p-5">
                <div className="space-y-2.5">
                    <h3 className="line-clamp-1 text-lg font-semibold text-slate-900 transition-colors group-hover/card:text-blue-600">
                        {property.propertyName}
                    </h3>
                    <div className="flex items-center gap-1.5 text-slate-600">
                        <MapPin className="h-3.5 w-3.5 text-blue-500" strokeWidth={2} />
                        <span className="text-xs font-medium">{locationName}</span>
                    </div>
                    <p className="line-clamp-2 text-sm leading-6 text-slate-600">
                        {property.propertyDescription || "Modern commercial space configured for fast discovery, easier comparison, and quicker lease interest capture."}
                    </p>
                </div>

                <div className="grid grid-cols-3 gap-2">
                    <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                        <p className="mb-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Units</p>
                        <p className="text-base font-semibold text-slate-950">{stats.totalUnits}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                        <p className="mb-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Floors</p>
                        <p className="text-base font-semibold text-slate-950">{stats.totalFloors}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                        <p className="mb-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">Blocks</p>
                        <p className="text-base font-semibold text-slate-950">{stats.totalBlocks}</p>
                    </div>
                </div>

                <div className="space-y-3 border-t border-slate-100 pt-4">
                    <div className="flex items-center justify-between text-xs font-medium text-slate-600">
                        <span>Immediate fit</span>
                        <span>{stats.vacantUnits} of {stats.totalUnits} units available</span>
                    </div>
                    <div className="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div
                            className={cn(
                                "h-full rounded-full transition-all duration-500",
                                stats.vacantUnits > 0 ? "bg-emerald-500" : "bg-slate-300"
                            )}
                            style={{ width: `${Math.max(stats.vacancyRate, stats.totalUnits > 0 ? 8 : 0)}%` }}
                        />
                    </div>
                </div>

                <div className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                    <div>
                        <p className="text-xs font-semibold text-slate-900">Explore units and submit interest</p>
                        <p className="mt-1 text-[11px] text-slate-600">Faster decision flow with availability surfaced upfront</p>
                    </div>
                    <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-all group-hover/card:bg-blue-500 group-hover/card:text-white">
                        <ArrowUpRight className="h-4 w-4" strokeWidth={2} />
                    </div>
                </div>
            </div>
        </div>
    )
}

function PropertyDetailsSheet({ property, locationName, stats, imageUrl, children }: { property: Property, locationName: string, stats: PropertyStats, imageUrl?: string | null, children: React.ReactNode }) {
    const { data: session } = useSession()
    const accessToken = resolveSessionAccessToken(session as any) || null
    const totalVacant = stats.vacantUnits
    const totalUnits = stats.totalUnits
    const today = new Date().toISOString().split("T")[0]

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
                toast.success("Lease interest submitted. Verification could not be confirmed.")
            } else {
                toast.success((created as any)?.message || "Interest submitted successfully.")
            }
            setIsInterestDialogOpen(false)
            resetInterestForm()
        } catch (error) {
            toast.error(error instanceof Error ? error.message : "Unable to submit interest.")
        } finally {
            setIsSubmittingInterest(false)
        }
    }

    return (
        <Sheet>
            <SheetTrigger asChild>{children}</SheetTrigger>
            <SheetContent className="w-full overflow-hidden border-l border-slate-200 bg-white p-0 sm:max-w-[540px] md:max-w-2xl">
                <div className="flex h-full flex-col">
                    <header className="relative shrink-0 border-b border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_40%),linear-gradient(135deg,_#eff6ff_0%,_#ffffff_55%,_#f8fafc_100%)] p-8 pb-10">
                        <div className="absolute inset-y-0 right-0 hidden w-[36%] md:block">
                            <PropertyVisual title={property.propertyName} imageUrl={imageUrl} className="h-full rounded-none border-l border-slate-200" />
                        </div>
                        <div className="relative z-10 space-y-5">
                            <div className="flex flex-wrap items-center gap-2">
                                <Badge className="rounded-full border-none bg-blue-600 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white">
                                    {property.propertyCode}
                                </Badge>
                                <Badge className={cn("rounded-full px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em]", getAvailabilityTone(stats))}>
                                    {totalVacant > 0 ? `${totalVacant} units available` : "Currently fully occupied"}
                                </Badge>
                            </div>
                            <div className="space-y-3">
                                <SheetTitle className="pr-12 text-3xl font-semibold leading-tight tracking-tight text-slate-900">
                                    {property.propertyName}
                                </SheetTitle>
                                <div className="flex items-center gap-2 text-sm font-medium text-slate-600">
                                    <MapPin className="h-4 w-4 text-blue-500" strokeWidth={2} />
                                    {locationName}
                                </div>
                                <p className="max-w-2xl text-sm leading-6 text-slate-600">
                                    {property.propertyDescription || "A well-positioned commercial property with a cleaner leasing journey, clearer unit visibility, and a faster path to submit interest."}
                                </p>
                            </div>
                        </div>
                        <div className="pointer-events-none absolute -right-10 -bottom-10 select-none text-blue-50">
                            <Building2 className="h-64 w-64" strokeWidth={0.5} />
                        </div>
                    </header>

                    <div className="flex-1 space-y-8 overflow-y-auto px-8 py-8 pb-36">
                        <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Vacant now</p>
                                <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{totalVacant}</p>
                                <p className="mt-1 text-xs text-slate-600">Immediate spaces open for leasing</p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Total units</p>
                                <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{totalUnits}</p>
                                <p className="mt-1 text-xs text-slate-600">Inventory across this property</p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Blocks</p>
                                <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{stats.totalBlocks}</p>
                                <p className="mt-1 text-xs text-slate-600">Distinct building sections</p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Floors</p>
                                <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{stats.totalFloors}</p>
                                <p className="mt-1 text-xs text-slate-600">Levels available to review</p>
                            </div>
                        </section>

                        <section className="space-y-4 rounded-[1.5rem] border border-blue-100 bg-blue-50/60 p-5">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <h4 className="text-sm font-semibold text-slate-900">Why this listing is easier to convert</h4>
                                    <p className="mt-1 text-sm leading-6 text-slate-600">
                                        Availability is surfaced before the enquiry step, so you can review inventory and act with less back-and-forth.
                                    </p>
                                </div>
                                <div className="rounded-2xl bg-white px-3 py-2 text-right shadow-sm">
                                    <p className="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Vacancy rate</p>
                                    <p className="mt-1 text-xl font-semibold tracking-tight text-slate-950">{stats.vacancyRate}%</p>
                                </div>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-3">
                                <div className="rounded-2xl border border-white/80 bg-white/80 p-4">
                                    <p className="text-xs font-semibold text-slate-900">Clearer stock visibility</p>
                                    <p className="mt-1 text-xs leading-5 text-slate-600">Browse every block, floor, and unit without switching screens.</p>
                                </div>
                                <div className="rounded-2xl border border-white/80 bg-white/80 p-4">
                                    <p className="text-xs font-semibold text-slate-900">Faster intent capture</p>
                                    <p className="mt-1 text-xs leading-5 text-slate-600">Submit interest directly from the property view once a fit is clear.</p>
                                </div>
                                <div className="rounded-2xl border border-white/80 bg-white/80 p-4">
                                    <p className="text-xs font-semibold text-slate-900">Better shortlist quality</p>
                                    <p className="mt-1 text-xs leading-5 text-slate-600">Prioritise spaces with current vacancy and useful structure details.</p>
                                </div>
                            </div>
                        </section>

                        <div className="space-y-6">
                            <div className="space-y-2">
                                <h4 className="text-xs font-semibold uppercase tracking-wider text-slate-500">Property structure</h4>
                                <p className="text-sm text-slate-600">Open each floor to inspect unit sizes and current availability before submitting interest.</p>
                            </div>

                            {property.blocks?.map((block) => (
                                <section key={block.id} className="space-y-4">
                                    <div className="flex items-center gap-3 border-b border-slate-200 pb-2">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500 text-white">
                                            <LayoutGrid className="h-4 w-4" strokeWidth={2} />
                                        </div>
                                        <div>
                                            <h4 className="text-lg font-semibold text-slate-900">{block.blockName}</h4>
                                            <p className="text-xs text-slate-500">{block.floors?.length ?? 0} floors in this block</p>
                                        </div>
                                    </div>

                                    <Accordion type="multiple" className="space-y-2">
                                        {block.floors?.map((floor) => (
                                            <AccordionItem key={floor.id} value={`floor-${floor.id}`} className="border-none">
                                                <AccordionTrigger className="py-0 hover:no-underline [&[data-state=open]>div]:border-blue-300 [&[data-state=open]>div]:bg-blue-50/30">
                                                    <div className="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white p-4 text-left transition-all">
                                                        <div className="flex items-center gap-4">
                                                            <div className="flex h-10 w-10 items-center justify-center rounded-lg border border-blue-200 bg-blue-50">
                                                                <span className="text-base font-semibold text-blue-600">
                                                                    {floor.floorLabel.replace(/\D/g, '')?.padStart(2, '0') || "01"}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <p className="text-sm font-semibold text-slate-900">{floor.floorLabel}</p>
                                                                <p className="mt-0.5 text-xs font-medium text-slate-600">{floor.units.length} units</p>
                                                            </div>
                                                        </div>
                                                        <ChevronDown className="h-4 w-4 text-slate-400 transition-transform duration-200" strokeWidth={2} />
                                                    </div>
                                                </AccordionTrigger>
                                                <AccordionContent className="px-1 pt-2">
                                                    <div className="space-y-2">
                                                        {floor.units?.map((unit) => (
                                                            <div
                                                                key={unit.id}
                                                                className="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50/20"
                                                            >
                                                                <div className="space-y-1">
                                                                    <div className="flex items-center gap-2">
                                                                        <p className="text-sm font-semibold text-slate-900">{unit.unitCode}</p>
                                                                        {String(unit.availabilityLabel || "").toLowerCase() === "vacant" ? (
                                                                            <span className="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-emerald-700">
                                                                                Recommended now
                                                                            </span>
                                                                        ) : null}
                                                                    </div>
                                                                    <div className="flex items-center gap-2 text-xs font-medium text-slate-600">
                                                                        <Maximize2 className="h-3.5 w-3.5 text-blue-500" strokeWidth={2} />
                                                                        {unit.unitSize} sq ft
                                                                    </div>
                                                                </div>
                                                                <Badge className={cn(
                                                                    "rounded-lg border px-2.5 py-1 text-[10px] font-semibold uppercase",
                                                                    unit.availabilityLabel === "Vacant"
                                                                        ? "border-emerald-200 bg-emerald-50 text-emerald-700"
                                                                        : "border-slate-200 bg-slate-100 text-slate-600"
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

                    <footer className="absolute bottom-0 z-50 w-full shrink-0 border-t border-slate-200 bg-white/95 p-5 backdrop-blur-sm">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p className="text-sm font-semibold text-slate-900">
                                    {totalVacant > 0 ? `${totalVacant} spaces can take interest today` : "This property is currently fully occupied"}
                                </p>
                                <p className="mt-1 text-xs text-slate-600">
                                    Submitting interest is non-binding and helps the leasing team respond with the right options faster.
                                </p>
                            </div>

                            <div className="flex gap-3">
                                <Button
                                    variant="outline"
                                    onClick={() => openPrintablePropertySummary({ property, locationName, stats, imageUrl })}
                                    className="h-11 rounded-xl border-slate-200 bg-white px-5 text-xs font-semibold text-slate-700 shadow-none transition-colors hover:border-slate-300 hover:bg-slate-50"
                                >
                                    Download details
                                </Button>
                                <Button
                                    disabled={totalVacant === 0}
                                    onClick={openInterestDialog}
                                    className={cn(
                                        "h-11 rounded-xl px-5 text-xs font-semibold shadow-none transition-colors focus-visible:ring-4 focus-visible:ring-blue-50",
                                        totalVacant === 0
                                            ? "cursor-not-allowed bg-slate-200 text-slate-500 hover:bg-slate-200"
                                            : "bg-blue-600 text-white hover:bg-blue-700"
                                    )}
                                >
                                    {totalVacant === 0 ? "No vacant units right now" : `Request this space (${totalVacant})`}
                                </Button>
                            </div>
                        </div>
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
                <DialogContent className="border border-slate-200 bg-white shadow-none sm:max-w-[640px]">
                    <DialogHeader>
                        <DialogTitle className="text-xl font-semibold tracking-tight text-slate-900">
                            Submit lease interest
                        </DialogTitle>
                    </DialogHeader>

                    <div className="rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                        <p className="text-sm font-semibold text-slate-900">{property.propertyName}</p>
                        <p className="mt-1 text-xs leading-5 text-slate-600">
                            Choose the unit that fits best, add your preferred leasing window, and the team can respond with the right next step.
                        </p>
                    </div>

                    <form className="space-y-4" onSubmit={submitInterest}>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-slate-700">Unit</label>
                                <select
                                    value={selectedUnitId}
                                    onChange={(e) => setSelectedUnitId(e.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300"
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
                                <label className="text-xs font-semibold text-slate-700">Payment frequency</label>
                                <select
                                    value={paymentFrequencyId}
                                    onChange={(e) => setPaymentFrequencyId(e.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300 disabled:bg-slate-100"
                                    disabled={isLoadingPaymentFrequency}
                                    required
                                >
                                    <option value="" disabled>
                                        {isLoadingPaymentFrequency ? "Loading payment frequencies" : "Select payment frequency"}
                                    </option>
                                    {paymentFrequencyOptions.map((option) => (
                                        <option key={option.id} value={option.id}>
                                            {option.label || option.name || option.code || `Option ${option.id}`}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {selectedUnit ? (
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Selected space</p>
                                <div className="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-700">
                                    <span className="font-semibold text-slate-900">{selectedUnit.unitCode}</span>
                                    <span>•</span>
                                    <span>{selectedUnit.blockName}</span>
                                    <span>•</span>
                                    <span>{selectedUnit.floorLabel}</span>
                                </div>
                            </div>
                        ) : null}

                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-slate-700">Interested start date</label>
                                <input
                                    type="date"
                                    value={startDate}
                                    min={today}
                                    onChange={(e) => setStartDate(e.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300"
                                    required
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-xs font-semibold text-slate-700">Interested end date</label>
                                <input
                                    type="date"
                                    value={endDate}
                                    min={startDate || today}
                                    onChange={(e) => setEndDate(e.target.value)}
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-blue-300"
                                    required
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-xs font-semibold text-slate-700">Additional information</label>
                            <textarea
                                value={additionalInformation}
                                onChange={(e) => setAdditionalInformation(e.target.value)}
                                placeholder="Tell the leasing team your use case, preferred term, or move-in expectations"
                                className="min-h-[104px] w-full rounded-xl border border-slate-200 bg-white p-3 text-sm outline-none focus:border-blue-300"
                            />
                        </div>

                        <div className="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-xs text-slate-600">
                            <span>No commitment is created at this step.</span>
                            <span className="font-semibold text-slate-900">Response-ready submission</span>
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
                                {isSubmittingInterest ? <><Spinner className="mr-1.5 h-4 w-4" />Submitting interest</> : "Send interest"}
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
