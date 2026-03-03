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
import { resolveSessionAccessToken } from "@/lib/auth/resolve-session-access-token"
import {
    createLeaseInterest,
    getLeaseInterestById,
    getPaymentFrequencyCodeDetails,
    type CodeDetail,
} from "@/lib/api/lease-interests"

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
