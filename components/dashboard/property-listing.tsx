"use client"

import { useMemo } from "react"
import { usePagination } from "@/components/providers/pagination-provider"
import { RentablePropertiesResponse } from "@/types/property"
import { Button } from "@/components/common/button"
import {
    Building2,
    MapPin,
    ArrowUpRight,
    Search,
    Inbox,
} from "lucide-react"
import { cn } from "@/lib/utils"

interface RentablePropertiesListProps {
    initialData?: RentablePropertiesResponse
    searchQuery: string
    setSearchQuery: (query: string) => void
}

export function RentablePropertiesList({
    initialData,
    searchQuery,
    setSearchQuery
}: RentablePropertiesListProps) {
    const { isPending, total } = usePagination()

    const properties = initialData?.data ?? []

    const filteredProperties = useMemo(() => {
        if (!searchQuery) return properties
        return properties.filter(p =>
            p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            p.code.toLowerCase().includes(searchQuery.toLowerCase())
        )
    }, [properties, searchQuery])

    return (
        <div className="w-full max-w-7xl mx-auto py-8 md:py-16 px-4 md:px-8 space-y-10 md:space-y-16 antialiased">
            <header className="flex flex-col lg:flex-row lg:items-end justify-between gap-8 border-b border-border/60 pb-12">
                <div className="space-y-6 max-w-2xl">
                    <div className="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-primary/5 text-primary border border-primary/10">
                        <Building2 className="h-3.5 w-3.5" />
                        <span className="text-[10px] font-bold tracking-[0.2em] uppercase">Property Registry</span>
                    </div>
                    <div className="space-y-2">
                        <p className="text-base font-light text-muted-foreground leading-relaxed">
                            Browse through our exclusive catalog of {total ?? 0} premium units available for lease.
                        </p>
                    </div>
                </div>

                <div className="relative w-full lg:w-80 group">
                    <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground group-focus-within:text-primary transition-colors" />
                    <input
                        type="text"
                        placeholder="Search by name or code..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="w-full pl-11 pr-4 py-3 rounded-full bg-secondary/50 border border-border/50 focus:border-primary/30 focus:ring-4 focus:ring-primary/5 outline-none transition-all text-sm font-light"
                    />
                </div>
            </header>

            {filteredProperties.length === 0 ? (
                <EmptyState isSearch={!!searchQuery} onClear={() => setSearchQuery("")} />
            ) : (
                <div className={cn(
                    "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-10 md:gap-y-14 transition-opacity duration-300",
                    isPending && "opacity-50 pointer-events-none"
                )}>
                    {filteredProperties.map((property) => (
                        <PropertyCard key={property.id} property={property} />
                    ))}
                </div>
            )}
        </div>
    )
}

function PropertyCard({ property }: { property: any }) {
    return (
        <div className="group flex flex-col h-full bg-transparent">
            <div className="relative aspect-[16/10] mb-6 overflow-hidden rounded-2xl bg-secondary/30 border border-border/10">
                <div className="absolute inset-0 flex items-center justify-center opacity-20 group-hover:scale-110 transition-transform duration-700">
                    <Building2 className="h-20 w-20" />
                </div>
                <div className="absolute top-4 left-4">
                    <span className="px-2.5 py-1 rounded-md bg-background/80 backdrop-blur-md text-[10px] font-mono tracking-tighter border border-border/20">
                        {property.code}
                    </span>
                </div>
                <div className="absolute inset-0 bg-gradient-to-t from-background/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                <button className="absolute bottom-4 right-4 h-12 w-12 rounded-full bg-primary text-primary-foreground flex items-center justify-center opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 shadow-xl shadow-primary/20">
                    <ArrowUpRight className="h-5 w-5" />
                </button>
            </div>

            <div className="flex flex-col flex-grow px-1">
                <h3 className="text-xl font-medium tracking-tight text-foreground mb-2 group-hover:text-primary transition-colors duration-300">
                    {property.name}
                </h3>

                <div className="flex items-center gap-2 text-muted-foreground mb-6">
                    <MapPin className="h-3.5 w-3.5" />
                    <span className="text-xs font-light tracking-tight truncate">
                        {property.location_name ?? "Standard District, HQ"}
                    </span>
                </div>

                <div className="mt-auto pt-6 border-t border-border/20 flex items-end justify-between">
                    <div className="space-y-1">
                        <span className="text-[9px] uppercase tracking-[0.2em] text-muted-foreground/70 font-bold block">Rate / Month</span>
                        <div className="flex items-baseline gap-1">
                            <span className="text-2xl font-light tracking-tighter">
                                {property.monthly_rent?.toLocaleString() ?? "—"}
                            </span>
                            <span className="text-[10px] font-medium text-muted-foreground">USD</span>
                        </div>
                    </div>
                    <div className="px-3 py-1 rounded-md bg-secondary/50 text-[10px] font-bold text-muted-foreground uppercase tracking-wider">
                        Active
                    </div>
                </div>
            </div>
        </div>
    )
}

function EmptyState({ isSearch, onClear }: { isSearch: boolean, onClear: () => void }) {
    return (
        <div className="py-32 flex flex-col items-center justify-center text-center space-y-6 rounded-3xl border border-dashed border-border/60 bg-secondary/20">
            <div className="h-20 w-20 rounded-full bg-background flex items-center justify-center text-muted-foreground/30 border border-border/40">
                <Inbox className="h-10 w-10 stroke-[1px]" />
            </div>
            <div className="space-y-2 px-4">
                <h3 className="text-lg font-medium">No matches found</h3>
                <p className="text-sm text-muted-foreground max-w-sm leading-relaxed font-light">
                    {isSearch
                        ? "Your search query didn't yield any properties in our current registry."
                        : "Our property portfolio is currently empty. Please check back later."}
                </p>
            </div>
            {isSearch && (
                <Button onClick={onClear} variant="link" className="text-primary font-medium">
                    Reset search filters
                </Button>
            )}
        </div>
    )
}