"use client"

import React, { useMemo, useState } from "react"
import { Building2, Search, Inbox, Layers, LayoutGrid, Loader2, ArrowUpRight, Maximize2, Sparkles, Filter, Building, X } from "lucide-react"
import { usePagination } from "@/components/providers/pagination-provider"
import { Property, PaginatedResponse, Unit } from "@/types/property"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetTrigger, } from "@/components/common/sheet"
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger, } from "@/components/common/accordion"
import { cn } from "@/lib/utils"

export function RentablePropertiesList({
    initialData,
    searchQuery,
    setSearchQuery
}: {
    initialData?: PaginatedResponse<Property>;
    searchQuery: string;
    setSearchQuery: (q: string) => void
}) {
    const { isPending } = usePagination()
    const [statusFilter, setStatusFilter] = useState<string | null>(null)

    const properties = initialData?.data ?? []

    const filteredProperties = useMemo(() => {
        const query = searchQuery.trim().toLowerCase()
        return properties.filter(p => {
            const matchesSearch = p.propertyName.toLowerCase().includes(query) ||
                p.propertyCode.toLowerCase().includes(query)

            if (!statusFilter) return matchesSearch

            const hasMatchingUnit = p.blocks.some(b =>
                b.floors.some(f =>
                    f.units.some(u => u.availabilityLabel === statusFilter)
                )
            )
            return matchesSearch && hasMatchingUnit
        })
    }, [properties, searchQuery, statusFilter])

    return (
        <div className="w-full space-y-8 antialiased selection:bg-sky-500/10 min-h-screen pb-20">
            <header className="flex flex-col gap-8">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div className="space-y-1.5">
                        <div className="flex items-center gap-2 text-sky-600">
                            <Sparkles className="h-4 w-4 fill-current" />
                            <span className="text-[15px] font-black uppercase tracking-[0.25em]">Asset Inventory</span>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="relative group w-full md:w-96">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/40 group-focus-within:text-sky-600 transition-colors" />
                            <input
                                type="text"
                                placeholder="Search by name, code..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-sky-50/50 dark:bg-sky-950/20 border border-transparent focus:bg-background focus:border-sky-200 focus:ring-4 focus:ring-sky-500/5 outline-none transition-all text-sm font-medium"
                            />
                        </div>
                        <Button variant="outline" className="rounded-2xl h-[52px] border-sky-100 dark:border-sky-900/30 bg-sky-50/30 px-4 md:px-6 hover:bg-sky-50">
                            <Filter className="h-4 w-4 md:mr-2 text-sky-600" />
                            <span className="hidden md:inline font-bold text-[10px] uppercase tracking-widest text-sky-700">Refine</span>
                        </Button>
                    </div>
                </div>

                <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    {["Vacant", "Occupied", "Under Maintenance"].map((status) => (
                        <button
                            key={status}
                            onClick={() => setStatusFilter(statusFilter === status ? null : status)}
                            className={cn(
                                "px-5 py-2.5 rounded-full text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap border",
                                statusFilter === status
                                    ? "bg-sky-600 text-white border-sky-600 shadow-lg shadow-sky-600/20"
                                    : "bg-background text-muted-foreground border-border/60 hover:border-sky-200 hover:text-sky-600"
                            )}
                        >
                            {status}
                        </button>
                    ))}
                    {statusFilter && (
                        <button
                            onClick={() => setStatusFilter(null)}
                            className="p-2.5 rounded-full bg-secondary/50 text-muted-foreground hover:text-destructive transition-colors"
                        >
                            <X className="h-3.5 w-3.5" />
                        </button>
                    )}
                </div>
            </header>

            {isPending && properties.length === 0 ? (
                <div className="h-[40vh] flex flex-col items-center justify-center gap-4 text-sky-600/30">
                    <Loader2 className="h-10 w-10 animate-spin" />
                    <span className="text-[10px] font-black uppercase tracking-[0.3em]">Synchronizing Registry</span>
                </div>
            ) : filteredProperties.length === 0 ? (
                <EmptyState isSearch={!!searchQuery || !!statusFilter} onClear={() => { setSearchQuery(""); setStatusFilter(null) }} />
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 md:gap-8">
                    {filteredProperties.map((property) => (
                        <PropertyDetailsSheet key={property.id} property={property}>
                            <PropertyCard property={property} />
                        </PropertyDetailsSheet>
                    ))}
                </div>
            )}
        </div>
    )
}

function PropertyCard({ property }: { property: Property }) {
    const stats = useMemo(() => {
        let units = 0, sqft = 0, vacant = 0;
        property.blocks.forEach(b => b.floors.forEach(f => f.units.forEach(u => {
            units++;
            sqft += parseFloat(u.unitSize) || 0;
            if (u.availabilityLabel === "Vacant") vacant++;
        })));
        return { units, sqft: sqft.toLocaleString(), vacant };
    }, [property]);

    return (
        <div className="group relative flex flex-col h-full bg-background border border-border/50 rounded-[2rem] overflow-hidden hover:border-sky-300 transition-all duration-500 shadow-none hover:shadow-2xl hover:shadow-sky-500/5">
            <div className="relative aspect-[16/10] bg-sky-50/50 dark:bg-sky-950/20 overflow-hidden">
                <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <Building2 className="h-20 w-20 text-sky-600/[0.03] group-hover:scale-125 group-hover:text-sky-600/[0.07] transition-all duration-1000" />
                </div>

                <div className="absolute top-5 left-5">
                    <Badge variant="outline" className="bg-background/90 backdrop-blur-md border-sky-100 text-[9px] font-black tracking-widest uppercase text-sky-700">
                        {property.propertyCode}
                    </Badge>
                </div>

                {stats.vacant > 0 && (
                    <div className="absolute top-5 right-5">
                        <div className="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500 text-white text-[9px] font-black shadow-lg shadow-emerald-500/20">
                            <span className="relative flex h-1.5 w-1.5">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-white"></span>
                            </span>
                            {stats.vacant} VACANT
                        </div>
                    </div>
                )}

                <div className="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

                <div className="absolute bottom-5 right-5 h-11 w-11 rounded-full bg-sky-600 text-white flex items-center justify-center opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500 shadow-xl shadow-sky-600/30">
                    <ArrowUpRight className="h-5 w-5 stroke-[2.5px]" />
                </div>
            </div>

            <div className="p-6 space-y-6 flex-grow flex flex-col">
                <div className="space-y-2">
                    <h3 className="text-xl font-bold tracking-tight group-hover:text-sky-600 transition-colors line-clamp-1">
                        {property.propertyName}
                    </h3>
                    <div className="flex items-center gap-4 text-muted-foreground/60 text-[10px] font-bold uppercase tracking-widest">
                        <span className="flex items-center gap-1.5"><Layers className="h-3.5 w-3.5 text-sky-600/40" /> {property.blocks.length} Blocks</span>
                        <span className="flex items-center gap-1.5"><LayoutGrid className="h-3.5 w-3.5 text-sky-600/40" /> {stats.units} Units</span>
                    </div>
                </div>

                <div className="mt-auto pt-6 border-t border-border/40 flex items-center justify-between">
                    <div className="flex items-baseline gap-1">
                        <span className="text-xl font-black tracking-tighter text-foreground">{stats.sqft}</span>
                        <span className="text-[10px] font-bold text-muted-foreground/60 uppercase">sqft</span>
                    </div>
                    <div className="text-[10px] font-black text-sky-600 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all">
                        Explore
                    </div>
                </div>
            </div>
        </div>
    )
}

function PropertyDetailsSheet({ property, children }: { property: Property, children: React.ReactNode }) {
    return (
        <Sheet>
            <SheetTrigger asChild>{children}</SheetTrigger>
            <SheetContent className="w-full sm:max-w-2xl bg-background p-0 border-l-border/30 shadow-none">
                <div className="h-full flex flex-col">
                    <div className="p-8 border-b border-sky-100 dark:border-sky-900/30 shrink-0 bg-sky-50/30 dark:bg-sky-950/10 relative overflow-hidden">
                        <div className="relative z-10">
                            <div className="flex items-center gap-2 text-[10px] font-black text-sky-600 uppercase tracking-[0.3em] mb-3">
                                <Building className="h-3.5 w-3.5" /> ID: {property.propertyCode}
                            </div>
                            <SheetTitle className="text-4xl font-black tracking-tight text-foreground">
                                {property.propertyName}
                            </SheetTitle>
                            <SheetDescription className="font-medium text-muted-foreground/70 leading-relaxed pt-2">
                                Architectural hierarchy and real-time unit availability for this asset.
                            </SheetDescription>
                        </div>
                        <Building2 className="absolute -right-8 -bottom-8 h-48 w-48 text-sky-600/[0.03] rotate-12" />
                    </div>

                    <div className="flex-1 overflow-y-auto p-6 md:p-10 space-y-12 scrollbar-none">
                        {property.blocks.map((block) => (
                            <div key={block.id} className="space-y-8">
                                <div className="flex items-center gap-6">
                                    <h4 className="text-[11px] font-black uppercase tracking-[0.4em] text-foreground shrink-0">{block.blockName}</h4>
                                    <div className="h-px w-full bg-gradient-to-r from-sky-100 dark:from-sky-900/50 to-transparent" />
                                </div>

                                <Accordion type="multiple" className="space-y-4">
                                    {block.floors.map((floor) => (
                                        <AccordionItem key={floor.id} value={`floor-${floor.id}`} className="border rounded-[1.5rem] px-6 bg-background border-border/60 overflow-hidden shadow-none transition-all hover:border-sky-200">
                                            <AccordionTrigger className="hover:no-underline py-6 group/trigger">
                                                <div className="flex items-center justify-between w-full pr-4 text-left">
                                                    <div className="space-y-1">
                                                        <p className="text-sm font-bold tracking-tight group-hover/trigger:text-sky-600 transition-colors">{floor.floorLabel}</p>
                                                        <p className="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">{floor.units.length} Units Found</p>
                                                    </div>
                                                </div>
                                            </AccordionTrigger>
                                            <AccordionContent className="pb-8">
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {floor.units.map((unit) => (
                                                        <UnitRow key={unit.id} unit={unit} />
                                                    ))}
                                                </div>
                                            </AccordionContent>
                                        </AccordionItem>
                                    ))}
                                </Accordion>
                            </div>
                        ))}
                    </div>

                    <div className="p-8 border-t border-border/40 shrink-0 flex gap-4 bg-background">
                        <Button variant="outline" className="flex-1 h-14 rounded-2xl text-[11px] font-black uppercase tracking-widest border-border/60 hover:bg-sky-50 hover:text-sky-600 hover:border-sky-200 transition-all">
                            Specifications
                        </Button>
                        <Button className="flex-1 h-14 rounded-2xl text-[11px] font-black uppercase tracking-widest bg-sky-600 hover:bg-sky-700 shadow-xl shadow-sky-600/20">
                            Book Inspection
                        </Button>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    )
}

function UnitRow({ unit }: { unit: Unit }) {
    const isVacant = unit.availabilityLabel === "Vacant";
    return (
        <div className="flex items-center justify-between p-4 rounded-2xl border border-sky-50 dark:border-sky-900/10 bg-sky-50/30 dark:bg-sky-900/5 hover:border-sky-200 transition-all group/unit">
            <div className="flex items-center gap-3">
                <div className={cn("h-2.5 w-2.5 rounded-full transition-all", isVacant ? "bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]" : "bg-muted-foreground/20")} />
                <div className="space-y-0.5">
                    <p className="text-xs font-bold text-foreground transition-colors group-hover/unit:text-sky-700">{unit.unitCode}</p>
                    <p className="text-[9px] font-bold text-muted-foreground/60 uppercase tracking-tighter flex items-center gap-1.5">
                        <Maximize2 className="h-3 w-3 text-sky-600/40" /> {unit.unitSize} SQFT
                    </p>
                </div>
            </div>
            <Badge variant="outline" className={cn("text-[8px] font-black tracking-widest px-2.5 h-6 rounded-lg border-none", isVacant ? "bg-emerald-500/10 text-emerald-600" : "bg-muted-foreground/10 text-muted-foreground/60")}>
                {unit.availabilityLabel}
            </Badge>
        </div>
    )
}

function EmptyState({ isSearch, onClear }: { isSearch: boolean, onClear: () => void }) {
    return (
        <div className="h-[50vh] flex flex-col items-center justify-center text-center p-12 bg-sky-50/30 dark:bg-sky-950/10 rounded-[3rem] border-2 border-dashed border-sky-100 dark:border-sky-900/30">
            <div className="h-24 w-24 rounded-[2rem] bg-background flex items-center justify-center border border-sky-100 mb-8 shadow-none">
                <Inbox className="h-10 w-10 text-sky-600/20 stroke-[1.5px]" />
            </div>
            <h3 className="text-2xl font-black tracking-tight text-foreground mb-3">Portfolio Empty</h3>
            <p className="text-sm text-muted-foreground/60 font-medium max-w-sm leading-relaxed mb-10">
                Refine your search parameters or reset filters to discover available inventory.
            </p>
            {isSearch && (
                <Button onClick={onClear} className="rounded-full px-10 text-[11px] font-black uppercase tracking-[0.2em] h-12 bg-sky-600 hover:bg-sky-700 shadow-xl shadow-sky-600/20 transition-all">
                    Reset Portfolio Filter
                </Button>
            )}
        </div>
    )
}